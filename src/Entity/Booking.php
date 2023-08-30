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
use DateTime;

use Dioqaapiconnexion\Entity\ImageCrd;
use Dioqaapiconnexion\Controller\SwitchAction;

class Booking
{
    public $id_product;
    public $id_customer;
    public $id_cart;
    public $id_crd;
    public $quantity;
    public $id;
    public $date_add;
    public $date_expire;
    public $add_time;

    public const ROUTE_UPDATE = "/api/crd/stock/:id/booking";

    public function __construct($id = null)
    {
        if ($id) {
            $this->populate($id);
        }
    }

    public function add()
    {
        $data = [
            'id_product' => $this->id_product,
            'id_crd' => $this->id_crd,
            'id_cart' => $this->id_cart,
            'quantity' => 1,
            'date_expire' => $this->date_expire,
        ];

        Db::getInstance()->insert('dioqaapiconnexion_booking', $data);
        $this->id = Db::getInstance()->Insert_ID();

        return $this->id;
    }

    public function delete()
    {
        $where = "id_product = " . $this->id_product
            . " AND id_cart = " . $this->id_cart
            . " AND id_crd = " . $this->id_crd
            . " AND id_booking = " . $this->id;

        return Db::getInstance()->delete('dioqaapiconnexion_booking', $where);
    }

    public function update()
    {
        $data = [
            'id_product' => $this->id_product,
            'id_crd' => $this->id_crd,
            'id_cart' => $this->id_cart,
            'quantity' => $this->quantity,
            'date_upd' => time(),
            'date_expire' => $this->date_expire,
            'add_time' => $this->add_time,
        ];

        $where = "id_booking = " . $this->id;

        return Db::getInstance()->update('dioqaapiconnexion_booking', $data, $where);
    }

    private function populate($id_booking)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_booking');
        $query->where("id_booking = " . $id_booking);

        if ($result = Db::getInstance()->getRow($query)) {
            $this->id = $result['id_booking'];
            $this->id_product = $result['id_product'];
            $this->id_cart = $result['id_cart'];
            $this->quantity = $result['quantity'];
            $this->id_crd = $result['id_crd'];
            $this->date_expire = $result['date_expire'];
            $this->add_time = $result['add_time'];
        }
    }

    public function exist()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_booking');
        $query->where("id_product = " . $this->id_product);
        $query->where("id_crd = " . $this->id_crd);
        $query->where("id_cart = " . $this->id_cart);

        if ($result = Db::getInstance()->getRow($query)) {
            $this->id = $result['id_booking'];
            return true;
        }

        return false;
    }

    public function handleBookingInBDD()
    {
        $exist = $this->exist();

        if ($exist) {
            if ($this->quantity <= 0) {
                return $this->delete();
            }
            return $this->update();
        }
        return $this->add();
    }

    public static function getBookingIdByDatas($id_product, $id_cart)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_booking');
        $query->where("id_product = " . $id_product);
        $query->where("id_cart = " . $id_cart);

        if ($result = Db::getInstance()->getRow($query)) {
            return $result['id_booking'];
        }

        return false;
    }

    public static function getBookingsByCartId($id_cart)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_booking');
        $query->where("id_cart = " . $id_cart);

        return Db::getInstance()->executeS($query);
    }

    public static function timeDifferenceToNowFormatted($date)
    {
        $dateTime = new DateTime($date);
        $dateTime->modify('-1 minutes');
        $now = new DateTime();

        if ($dateTime < $now) {
            return false;
        }

        $interval = $now->diff($dateTime);

        $formattedTime = sprintf('%02d:%02d:%02d', $interval->h, $interval->i, $interval->s);
        return $formattedTime;
    }
}
