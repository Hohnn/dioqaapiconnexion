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
use Dioqaapiconnexion\Entity\CustomerCrd;
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

        $this->setTableLink($id_product, $object);
    }

    public function update($object)
    {
        $id_product = $this->getTableLink($object->deviceId);

        $this->updateTableLink($id_product, $object);

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
        $pr->id_category_default = $cat[0] ?? 2;
        $pr->redirect_type = '301';
        $pr->show_price = 1;
        $pr->on_sale = 0;
        $pr->online_only = 0;
        $pr->meta_description = '';
        $idManufacturer = new ManufacturerCrd();
        $idManufacturer = $idManufacturer->getTableLink($object->brandId);
        $pr->id_manufacturer = $idManufacturer;

        if ($action == 'create') {
            $pr->active = false;
        }

        /* ajout/update/delete du produit  */
        SwitchAction::handleCrud($pr, $action);
        /* Gestion des images */
        $this->handleAllImages($pr, $object);

        $pr->deleteCategories();
        $catsSeo = $this->getCatSeo($object->modelId);
        $allCats = array_merge($cat, $catsSeo);
        $pr->addToCategories($allCats);

        /* $groupCats = $this->getCatGroupByModelId($object->modelId);
        $catsForImages = array_merge($allCats, $groupCats);
        $this->addCategoriesImages($catsForImages, $object->colorId, $object->productImage[0]); */

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

        if ($action == 'create') {
            StockAvailable::setQuantity($pr->id, null, 1);
        }

        $this->handleCustomerCrd($object->placeId);
        $client = new CustomerCrd();
        $client->setProductToCustomer($object->placeId, $pr->id);

        /* ajout à l'index de recherche */
        Search::indexation(false, $pr->id);
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

    public function getCatsForImages($modelId)
    {
        $cat = $this->getCatModel($modelId);
        $catsSeo = $this->getCatSeo($modelId);
        $allCats = array_merge($cat, $catsSeo);
        $groupCats = $this->getCatGroupByModelId($modelId);
        return array_merge($allCats, $groupCats);
    }

    private function getCatModel($id_crd)
    {
        $type = "model-" . $id_crd;

        $query = new DbQuery();
        $query->select('id_category');
        $query->from('dioqaapiconnexion_category');
        $query->where("id_crd = $id_crd");
        $query->where("type like '%$type'");

        return array_map(fn ($cat) => $cat['id_category'], Db::getInstance()->executeS($query));
    }

    private function getCatGroupByModelId($id_crd)
    {
        $type = "%group-%-model-" . $id_crd;


        $result = CategoryCrd::getTableLinkLikeStatic($id_crd, $type);

        $groupIds = [];
        foreach ($result as $key => $value) {
            //find group id
            $groupId = $this->getIdByType($value['type'], 'group');
            $type = "%group-$groupId";
            $groups = CategoryCrd::getTableLinkLikeStatic($groupId, $type);
            foreach ($groups as $key => $group) {
                $groupIds[] = $group['id_category'];
            }
        }
        return $groupIds;
    }

    private function getCatSeo($id_crd)
    {
        $type = "model-" . $id_crd;

        $query = new DbQuery();
        $query->select('type');
        $query->from('dioqaapiconnexion_category');
        $query->where("id_crd = $id_crd");
        $query->where("type like '%$type'");

        $datas = array_map(fn ($cat) => $cat['type'], Db::getInstance()->executeS($query));

        $group = array_filter($datas, fn ($cat) => preg_match('/group/', $cat));

        $catIdsGroup = [];
        $catIdsPt = [];

        foreach ($group as $key => $type) {

            $groupId = $this->getIdByType($type, 'group');

            $brandId = $this->getIdByType($type, 'brand');

            $typeGroup = "brandSeo-" . $brandId . '-groupSeo-' . $groupId;
            $cat = new CategoryCrd();
            $catIdGroup = $cat->getTableLink($groupId, $typeGroup);
            $catIdsGroup[] = $catIdGroup;

            $productTypeId = $this->getIdByType($type, 'productType');

            $typePt = 'productTypeSeo-' . $productTypeId;
            $catIdPt = $cat->getTableLink($productTypeId, $typePt);
            $catIdsPt[] = $catIdPt;
        }

        return array_merge($catIdsGroup, $catIdsPt);
    }

    public function getIdByType($type, $typeName)
    {
        $pattern = "/$typeName-\d+/";
        preg_match($pattern, $type, $matches);
        return str_replace("$typeName-", '', $matches[0]);
    }

    private function setTableLink($id_product, $objectCrd)
    {
        $datas = [
            "id_product" => $id_product,
            "id_crd" => $objectCrd->deviceId,
            "productId" => $objectCrd->productId,
            "gradeId" => $objectCrd->gradeId,
            "productTypeId" => $objectCrd->productTypeId,
            "brandId" => $objectCrd->brandId,
            "modelId" => $objectCrd->modelId,
        ];
        return Db::getInstance()->insert('dioqaapiconnexion_product', $datas);
    }

    private function updateTableLink($id_product, $objectCrd)
    {
        $datas = [
            "id_product" => $id_product,
            "id_crd" => $objectCrd->deviceId,
            "productId" => $objectCrd->productId,
            "gradeId" => $objectCrd->gradeId,
            "productTypeId" => $objectCrd->productTypeId,
            "brandId" => $objectCrd->brandId,
            "modelId" => $objectCrd->modelId
        ];
        return Db::getInstance()->update('dioqaapiconnexion_product', $datas, "id_crd = $objectCrd->deviceId");
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

        if (self::getCover($pr->id)) {
            $objImg->cover = false;
        } else {
            $objImg->cover = true;
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

    private static function getCover($id_product)
    {
        $query = new DbQuery();
        $query->select('id_image');
        $query->from('image');
        $query->where("id_product = $id_product");
        $query->where("cover = 1");

        return Db::getInstance()->getValue($query);
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

    public function addCategoriesImages($allCats, $colorId, $image_link)
    {
        if ($colorId != 1 && $colorId != 2) {
            return;
        }

        if (!$image_link) {
            return;
        }

        foreach ($allCats as $catId) {
            $this->handleCategoryImage($catId, $image_link);
        }
    }

    private function handleCategoryImage($catId, $image_link)
    {
        return ImageCrd::copyImg($catId, null, $image_link, 'categories');
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

        $stock = ApiController::getInstance()->get("/api/crd/stocks/device/$object->deviceId");

        $pr = new \Product($id_product);
        if ($object->publishedDateStart) {
            if ($object->publishedDateEnd) {
                $pr->active = false;
            } else {
                $pr->active = true;
            }
        } else {
            if (isset($stock[0])) {
                StockAvailable::setQuantity($id_product, null, $stock[0]->quantity);
            }
        }

        $pr->update();
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

    private function handleCustomerCrd($id_crd)
    {
        $places = ApiController::getInstance()->get("/api/crd/essentials/place");
        $clientApi = array_filter($places, fn ($place) => $place->placeId == $id_crd);

        if (empty($clientApi)) {
            return;
        }

        $clientApi = array_shift($clientApi);

        $clientPresta = \Customer::getCustomersByEmail($clientApi->mail);

        if (isset($clientPresta[0])) {
            $id_customer = $clientPresta[0]['id_customer'];
            if (!CustomerCrd::getTableLinkStatic($id_crd)) {
                CustomerCrd::setTableLinkStatic($id_customer, $id_crd);
            }
        } else {
            $client = new CustomerCrd();
            $client->add($clientApi);
        }
    }

    public function disableProduct()
    {
        $pr = new \Product($this->id);
        $pr->active = false;
        $pr->update();
    }
}
