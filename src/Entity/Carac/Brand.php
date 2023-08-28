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
use Dioqaapiconnexion\Entity\ManufacturerCrd;
use PrestaShopException;



class Brand extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'brand';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }

    public function createBrand($object)
    {
        $obj = $this->formatDataBrand($object);
        $this->setBrand($obj);
    }

    private function setBrand($obj)
    {
        $fv = new ManufacturerCrd();
        if ($fv->getTableLink($obj->id)) {
            $fv->update($obj);
        } else {
            $fv->add($obj);
        }
    }

    private function formatDataBrand($object)
    {
        return (object)[
            "id" => $object->brandId,
            "name" => $object->name,
            "image" => $object->image,
            "status" => $object->status
        ];
    }
}
