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
use Manufacturer;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;

class ManufacturerCrd
{

    public function add($object)
    {
        $id_manufacturer = $this->parseManufacturer($object, "create");

        $this->setTableLink($id_manufacturer, $object->id);
    }

    public function update($object)
    {
        $id_manufacturer = $this->getTableLink($object->id);

        $this->parseManufacturer($object, "update", $id_manufacturer);
    }

    private function parseManufacturer($object, $action, $id_manufacturer = null)
    {
        $man = new Manufacturer($id_manufacturer, Configuration::get('PS_LANG_DEFAULT'));
        $man->name = $object->name;
        $clean = preg_replace('/[^A-Za-z0-9. -]/', '', $object->name);
        $man->link_rewrite = Tools::link_rewrite($clean);
        $man->active = $object->status;

        SwitchAction::handleCrud($man, $action);

        $this->handleImage($man, $object);

        return $man->id;
    }

    private function setTableLink($id_manufacturer, $id_crd)
    {
        $datas = ["id_manufacturer" => $id_manufacturer, "id_crd" => $id_crd];
        return Db::getInstance()->insert('dioqaapiconnexion_manufacturer', $datas);
    }

    public function getTableLink($id_crd)
    {
        if (!$id_crd) {
            return false;
        }
        $query = new DbQuery();
        $query->select('id_manufacturer');
        $query->from('dioqaapiconnexion_manufacturer');
        $query->where("id_crd = $id_crd");

        return Db::getInstance()->getValue($query);
    }

    public function handleImage($man, $object)
    {

        if (!$object->image) {
            return false;
        }

        $url = trim($object->image);
        $url = str_replace(' ', '%20', $url);


        if (!ImageCrd::copyImg($man->id, null, $url, 'manufacturers', true)) {
            throw new PrestaShopException('error image import');
        }
    }
}
