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
use Category;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;

class CategoryCrd
{
    const ID_RACINE_CAT = 2;
    const ID_MAIN_CAT = 2284;
    const ID_SMARTPHONE_CAT = 2285;
    const ID_TABLETTE_CAT = 2394;
    const ID_COMPUTER_CAT = 2306;
    const ID_OBJECT_CAT = 2445;
    const ID_CONSOLE_CAT = 2289;
    const ID_SEO_CAT = 4420;
    public $id_category_parent;

    public function add($object)
    {
        $id_category = $this->parseCategory($object, "create");

        $this->setTableLink($id_category, $object->id, $object->type);
    }

    public function update($object)
    {
        $id_category = $this->getTableLink($object->id, $object->type);

        $this->parseCategory($object, "update", $id_category, true);
    }

    private function parseCategory($object, $action, $id_category = null, $justCat = false)
    {
        $cat = new Category($id_category, Configuration::get('PS_LANG_DEFAULT'));
        $cat->id_parent = $this->id_category_parent; /* 2 = accueil */

        if (!$justCat) {

            $cat->is_root_category = false; /* like accueil */
            $clean = preg_replace('/[^A-Za-z0-9. -]/', '', $object->name);
            $cat->link_rewrite = Tools::link_rewrite($clean);
            $cat->name = $object->name;
            $cat->active = $object->status;

            if ($object->meta != null && !$cat->meta_description) {
                $cat->meta_description = $object->meta;
            }
        }


        SwitchAction::handleCrud($cat, $action);

        return $cat->id;
    }

    private function setTableLink($id_category, $id_crd, $type)
    {
        $datas = ["id_category" => $id_category, "id_crd" => $id_crd, "type" => $type];
        return Db::getInstance()->insert('dioqaapiconnexion_category', $datas);
    }

    public function getTableLink($id_crd, $type)
    {
        $query = new DbQuery();
        $query->select('id_category');
        $query->from('dioqaapiconnexion_category');
        $query->where("id_crd = $id_crd");
        $query->where("type = '$type'");

        return Db::getInstance()->getValue($query);
    }

    public static function getTableLinkLikeStatic($id_crd, $type)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_category');
        $query->where("id_crd = '$id_crd'");
        $query->where("type LIKE '$type'");

        return Db::getInstance()->executeS($query);
    }
}
