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

/* Useless */

class State extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'state';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }
}
