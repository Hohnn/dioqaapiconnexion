<?php

namespace Dioqaapiconnexion\Controller;

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


class SwitchAction
{
    /* public static function handleCrud($object, $action)
    {
        switch ($action) {
            case "create":
                $ligne =  Db::getInstance()->getRow(
                    'SELECT * FROM ' . 'ps_' . $object::$definition['table'] . ' WHERE ' . $object::$definition['primary'] . ' = ' . $object->id
                );

                if ($ligne) {
                    $object->update();
                } else {
                    $object->force_id = true;
                    $object->add();
                }

                break;
            case "update":
                $ligne =  Db::getInstance()->getRow(
                    'SELECT * FROM ' . 'ps_' . $object::$definition['table'] . ' WHERE ' . $object::$definition['primary'] . ' = ' . $object->id
                );

                if ($ligne) {
                    $object->update();
                } else {
                    $object->force_id = true;
                    $object->add();
                }

                break;
            case "delete":
                $object->delete();
                break;
        }
    } */

    public static function handleCrud($object, $action)
    {
        switch ($action) {
            case "create":
                $object->add();
                break;
            case "update":
                $object->update();
                break;
            case "delete":
                $object->delete();
                break;
        }
    }
}
