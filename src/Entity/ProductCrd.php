<?php

namespace Dioqaapiconnexion\Entity;

use Db;
use DbQuery;
use Configuration;
use Language;
use Tools;
use Search;
use Module;
use Tag;
use Image;
use Shop;
use ImageManager;
use ImageType;
use Context;
use Dioqaapiconnexion\Controller\ApiController;
use PrestaShopException;
use Exception;
use StockAvailable;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;
use Dioqaapiconnexion\Entity\ManufacturerCrd;
use Dioqaapiconnexion\Entity\FeatureValueCrd;
use Dioqaapiconnexion\Entity\FeatureCrd;
use Throwable;

class ProductCrd
{
    public $id;

    public function __construct($id_product = null)
    {
        $this->id = $id_product;
    }

    public function setProduct($object)
    {
        if ($this->getTableLink($object->deviceId)) {
            $this->update($object);
        } else {
            $this->add($object);
        }
    }

    public function add($object)
    {
        $id_product = $this->parseProduct($object, "create");

        $this->setTableLink($id_product, $object->deviceId);
    }

    public function update($object)
    {
        $id_product = $this->getTableLink($object->deviceId);

        $this->parseProduct($object, "update", $id_product);
    }

    public function parseProduct($object, $action, $id = null)
    {
        $cat = $this->getCatModel($object->modelId);

        $pr = new \Product($id, Configuration::get('PS_LANG_DEFAULT'));
        $pr->reference = $object->serialNumber;
        /* $pr->supplier_reference = $object->manufacturerRef; */
        $pr->price = $object->unitSalePrice ? number_format($object->unitSalePrice, 6, '.', '') : 0;
        /* $pr->description = $object->description; */
        $pr->name = self::createMultiLangField($this->createProductName($object));
        /* $pr->link_rewrite = self::createMultiLangField(Tools::str2url($object->shortName)); */
        $pr->modifierWsLinkRewrite();
        $pr->minimal_quantity = 1;
        $pr->id_category_default = $cat;
        $pr->redirect_type = '301';
        $pr->show_price = 1;
        $pr->on_sale = 0;
        $pr->online_only = 0;
        $pr->meta_description = '';
        $idManufacturer = new ManufacturerCrd();
        $idManufacturer = $idManufacturer->getTableLink($object->brandId);
        $pr->id_manufacturer = $idManufacturer;
        /* $pr->quantity = 1; */

        if ($action == 'create') {
            $pr->active = false;
        }

        /* ajout/update/delete du produit  */
        SwitchAction::handleCrud($pr, $action);
        /* Gestion des images */
        $this->handleAllImages($pr, $object);

        $pr->deleteCategories();
        $pr->addToCategories([$cat]);

        $this->setAllFeatures($pr, $object);

        if (isset($object->ramCapacityId) && $object->ramCapacityId != null) {
            $this->addAndsetFeatures($pr, $object, $object->ramCapacityId, 'ramCapacity');
        }

        if (isset($object->romCapacityId) && $object->romCapacityId != null) {
            $this->addAndsetFeatures($pr, $object, $object->romCapacityId, 'romCapacity');
        }

        if (isset($object->gradeId) && $object->gradeId != null) {
            $this->addAndsetFeatures($pr, $object, $object->gradeId, 'grade');
        }

        StockAvailable::setQuantity($pr->id, null, 1);

        /* ajout à l'index de recherche */
        Search::indexation(false, $object->deviceId);
        Module::processDeferedFuncCall();
        Module::processDeferedClearCache();
        Tag::updateTagCount();

        return $pr->id;
    }

    private function addAndsetFeatures(\Product $pr, object $object, int $id, string $type)
    {
        $class = FeatureCrd::getClass($type);
        $class->setFeatureValue($object);

        $this->setFeatureProduct($pr, $id, $type);
    }

    public function setAllFeatures($pr, $object)
    {
        $features = FeatureCrd::$featureListToWatch;

        foreach ($features as $key => $value) {
            $id = $value . 'Id';
            if (isset($object->$id) && $object->$id) {
                $this->setFeatureProduct($pr, $object->$id, $value);
            }
        }
    }

    public function setFeatureProduct($pr, $id, $type)
    {
        $cq = new FeatureValueCrd();
        $idFeatureValue = $cq->getTableLink($id, $type);

        if (!$idFeatureValue) {
            return;
        }

        $idFeature = $this->getFeatureParent($idFeatureValue);
        \Product::addFeatureProductImport($pr->id, $idFeature, $idFeatureValue);
    }

    public function getFeatureParent($idFeatureValue)
    {
        $query = new DbQuery();
        $query->select('id_feature');
        $query->from('feature_value');
        $query->where("id_feature_value = $idFeatureValue");

        return Db::getInstance()->getValue($query);
    }

    public function createProductName($object)
    {
        return $object->name;
    }

    private function getCatList($object)
    {
        $list = [];
        foreach ($object->models as $key => $value) {
            $cat = $this->getCatModel($value->id);
            $cat && $list[] = $cat;
        }
        return $list;
    }

