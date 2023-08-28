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
use Dioqaapiconnexion\Controller\ApiController;
use PrestaShopException;



class Capacity extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'capacity';
    private $units = [];

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }

    public function formatDataFeatureValue($object)
    {
        $unit = $object->unitId;
        $id = $this->TYPE . 'Id';
        $name =  $object->value . ' ' . $unit;
        return (object)[
            "id" => $object->$id,
            "name" => $name,
            "type" => $this->TYPE
        ];
    }
}
