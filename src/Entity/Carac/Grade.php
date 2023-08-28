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
use PrestaShopException;

use Dioqaapiconnexion\Entity\FeatureValueCrd;

class Grade extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'grade';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }

    public function formatDataFeatureValue($object)
    {
        return (object)[
            "id" => $object->gradeId,
            "name" => $object->gradeName,
            "type" => $this->TYPE
        ];
    }
}
