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
use FeatureValue;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;

class FeatureValueCrd
{
    public $id_feature;

    public function add($object)
    {
        $id_featureValue = $this->parseFeatureValue($object, "create");

        $this->setTableLink($id_featureValue, $object->id, $object->type);
    }

    public function update($object)
    {
        $id_featureValue = $this->getTableLink($object->id, $object->type);

        $this->parseFeatureValue($object, "update", $id_featureValue);
    }

    public function parseFeatureValue($object, $action, $id_featureValue = null)
    {
        $feat = new FeatureValue($id_featureValue, Configuration::get('PS_LANG_DEFAULT'));
        $feat->value = preg_match(Tools::cleanNonUnicodeSupport('/^[^<>={}]*$/u'), $object->name) ? $object->name : htmlspecialchars($object->name);
        $feat->id_feature = $this->id_feature;

        SwitchAction::handleCrud($feat, $action);

        return $feat->id;
    }

    private function setTableLink($id_feature, $id_crd, $type)
    {
        $datas = ["id_feature_value" => $id_feature, "id_crd" => $id_crd, "type" => $type];
        return Db::getInstance()->insert('dioqaapiconnexion_feature_value', $datas);
    }

    public function getTableLink($id_crd, $type)
    {
        $query = new DbQuery();
        $query->select('id_feature_value');
        $query->from('dioqaapiconnexion_feature_value');
        $query->where("id_crd = $id_crd");
        $query->where("type = '$type'");

        return Db::getInstance()->getValue($query);
    }
}