    private function getCatModel($id_crd)
    {
        $type = "model-" . $id_crd;

        $query = new DbQuery();
        $query->select('id_category');
        $query->from('dioqaapiconnexion_category');
        $query->where("id_crd = $id_crd");
        $query->where("type like '%$type'");

        return Db::getInstance()->getValue($query);
    }

    private function setTableLink($id_product, $id_crd)
    {
        $datas = ["id_product" => $id_product, "id_crd" => $id_crd];
        return Db::getInstance()->insert('dioqaapiconnexion_product', $datas);
    }

    private function getTableLink($id_crd)
    {
        $query = new DbQuery();
        $query->select('id_product');
        $query->from('dioqaapiconnexion_product');
        $query->where("id_crd = $id_crd");

        return Db::getInstance()->getValue($query);
    }

    public function handleAllImages($pr, $object)
    {
        $pr->deleteImages();

        if (isset($object->productImages)) {
            foreach ($object->productImages as $key => $image_link) {
                $this->handleImage($pr, $object, $image_link);
            }
        }

        foreach ($object as $key => $value) {
            if (preg_match('/image\d/', $key) && $value != null) {
                $this->handleImage($pr, $object, $value);
            }
        }
    }

    public function handleImage($pr, $object, $image_link)
    {
        $objImg = new Image(null, Configuration::get('PS_LANG_DEFAULT'));
        $objImg->id_product = $pr->id;
        $objImg->position = \Image::getHighestPosition($pr->id) + 1;

        if ($objImg->position == 1) {
            $objImg->cover = true;
        } else {
            $objImg->cover = false;
        }

        $alt = substr($this->createProductName($object), 0, 100);
        if (strlen($alt) > 0) {
            $objImg->legend = self::createMultiLangField($alt);
        }

        $url = trim($image_link);
        $url = str_replace(' ', '%20', $url);

        if (($objImg->validateFields(false, true)) === true &&
            ($objImg->validateFieldsLang(false, true)) === true && $objImg->add()
        ) {
            if (!ImageCrd::copyImg($pr->id, $objImg->id, $url, 'products', true)) {
                $objImg->delete();
            }
        }
    }

    public function isSameHash($url, $productId)
    {
        $sql = "SELECT * FROM ps_dioqaapiconnexion_image WHERE id_product = $productId";
        $result = Db::getInstance()->getRow($sql);

        if (!$result) {
            return false;
        }

        $file = Tools::file_get_contents($url);
        $temp = tmpfile();
        fwrite($temp, $file);
        fseek($temp, 0);
        $metaData = stream_get_meta_data($temp);
        $filepath = $metaData['uri'];
        $hash = hash_file('md5', $filepath);
        fclose($temp); // this removes the file

        if ($hash == $result['hash']) {
            return $result['id_image'];
        }

        $img = new Image($result['id_image']);
        $img->delete();

        $sql = "DELETE FROM ps_dioqaapiconnexion_image WHERE id_image = " . $result['id_image'];
        $result = Db::getInstance()->execute($sql);

        return false;
    }

    public function storeImageInfos($idImage, $idProduct, $url)
    {
        $file = Tools::file_get_contents($url);
        $temp = tmpfile();
        fwrite($temp, $file);
        fseek($temp, 0);
        $metaData = stream_get_meta_data($temp);
        $filepath = $metaData['uri'];
        $hash = hash_file('md5', $filepath);
        fclose($temp); // this removes the file
        $sql = "INSERT INTO ps_dioqaapiconnexion_image VALUE ($idImage, $idProduct, '$hash')";
        return Db::getInstance()->execute($sql);
    }

    protected static function createMultiLangField($field)
    {
        $res = [];
        foreach (Language::getIDs(false) as $id_lang) {
            $res[$id_lang] = $field;
        }

        return $res;
    }

    public function setStock($object)
    {
        $id_product = $this->getTableLink($object->deviceId);

        $stock = $object->quantity - ($object->bookingQuantity ?: 0); /*  - bookingValidity ???*/

        StockAvailable::setQuantity($id_product, null, $stock);
    }

    public function isPublished()
    {
        $query = new DbQuery();
        $query->select('publish');
        $query->from('dioqaapiconnexion_product');
        $query->where("id_product = " . (int) $this->id);

        return Db::getInstance()->getValue($query);
    }

    public function setPublished()
    {
        return Db::getInstance()->update('dioqaapiconnexion_product', ['publish' => true], "id_product = " . (int) $this->id);
    }

    public function setNoPublished()
    {
        return Db::getInstance()->update('dioqaapiconnexion_product', ['publish' => false], "id_product = " . (int) $this->id);
    }

    public function getCRDProductId()
    {
        $query = new DbQuery();
        $query->select('id_crd');
        $query->from('dioqaapiconnexion_product');
        $query->where("id_product = " . (int) $this->id);

        $id_crd = Db::getInstance()->getValue($query);

        if (!$id_crd) {
            throw new PrestaShopException("Product id : " . (int) $this->id . " does not exist");
        }

        return $id_crd;
    }
}
