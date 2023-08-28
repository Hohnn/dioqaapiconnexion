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



class Model extends Main
{
    protected $ID_FEATURE;
    protected $TYPE = 'model';

    public function __construct()
    {
        $this->ID_FEATURE = Configuration::get('DIOQAAPICONNEXION_IDFEATURE_' . strtoupper($this->TYPE));
    }

    public function formatDataFeatureValue($object)
    {
        $id = $this->TYPE . 'Id';
        /* $name =  $object->brandName . ' ' . $object->name; */
        return (object)[
            "id" => $object->$id,
            "name" => $object->name,
            "type" => $this->TYPE
        ];
    }

    public function createCategoryThree($object)
    {
        $this->createCatBrand($object);

        $this->createCatProductType($object);

        $this->createCatModel($object);
    }

    public function createCatBrand($object)
    {
        $brandId = $object->brandId;
        $brandName = $object->brandName;

        $cat = new CategoryCrd();
        $this->ID_CATEGORY_PARENT = $cat::ID_MAIN_CAT;

        $type = 'brand-' . $brandId;

        $obj = $this->formatDataCategory($brandId, $brandName, $type, $object->status);
        $this->setCategory($obj);
    }

    public function createCatProductType($object)
    {
        $productTypeId = $object->productTypeId;
        $productTypeName = $object->productTypeName . ' ' . $object->brandName;

        $typeBrand = 'brand-' . $object->brandId;

        $cat = new CategoryCrd();

        $this->ID_CATEGORY_PARENT = $cat->getTableLink($object->brandId, $typeBrand);

        $type = $typeBrand . '-' . 'productType-' . $productTypeId;

        $obj = $this->formatDataCategory($productTypeId, $productTypeName, $type, $object->status);
        $this->setCategory($obj);
    }

    public function createCatModel($object)
    {
        $modelId = $object->modelId;
        $modelName = $object->name;

        $typeBrand = 'brand-' . $object->brandId;
        $typeproductType = $typeBrand . '-' . 'productType-' . $object->productTypeId;

        $cat = new CategoryCrd();

        $this->ID_CATEGORY_PARENT = $cat->getTableLink($object->productTypeId, $typeproductType);

        $type = $typeproductType . '-' . 'model-' . $modelId;

        $obj = $this->formatDataCategory($modelId, $modelName, $type, $object->status);
        $this->setCategory($obj);
    }

    private function setCategory($obj)
    {
        $fv = new CategoryCrd();
        $fv->id_category_parent = $this->ID_CATEGORY_PARENT;
        if ($fv->getTableLink($obj->id, $obj->type)) {
            $fv->update($obj);
        } else {
            $fv->add($obj);
        }
    }

    private function formatDataCategory($id, $name, $type, $status)
    {
        return (object)[
            "id" => $id,
            "name" => $name,
            "type" => $type,
            "status" => $status
        ];
    }
}
