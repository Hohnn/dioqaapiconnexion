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
use Dioqaapiconnexion\Controller\ApiController;
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
        $this->createCatProductType($object);
        $this->createCatSeoProductType($object);

        $this->createCatBrand($object);

        $haveGroup = $this->createCatGroup($object);

        $this->createCatModel($object, $haveGroup);

        if ($haveGroup) {
            $group = $this->getGroupDetail($object->groupId);
            if ($group->isSeo == true) {
                $this->createCatSeoBrand($object);
                $this->createCatSeoGroup($object);
            }
        }
    }

    public function createCatProductType($object)
    {
        $productTypeId = $object->productTypeId;
        $productTypeName = $object->productTypeName;

        $cat = new CategoryCrd();

        $this->ID_CATEGORY_PARENT = $cat::ID_MAIN_CAT;

        $type = 'productType-' . $productTypeId;

        $obj = $this->formatDataCategory($productTypeId, $productTypeName, $type, $object->status);
        $this->setCategory($obj);
    }

    public function createCatBrand($object)
    {
        $brandId = $object->brandId;
        $brandName = $object->brandName;

        $productTypeId = $object->productTypeId;
        $productTypeName = $object->productTypeName;

        $brandName = $productTypeName . ' ' . $brandName;

        $typePT = 'productType-' . $productTypeId;

        $cat = new CategoryCrd();
        $this->ID_CATEGORY_PARENT = $cat->getTableLink($productTypeId, $typePT);

        $type = $typePT . '-' . 'brand-' . $brandId;

        $obj = $this->formatDataCategory($brandId, $brandName, $type, $object->status);
        $this->setCategory($obj);
    }

    public function createCatGroup($object)
    {
        $brandId = $object->brandId;

        $productTypeId = $object->productTypeId;

        $groupId = $object->groupId;
        $groupName = $object->groupName;

        if ($groupId == null || $groupName == null) {
            return false;
        }

        $typePT = 'productType-' . $productTypeId;
        $typeBrand = $typePT . '-' . 'brand-' . $brandId;

        $cat = new CategoryCrd();
        $this->ID_CATEGORY_PARENT = $cat->getTableLink($brandId, $typeBrand);

        $type = $typeBrand . '-' . 'group-' . $groupId;

        $obj = $this->formatDataCategory($groupId, $groupName, $type, $object->status);
        $this->setCategory($obj);
        return true;
    }

    public function createCatModel($object, $haveGroup)
    {
        $modelId = $object->modelId;
        $modelName = $object->name;

        $brandId = $object->brandId;
        $groupId = $object->groupId;

        $productTypeId = $object->productTypeId;

        $typePT = 'productType-' . $productTypeId;
        $typeBrand = $typePT . '-' . 'brand-' . $brandId;

        $cat = new CategoryCrd();

        if ($haveGroup) {
            $typeGroup = $typeBrand . '-' . 'group-' . $groupId;
            $this->ID_CATEGORY_PARENT = $cat->getTableLink($object->groupId, $typeGroup);
            $type = $typeGroup . '-' . 'model-' . $modelId;
        } else {
            $this->ID_CATEGORY_PARENT = $cat->getTableLink($object->brandId, $typeBrand);
            $type = $typeBrand . '-' . 'model-' . $modelId;
        }

        $obj = $this->formatDataCategory($modelId, $modelName, $type, $object->status, $object->metaDescription);
        $this->setCategory($obj);
    }

    private function createCatSeoProductType($object)
    {
        $productTypeId = $object->productTypeId;
        $productTypeName = $object->productTypeName;
        $productTypeName = $productTypeName . ' reconditionné';

        $cat = new CategoryCrd();

        $this->ID_CATEGORY_PARENT = $cat::ID_SEO_CAT;

        $type = 'productTypeSeo-' . $productTypeId;

        $obj = $this->formatDataCategory($productTypeId, $productTypeName, $type, $object->status);
        $this->setCategory($obj);
    }

    private function createCatSeoBrand($object)
    {
        $brandId = $object->brandId;
        $brandName = $object->brandName;
        $brandName = $brandName . ' reconditionné';

        $cat = new CategoryCrd();
        $this->ID_CATEGORY_PARENT = $cat::ID_SEO_CAT;

        $type = 'brandSeo-' . $brandId;

        $obj = $this->formatDataCategory($brandId, $brandName, $type, $object->status);
        $this->setCategory($obj);
    }

    private function createCatSeoGroup($object)
    {
        $brandId = $object->brandId;

        $groupId = $object->groupId;
        $groupName = $object->groupName;
        $groupName = $groupName . ' reconditionné';

        $typeBrand = 'brandSeo-' . $brandId;

        $cat = new CategoryCrd();
        $this->ID_CATEGORY_PARENT = $cat->getTableLink($brandId, $typeBrand);

        $type = $typeBrand . '-' . 'groupSeo-' . $groupId;

        $obj = $this->formatDataCategory($groupId, $groupName, $type, $object->status);
        $this->setCategory($obj);
    }


    private function getGroupDetail($groupId)
    {
        return ApiController::getInstance()->get("/api/crd/group/$groupId/detail");
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

    private function formatDataCategory($id, $name, $type, $status, $meta = null)
    {
        return (object)[
            "id" => $id,
            "name" => $name,
            "type" => $type,
            "status" => $status,
            "meta" => $meta
        ];
    }
}
