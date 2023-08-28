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
use PrestaShopException;
use Feature;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;

class FeatureCrd
{
    static public $featureListToWatch = ["brand", "color", "model", "productType"];

    const FEATURE_LIST_CLASS = [
        "Brand" => "Marque",
        "RomCapacity" => "Capacité",
        "RamCapacity" => "États esthétique",
        "Color" => "Couleurs",
        "Model" => "Modèle",
        "Grade" => "Qualité",
    ];

    public function add($object)
    {
        $feat = new Feature(null, Configuration::get('PS_LANG_DEFAULT'));
        $feat->name = $object->nom;
        $feat->add();

        $this->setTableLink($feat->id, $object->id);
        return $feat->id;
    }

    public function update($object)
    {
        $id = $this->getTableLink($object->id);
        $feat = new Feature($id, Configuration::get('PS_LANG_DEFAULT'));
        $feat->name = $object->nom;
        $feat->update();
    }

    private function setTableLink($id_feature, $id_crd)
    {
        $datas = ["id_feature" => $id_feature, "id_crd" => $id_crd];
        return Db::getInstance()->insert('dioqaapiconnexion_feature', $datas);
    }

    private function getTableLink($id_crd)
    {
        $query = new DbQuery();
        $query->select('id_feature');
        $query->from('dioqaapiconnexion_feature');
        $query->where("id_crd = $id_crd");

        return Db::getInstance()->getValue($query);
    }

    public function getAllCRDFeatures()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_feature');

        return Db::getInstance()->executeS($query);
    }

    public function setinitalFeatures()
    {
        $count = 0;
        foreach (self::FEATURE_LIST_CLASS as $className => $value) {
            $obj = (object)[
                "id" => $count,
                "nom" => $value
            ];

            $query = new DbQuery();
            $query->select('*');
            $query->from('feature_lang');
            $query->where("name = '$value'");
            $featureExist = Db::getInstance()->getRow($query);

            if ($featureExist) {
                $linkExist = $this->getTableLink($obj->id);
                $idFeature = $featureExist['id_feature'];
                if (!$linkExist) {
                    $this->setTableLink($featureExist['id_feature'], $obj->id);
                }
            } else {
                $idFeature = $this->add($obj);
            }

            Configuration::updateValue('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($className), $idFeature);
            $count++;
        }
    }

    static function getClass($featureName)
    {
        $className = "Dioqaapiconnexion\Entity\Carac\\" . ucfirst($featureName);
        return new $className();
    }
}
