<?php

namespace Dioqaapiconnexion\Entity\Carac;

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
use Dioqaapiconnexion\Entity\CategoryCrd;
use PrestaShopException;

use Dioqaapiconnexion\Entity\FeatureValueCrd;


abstract class Main
{
    protected $ID_FEATURE;
    protected $ID_CATEGORY_PARENT;
    protected $TYPE;

    public function setFeatureValue($object)
    {
        $obj = $this->formatDataFeatureValue($object);

        $fv = new FeatureValueCrd();
        $fv->id_feature = $this->ID_FEATURE;
        if ($fv->getTableLink($obj->id, $this->TYPE)) {
            $fv->update($obj);
        } else {
            $fv->add($obj);
        }
    }

    public function formatDataFeatureValue($object)
    {
        $name = $this->TYPE . 'Id';
        return (object)[
            "id" => $object->$name,
            "name" => $object->name ?: 'Inconnu',
            "type" => $this->TYPE
        ];
    }
}
