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

class CustomerCrd
{

    public function add($object)
    {
        $id_customer = $this->parseCustomer($object, "create");

        $this->setTableLink($id_customer, $object->placeId);
    }

    public function update($object)
    {
        $id_customer = $this->getTableLink($object->placeId);

        $this->parseCustomer($object, "update", $id_customer);
    }

    private function parseCustomer($object, $action, $id_customer = null)
    {
        $customer = new \Customer($id_customer, Configuration::get('PS_LANG_DEFAULT'));
        $customer->firstname = "Agence";
        $customer->lastname = $object->name;
        $customer->email = $object->mail;
        $customer->passwd = Tools::passwdGen();
        $customer->active = 1;

        SwitchAction::handleCrud($customer, $action);

        return $customer->id;
    }

    public function setTableLink($id_customer, $id_crd)
    {
        return self::setTableLinkStatic($id_customer, $id_crd);
    }

    public static function setTableLinkStatic($id_customer, $id_crd)
    {
        $datas = ["id_customer" => $id_customer, "id_crd" => $id_crd];
        return Db::getInstance()->insert('dioqaapiconnexion_customer', $datas);
    }

    public function getTableLink($id_crd)
    {
        return self::getTableLinkStatic($id_crd);
    }

    public static function getTableLinkStatic($id_crd)
    {
        $sql = new DbQuery();
        $sql->select('id_customer');
        $sql->from('dioqaapiconnexion_customer');
        $sql->where('id_crd = ' . $id_crd);

        return Db::getInstance()->getValue($sql);
    }


    /**
     * Ajoute les produits à un client pour le module marketplace
     * @param $id_crd int id place
     * @param $id_product int id product
     * @return bool
     */
    public function setProductToCustomer($id_crd, $id_product)
    {
        $id_customer = $this->getTableLink($id_crd);

        if (!$id_customer) {
            return false;
        }

        $id_seller = Db::getInstance()->getValue('SELECT id_seller FROM ' . _DB_PREFIX_ . 'wk_mp_seller WHERE seller_customer_id = ' . $id_customer);

        if (!$id_seller) {
            $id_seller = $this->addSeller($id_customer);
        }

        $datas = [
            "id_seller" => $id_seller,
            "id_ps_product" => $id_product,
            "id_mp_shop_default" => 1,
            "id_mp_duplicate_product_parent" => 0,
            "status_before_deactivate" => 1,
            "admin_assigned" => 1,
            "admin_approved" => 1,
            "is_pack_product" => 0,
            "pack_stock_type" => 0,
            "date_upd" => date("Y-m-d H:i:s")
        ];

        $exist = Db::getInstance()->getValue('SELECT id_seller FROM ' . _DB_PREFIX_ . 'wk_mp_seller_product WHERE id_ps_product = ' . $id_product);

        if ($exist) {
            return Db::getInstance()->update('wk_mp_seller_product', $datas, 'id_ps_product = ' . $id_product);
        }

        $datas['date_add'] = date("Y-m-d H:i:s");
        return Db::getInstance()->insert('wk_mp_seller_product', $datas);
    }

    private function addSeller($id_customer)
    {
        $customer = new \Customer($id_customer, Configuration::get('PS_LANG_DEFAULT'));

        $uniqueName = $customer->firstname . " " . $customer->lastname;
        $datas = [
            "shop_name_unique" => pSQL($uniqueName),
            "link_rewrite" => Tools::link_rewrite($uniqueName),
            "seller_firstname" => pSQL($customer->firstname),
            "seller_lastname" => pSQL($customer->lastname),
            "business_email" => pSQL($customer->email),
            "id_country" => 0,
            "id_state" => 0,
            "default_lang" => (int) Configuration::get('PS_LANG_DEFAULT'),
            "active" => 1,
            "shop_approved" => 1,
            "seller_customer_id" => (int) $id_customer,
            "id_shop" => 1,
            "id_shop_group" => 1,
            "seller_details_access" => json_encode(["1", "2", "3", "4", "5", "6", "7", "8", "9"]),
            "date_add" => date("Y-m-d H:i:s"),
            "date_upd" => date("Y-m-d H:i:s"),
        ];

        //insert
        Db::getInstance()->insert('wk_mp_seller', $datas);
        $id_seller =  Db::getInstance()->Insert_ID();

        $datas_lang = [
            "id_seller" => $id_seller,
            "id_lang" => (int) Configuration::get('PS_LANG_DEFAULT'),
            "shop_name" => pSQL($customer->lastname),
        ];

        Db::getInstance()->insert('wk_mp_seller_lang', $datas_lang);

        return $id_seller;
    }
}
