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



class RamCapacity extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'ramCapacity';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }

    public function formatDataFeatureValue($object)
    {
        return (object)[
            "id" => $object->ramCapacityId,
            "name" => $object->ram,
            "type" => $this->TYPE
        ];
    }
}
