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



class RomCapacity extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'romCapacity';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_'  . strtoupper($this->TYPE));
    }

    public function formatDataFeatureValue($object)
    {
        return (object)[
            "id" => $object->romCapacityId,
            "name" => $object->rom,
            "type" => $this->TYPE
        ];
    }
}
