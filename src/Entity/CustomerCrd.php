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

        $this->setTableLink($id_customer, $object->id);
    }

    public function update($object)
    {
        $id_customer = $this->getTableLink($object->id);

        $this->parseCustomer($object, "update", $id_customer);
    }

    private function parseCustomer($object, $action, $id_customer = null)
    {
        $customer = new Customer($id_customer, Configuration::get('PS_LANG_DEFAULT'));
        $customer->firstname = $object->firstname;
        $customer->lastname = $object->lastname;
        $customer->email = $object->email;
        $customer->passwd = $object->passwd;
        $customer->active = $object->status;

        SwitchAction::handleCrud($customer, $action);

        return $customer->id;
    }

    private function setTableLink($id_customer, $id_crd)
    {
        $datas = ["id_customer" => $id_customer, "id_crd" => $id_crd];
        return Db::getInstance()->insert('dioqaapiconnexion_customer', $datas);
    }

    public function getTableLink($id_crd)
    {
        $sql = new DbQuery();
        $sql->select('id_customer');
        $sql->from('dioqaapiconnexion_customer');
        $sql->where('id_crd = ' . $id_crd);

        return Db::getInstance()->getValue($sql);
    }

    public function setProductToCustomer($id_place, $id_product)
    {
        $id_customer = $this->getTableLink($id_place);

        if (!$id_customer) {
            return false;
        }

        $id_seller = Db::getInstance()->getValue('SELECT id_seller FROM ' . _DB_PREFIX_ . 'wk_mp_seller WHERE seller_customer_id = ' . $id_customer);

        if (!$id_seller) {
            return false;
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
}
