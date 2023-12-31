<?php

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Dioqaapiconnexion\Controller\ApiController;
use Dioqaapiconnexion\Entity\Carac\Brand;
use Dioqaapiconnexion\Entity\Carac\Color;
use Dioqaapiconnexion\Entity\Carac\Capacity;
use Dioqaapiconnexion\Entity\Carac\ComponentQuality;
use Dioqaapiconnexion\Entity\Carac\ComponentType;
use Dioqaapiconnexion\Entity\Carac\Model;
use Dioqaapiconnexion\Entity\Carac\ProductType;
use Dioqaapiconnexion\Entity\Carac\RepairType;
use Dioqaapiconnexion\Entity\FeatureCrd;
use Dioqaapiconnexion\Entity\FeatureValueCrd;
use Dioqaapiconnexion\Entity\ProductCrd;
use Dioqaapiconnexion\Entity\Booking;
use Dioqaapiconnexion\Entity\CategoryCrd;
use Dioqaapiconnexion\Entity\CustomerCrd;
use PrestaShop\PrestaShop\Core\Domain\Category\ValueObject\CategoryId;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShopBundle\Form\Admin\Type\TranslateType;
use \Symfony\Component\Form\Extension\Core\Type\TextType;


class Dioqaapiconnexion extends Module implements WidgetInterface
{
    protected $config_form = false;

    public $success = 0;
    public $fail = 0;
    public $action = null;
    public $route = null;
    public $API_route = null;
    public $queueType = 'base';
    public $id_place = 2;

    public function __construct()
    {
        $this->name = 'dioqaapiconnexion';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'DIOQA';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DIOQA api connexion');
        $this->description = $this->l('Connexion api avec prestashop');

        $this->confirmUninstall = $this->l('Si vous désinstaller ce module les produits ne seront plus à jour');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return
            /* $this->installDb() && 
            $this->installFeatures() && */
            Configuration::updateValue('DIOQAAPICONNEXION_LIVE_MODE', false) &&
            parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionCartUpdateQuantityBefore') &&
            $this->registerHook('actionCartUpdateQuantityAfter') &&
            $this->registerHook('actionObjectProductInCartDeleteAfter') &&
            $this->registerHook('actionCustomerFormBuilderModifier') &&
            $this->registerHook('actionAfterUpdateCustomerFormHandler') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionPresentProduct') &&
            $this->registerHook('displayProductActions') &&
            $this->registerHook('actionProductUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('DIOQAAPICONNEXION_LIVE_MODE');

        return parent::uninstall();
    }

    public function installDb()
    {
        $return = true;
        $sql = include __DIR__ . '/sql_install.php';
        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }

        return $return;
    }

    public function installFeatures()
    {
        $feat = new FeatureCrd();
        $feat->setinitalFeatures();

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitDioqaapiconnexionModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDioqaapiconnexionModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'DIOQAAPICONNEXION_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'DIOQAAPICONNEXION_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'DIOQAAPICONNEXION_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'DIOQAAPICONNEXION_LIVE_MODE' => Configuration::get('DIOQAAPICONNEXION_LIVE_MODE', true),
            'DIOQAAPICONNEXION_ACCOUNT_EMAIL' => Configuration::get('DIOQAAPICONNEXION_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'DIOQAAPICONNEXION_ACCOUNT_PASSWORD' => Configuration::get('DIOQAAPICONNEXION_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->registerJavascript(
            $this->name . '_js',
            'modules/' . $this->name . '/views/js/front.js',
            [
                'attributes' => 'defer',
                'priority' => 1000,
                'position' => 'bottom'
            ]
        );
        $this->context->controller->registerJavascript(
            $this->name . '_modal_js',
            'modules/' . $this->name . '/views/js/modal.js',
            [
                'attributes' => 'defer',
                'priority' => 1000,
                'position' => 'bottom'
            ]
        );
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');

        if (isset($this->context->controller->php_self)  && $this->context->controller->php_self == 'order') {
            $this->addBookingMoreTime();
        }

        if (isset($this->context->controller->php_self)  && $this->context->controller->php_self == 'product') {
            $this->context->controller->registerJavascript(
                $this->name . '_product_js',
                'modules/' . $this->name . '/views/js/product.js',
                [
                    'attributes' => 'defer',
                    'priority' => 1000,
                    'position' => 'bottom'
                ]
            );
        }
    }

    public function hookActionCartUpdateQuantityBefore($params)
    {
        /* add booking qty check */
    }

    public function hookActionAfterUpdateCustomerFormHandler(array $params)
    {
        $customerId = $params['id'];
        $companyId = $params['form_data']['id_place'][1];
        $this->storeDescription($customerId, $companyId);
    }

    protected function storeDescription($customerId, $data)
    {
        Db::getInstance()->insert(
            'dioqaapiconnexion_customer',
            ['id_crd' => (int) $data, 'id_customer' => (int) $customerId],
            false,
            true,
            Db::ON_DUPLICATE_KEY
        );
    }

    public function hookActionCustomerFormBuilderModifier(array $params)
    {
        $customerId = $params['id'];
        $formBuilder = $params['form_builder'];
        $locales = $this->get('prestashop.adapter.legacy.context')->getLanguages();

        $formBuilder->add(
            'id_place',
            TranslateType::class,
            [
                'type' => TextType::class,
                'label' => "Société ID",
                'locales' => $locales,
                'hideTabs' => false,
                'required' => false
            ]
        );

        foreach ($locales as $locale) {
            $langId = $locale['id_lang'];
            $params['data']['id_place'][$langId] = $this->getDescription($customerId);
        }
        $formBuilder->setData($params['data']);
    }

    protected function getDescription($customerId)
    {
        if ((int) $customerId) {
            $result = Db::getInstance()->getValue('SELECT `id_crd` FROM `' . _DB_PREFIX_ . 'dioqaapiconnexion_customer` WHERE `id_customer` = ' . $customerId);
            return $result;
        }
        return false;
    }


    public function hookActionCartUpdateQuantityAfter($params)
    {
        try {
            $this->addBooking(
                $params['product']->id,
                $params['cart']->id,
            );
        } catch (Throwable $th) {
            $this->setLogTest(
                'hookActionCartUpdateQuantityAfter : ' . $th->__toString(),
                null,
                __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }
    }

    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        try {
            $this->deleteBooking($params['id_product'], $params['id_cart']);
        } catch (\Exception $th) {
            $this->setLogTest(
                'hookActionObjectProductInCartDeleteAfter : ' . $th->__toString(),
                null,
                __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }
    }

    public function hookActionValidateOrder($params)
    {
        try {
            $this->deleteBookingAfterOrder($params);
            $this->disableOrderProducts($params);
            $this->sendOrder($params);
        } catch (\Throwable $th) {
            $this->setLogTest(
                'hookActionValidateOrder : ' . $th->__toString(),
                null,
                __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }
    }

    /* Mise a jour du produit depuis le BO */
    public function hookActionProductUpdate($params)
    {
        try {
            if ($params['product']->active) {
                $productCrd = new ProductCrd($params['id_product']);
                if ($productCrd->isPublished()) {
                    return;
                }

                $this->sendDevicePublish($productCrd->getCRDProductId());

                $productCrd->setPublished();
            }
        } catch (\Throwable $th) {
            $this->setLogTest(
                'hookActionProductUpdate : ' . $th->__toString(),
                null,
                __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }
    }

    /**
     * @param array{presentedProduct: ProductLazyArray} $params
     * @return void
     */
    public function hookActionPresentProduct(array &$params)
    {
        /* if (isset($this->context->controller->php_self)  && $this->context->controller->php_self == 'product') {
            $this->context->controller->registerJavascript(
                $this->name . '_product_js',
                'modules/' . $this->name . '/views/js/product.js',
                [
                    'attributes' => 'defer',
                    'priority' => 1000,
                    'position' => 'bottom'
                ]
            );

            $id_product = $params['presentedProduct']->getId();

            $this->context->smarty->assign([
                'isBookingPossible' => $this->isBookingPossible($id_product)
            ]);
        } */
    }

    public function hookDisplayProductActions($params)
    {
        /* $this->setLogTest(
            'hookDisplayProductActions : ' . json_encode($params['product']->id),
            null,
            __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
        ); */

        return $this->display(__FILE__, 'views/templates/hook/addToCartBtn.tpl');
    }

    public function hookDisplayBeforeBodyClosingTag(array &$params)
    {
        if ($this->context->cart->id) {
            $bookings = Booking::getBookingsByCartId($this->context->cart->id);

            $productCats = [];
            $products = [];

            if ($bookings) {
                $olderBook = $bookings[0];
                $date = $olderBook['date_expire'];
                $countDown = Booking::timeDifferenceToNowFormatted($date);

                if (!$countDown) {
                    [$productCats, $products] = $this->deleteProductNotAvailable();
                }
            }

            $this->smarty->assign([
                'products' => $products,
                'productCats' => $this->getCatInfoByCatIds($productCats)
            ]);

            return $this->display(__FILE__, 'views/templates/hook/modals.tpl');
        }
    }

    private function getCatInfoByCatIds($ids)
    {
        $productCats = [];
        foreach (array_unique($ids) as $id) {
            $cat = new Category($id);
            $productCats[] = [
                'link' => $cat->getLink(),
                'name' => $cat->name[$this->context->language->id],
                'id_image' => $cat->id_image,
                'link_rewrite' => $cat->link_rewrite[$this->context->language->id]
            ];
        }
        return $productCats;
    }

    private function deleteProductNotAvailable()
    {
        $cart = new Cart($this->context->cart->id);
        $productsInCart = $cart->getProducts(false, false, null, false);

        $productCats = [];
        $products = [];
        foreach ($productsInCart as $key => $product) {
            $productCats[] = $product['id_category_default'];

            $productCrd = new ProductCrd($product['id_product']);
            $id_crd = $productCrd->getCRDProductId();
            $stock = $this->getCRDStockByProductId($id_crd);

            if ($stock->bookingQuantity < $stock->quantity) {
                $products[] = $product;
            }

            /* if (count($stock->bookings) && $stock->bookings[0]->cartId == $this->context->cart->id) {
                continue;
            } */

            $cart->deleteProduct($product['id_product'], $product['id_product_attribute'], $product['id_customization']);
            $this->deleteBooking($product['id_product'], $this->context->cart->id);
        }

        return [$productCats, $products];
    }

    /**
     * Affichage du widget
     * @param type $hookName
     * @param array $configuration : Ensemble des variables du widget
     * @return array
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (isset($configuration['action']) && $configuration['action'] == "displayBookingTimer") {
            return $this->renderTimer();
        }

        if (isset($configuration['action']) && $configuration['action'] == "displayAddCharger") {
            $this->smarty->assign($configuration);
            return $this->display(__FILE__, 'views/templates/widget/addCharger.tpl');
        }

        if (isset($configuration['action']) && $configuration['action'] == "displayBooked") {
            $id_product = $configuration['id_product'];

            $this->smarty->smarty->assign([
                'isBookingPossible' => $this->isBookingPossible($id_product)
            ]);
            /* $this->smarty->assign($configuration); */
            return $this->display(__FILE__, 'views/templates/widget/isBooked.tpl');
        }
    }

    private function renderTimer()
    {
        if ($this->context->cart->id) {
            $bookings = Booking::getBookingsByCartId($this->context->cart->id);
            if (!empty($bookings)) {

                $olderBook = $bookings[0];

                $date = $olderBook['date_expire'];

                $countDown = Booking::timeDifferenceToNowFormatted($date);

                $this->smarty->assign([
                    'date' => $date,
                    'timed' => $countDown
                ]);
            }

            return $this->display(__FILE__, 'views/templates/widget/timer.tpl');
        }
    }


    /**
     * Récupération des variables du widget
     * @param type $hookName
     * @param array $configuration
     * @return array
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {

        return  [
            'test' => 'test_var',
            'test2' => 'test_var2'
        ];
    }


    private function sendDevicePublish($id)
    {
        $route = "/api/crd/device/$id/publish";
        return ApiController::getInstance()->post($route, []);
    }




    public function dev()
    {
        echo 'DEV';
        echo '<pre>';
        try {
        } catch (\Throwable $e) {
            var_dump($e->__toString());
        }
    }





    public function setTasksFromAPI($types = [])
    {
        /* $types = ['category', 'brand', 'feature', 'product', 'stock'];
        $types = ['product']; */

        foreach ($types as $action) {
            $this->dispatchTasks($action);
        }
    }

    public function executeTasksFromBDD($test = false)
    {
        /* if (!$test) {
            return;
        } */

        $startTime = time();

        while (time() - $startTime < 60) { /* 60 seconds */
            $task = $this->getTask();

            if (!$task) {
                break;
            }

            $this->dispatchTasks($task['action']);
            $this->updateTaskStatus('Processing', $task['id_task']);
            $data = json_decode($task['data']);

            try {
                $this->executeTasks($data);
                $this->updateTaskStatus('Done', $task['id_task']);
            } catch (Throwable $e) {
                $this->setTaskError($e->__toString(), $task['id_task']);
                var_dump($e);
            }

            if ($test) {
                break;
            }
        }
    }

    public function dispatchTasks($action)
    {
        $this->action = $action;
        $this->queueType = "base";

        switch ($action) {
            case 'product':
                $this->route = "device";
                $this->API_route = "/api/crd/devices";
                $this->getDatas();
                break;
            case 'category':
                $this->route = "model";
                $this->API_route = "/api/crd/essentials/model";
                $this->getDatas();
                break;
            case 'brand':
                $this->route = "brand";
                $this->API_route = "/api/crd/essentials/brand";
                $this->getDatas();
                break;
            case 'feature':
                $this->route = null;
                $this->API_route = "/api/crd/essentials/";
                $this->getDatas();
                break;
            case 'stock':
                $this->route = "stock";
                $this->API_route = "/api/crd/devices";
                $this->getDatas();
                break;
                /* case 'orderCategory':
                $this->route = "orderCategory";
                $this->API_route = "/api/crd/average";
                $this->getDatas();
                break; */
            case 'product_crd':
                $this->route = "product_crd";
                $this->API_route = "/api/crd/products";
                $this->getDatas();
                break;
            default:
                break;
        }
    }

    private function executeTasks($data)
    {
        switch ($this->action) {
            case 'product':
                $this->setProduct($data);
                $this->setStock($data);
                break;
            case 'category':
                $this->setCategory($data);
                break;
            case 'brand':
                $this->setBrand($data);
                break;
            case 'feature':
                $this->setFeature($data);
                break;
            case 'stock':
                $this->setStock($data);
                break;
                /* case 'orderCategory':
                $this->orderCategory($data);
                break; */
            case 'product_crd':
                $this->setCategoriesImage($data);
                break;
            default:
                break;
        }
    }

    private function getDatas()
    {
        $lastDate = $this->getApiCallLastDate($this->action);
        $date = $lastDate ? "?date=$lastDate" : "";

        $this->setApiCallState($this->action);

        if ($this->action === 'feature') {
            $features = FeatureCrd::$featureListToWatch;
            $this->processFeatureData($date, $features);
        } else {
            $this->processNormalData($date);
        }
    }

    private function processFeatureData($date, $features)
    {
        foreach ($features as $featureName) {
            $this->route = $featureName;
            $datas = ApiController::getInstance()->get($this->API_route . $featureName . $date);
            $this->processData($datas, $featureName);
        }
    }

    private function processNormalData($date)
    {
        $datas = ApiController::getInstance()->get($this->API_route . $date);
        $this->processData($datas);
    }

    private function processData($datas, $featureName = null)
    {
        foreach ($datas as $key => $data) {
            /* if ($this->action === 'category' && $data->groupId == null) {
                continue;
            } */
            if ($featureName !== null) {
                $data->featureName = $featureName;
            }
            try {
                $this->setTask($data);
                $this->handleSuccess($key, $data);
            } catch (\Throwable $e) {
                $this->handleFail($key, $data, $e->__toString());
            }
        }
    }

    private function setTask($message)
    {
        $data = [
            "status" => 'Waiting',
            "action" => $this->action,
            "data" => pSQL(json_encode($message)),
        ];
        return Db::getInstance()->insert('dioqaapiconnexion_task', $data);
    }

    private function updateTaskStatus($status, $id_task)
    {
        $data = [
            "status" => $status
        ];
        return Db::getInstance()->update('dioqaapiconnexion_task', $data, "id_task = $id_task");
    }

    private function setTaskError(string $error, int $id_task)
    {
        $data = [
            "error" => pSQL($error),
        ];
        return Db::getInstance()->update('dioqaapiconnexion_task', $data, "id_task = $id_task");
    }

    private function getTask()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('dioqaapiconnexion_task');
        $query->where("status = 'Waiting'");

        return Db::getInstance()->getRow($query);
    }

    private function setProduct($data)
    {
        $productId = $data->productId;
        $product = ApiController::getInstance()->get("/api/crd/product/$productId/detail");

        if (is_object($product)) {
            $data->productImages = [
                $product->image,
                $product->image_2
            ];
        }

        $pcc = new ProductCrd();
        $pcc->setProduct($data);
    }

    private function setCategory($data)
    {
        $id = $data->modelId;
        $modelDetail = ApiController::getInstance()->get("/api/crd/model/$id/detail");
        $model = new Model();
        $model->createCategoryThree($modelDetail);
    }

    private function setBrand($data)
    {
        $model = new Brand();
        $model->createBrand($data);
    }

    private function setFeature($data)
    {
        $this->route = $data->featureName;

        $class = FeatureCrd::getClass($data->featureName);

        $class->setFeatureValue($data);
    }

    private function setStock($data)
    {
        $pcc = new ProductCrd();
        $pcc->setStock($data);
    }

    public function setLog(string $log, array $array = null): void
    {
        if ($array !== null) {
            $log .= ' : ' . json_encode($array, JSON_PRETTY_PRINT);
        }

        $date = date('Y-m-d H:i:s');
        $strLog = "==================================$date==================================\n => $log \n";
        error_log($strLog, 3, __DIR__ . '/logs_' . $this->queueType . '/log_' . date('y-m-d-H') . 'h.log');
    }

    public function setLogTest(string $log, array $array = null, $path): void
    {
        if ($array !== null) {
            $log .= ' : ' . json_encode($array, JSON_PRETTY_PRINT);
        }

        $date = date('Y-m-d H:i:s');
        $strLog = "==================================$date==================================\n => $log \n";
        error_log($strLog, 3, $path);
    }

    public function createJson($msg)
    {
        $times = uniqid();
        $name = $this->route . '_' . $times;
        return file_put_contents(__DIR__ . "/bugs_json/$name.json", json_encode($msg, JSON_PRETTY_PRINT));
    }

    private function handleSuccess($key, $value)
    {
        /* $this->setLog('SUCCESS ' . $this->route . ' ' . ($key + 1) . ' : ' . json_encode($value)); */
        $this->success++;
    }

    private function handleFail($key, $value, $e)
    {
        var_dump('handleFail : ' . $e);
        $this->setLog('FAIL ' . $this->route . ' ' . ($key + 1) . ' : ' . $e . ' : ' . json_encode($value));
        $this->fail++;
        $value->error = $e;
        $this->createJson($value);
    }

    /**
     *  Récupère la date du dernier call à l'api suivant le type
     *
     * @param String $type
     * @return mixed
     */
    private function getApiCallLastDate($type)
    {
        $filePath = _PS_MODULE_DIR_ . $this->name . "/apicall_state.json";

        $jsonString = file_get_contents($filePath);
        $data = json_decode($jsonString);

        return isset($data->$type) ? $data->$type->date : null;
    }

    /**
     * Ecrit dans le json suivant le type
     *
     * @param String $type
     * @return bool
     */
    private function setApiCallState($type)
    {
        $filePath = _PS_MODULE_DIR_ . $this->name . "/apicall_state.json";

        $jsonString = file_get_contents($filePath);
        $data = json_decode($jsonString);
        $date = date('Y-m-d\TH:i:s');
        $obj = (object) [
            "date" => $date,
            "success" => $this->success,
            "fail" => $this->fail,
        ];
        $data->$type = $obj;

        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);
        return (bool) file_put_contents($filePath, $newJsonString);
    }

    public function addBooking($id_product, $id_cart)
    {
        $query = new DbQuery();
        $query->select('quantity');
        $query->from('cart_product');
        $query->where("id_cart = $id_cart AND id_product = $id_product");

        $reelQuantity = Db::getInstance()->getValue($query);

        if (!$reelQuantity) {
            throw new PrestaShopException("Quantity in cart for product id : " . $id_product . " and cart id : $id_cart does not exist");
            return false;
        }

        $book = new Booking();
        $book->quantity = $reelQuantity;
        $book->id_product = $id_product;
        $book->id_cart = $id_cart;
        $book->id_crd = (new ProductCrd($id_product))->getCRDProductId();

        return $this->handleBooking($book);
    }

    public function deleteBooking($id_product, $id_cart)
    {
        return $this->updateBooking($id_product, $id_cart, 0);
    }

    private function deleteBookingAfterOrder($params)
    {
        foreach ($params['order']->product_list as $key => $product) {
            $this->deleteBooking($product['id_product'], $params['cart']->id);
        }
    }

    private function sendOrder($params)
    {
        $order = $params['order'];
        $customer = $params['customer'];
        $orderStatus = $params['orderStatus'];
        $products = $order->product_list;

        $productDatas = $this->getProductsDatasForOrder($products);
        $customerDatas = $this->getCustomersDatasForOrder($customer, $order->id_address_delivery);

        $data = [
            "orderId" => (int) $order->id,
            "orderDate" => $order->date_add,
            "shippingPrice" => (float) $order->total_shipping_tax_incl,
            "totalPrice" => (float) $order->total_paid_tax_incl,
            "paymentMethod" => $order->payment,
            "paymentPrice" => (float) $order->total_paid_real,
            "orderState" => $orderStatus->name,
            "customer" => $customerDatas,
            "content" => $productDatas
        ];

        $this->setLogTest(
            'sendOrder row',
            $params,
            __DIR__ . '/logs_order/log_' . date('y-m-d-H') . 'h.log'
        );

        $this->setLogTest(
            'sendOrder formatted',
            $data,
            __DIR__ . '/logs_order/log_' . date('y-m-d-H') . 'h.log'
        );

        return ApiController::getInstance()->post("/api/crd/order", $data);
    }

    private function getProductsDatasForOrder($products)
    {
        $productDatas = [];

        foreach ($products as $product) {
            $pr = new ProductCrd($product['id_product']);
            $id_crd = $pr->getCRDProductId();
            $data = [
                "deviceId" => (int) $id_crd,
                "quantity" => (int) $product['quantity'],
                "unitPrice" => (float) $product['price_wt'],
                "withCharger" => (bool) $this->withCharger($product),
            ];

            array_push($productDatas, $data);
        }

        return $productDatas;
    }

    private function getCustomersDatasForOrder($customer, $id_address_delivery)
    {
        $address = new Address($id_address_delivery);

        $gender = new Gender($customer->id_gender);

        return [
            "id_customer" => (int) $customer->id,
            "firstname" => $customer->firstname,
            "lastname" => $customer->lastname,
            "address1" => $address->address1,
            "address2" => $address->address2,
            "postcode" => $address->postcode,
            "city" => $address->city,
            "phone" => $address->phone,
            "email" => $customer->email,
            "gender" => $gender->name[$this->context->language->id],
        ];
    }

    private function withCharger($product)
    {
        $id_customization = $product['id_customization'];

        if ($id_customization == 0) {
            return false;
        }

        //get customization data in bdd
        $query = new DbQuery();
        $query->select('value')
            ->from('customized_data')
            ->where("id_customization = $id_customization");

        $customization_data = Db::getInstance()->getValue($query);

        if (!$customization_data) {
            return;
        }

        if ($customization_data == 'Avec chargeur') {
            return true;
        } else if ($customization_data == 'Sans chargeur') {
            return false;
        }
    }

    private function disableOrderProducts($params)
    {
        $order = $params['order'];
        $products = $order->product_list;

        foreach ($products as $product) {
            $pr = new ProductCrd($product['id_product']);
            $pr->disableProduct();
        }
    }

    private function updateBooking($id_product, $id_cart, $quantity, $addTime = false)
    {
        $id_booking = Booking::getBookingIdByDatas($id_product, $id_cart);

        if (!$id_booking) {
            throw new PrestaShopException("Booking for product id : " . $id_product . " and cart id " . $id_cart . " does not exist");
            return false;
        }

        $book = new Booking($id_booking);
        $book->quantity = $quantity;

        $this->handleBooking($book, $addTime);
    }

    private function handleBooking(Booking $book, $addTime = false)
    {
        $stock = $this->getCRDStockByProductId($book->id_crd);

        if (!$stock) {
            throw new PrestaShopException("Stock for product id : " . $book->id_product . " does not exist");
            return false;
        }

        $route = str_replace(":id", $stock->stockId, Booking::ROUTE_UPDATE);

        $data = (object)[
            "cartId" => $book->id_cart,
            "quantity" => $book->quantity,
            "time" => $addTime
        ];

        $bookingCrd = null;

        try {
            $bookingCrd = ApiController::getInstance()->post($route, $data);
            if (isset($bookingCrd->dateValidity)) {
                $book->date_expire = $bookingCrd->dateValidity;
                $book->add_time = $addTime;
            }
        } catch (\Throwable $th) {
            $this->setLogTest(
                'handleBooking : ' . $th->__toString(),
                [$book, $route, $data, $bookingCrd],
                __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }



        return $book->handleBookingInBDD();
    }


    public function getCRDStockByProductId($id_crd)
    {
        $stocks = ApiController::getInstance()->get("/api/crd/stocks/device/$id_crd");

        if (!is_array($stocks)) {
            return false;
        }

        /* $placeId = 999;

        $stock = array_filter($stocks, function ($e) use ($placeId) {
            return $e->placeId == $placeId;
        });

        if (empty($stock)) {
            return false;
        } */

        return array_shift($stocks);
    }

    public function isBookingPossible($id_product)
    {
        $product = new ProductCrd($id_product);
        $id_crd = $product->getCRDProductId();
        $stocks = ApiController::getInstance()->get("/api/crd/stocks/device/$id_crd");

        if (!empty($stocks)) {
            $stock = array_shift($stocks);

            $booking = $stock->bookings;

            $myBooking = array_filter($booking, function ($v) {
                return $v->cartId == $this->context->cart->id && $v->quantity > 0;
            });

            $isBooked = $stock->quantity == $stock->bookingQuantity;

            return [
                "myBooking" => (bool) !empty($myBooking),
                "isBooked" => (bool) $isBooked
            ];
        }

        return false;
    }

    private function addBookingMoreTime()
    {
        $id_cart = $this->context->cart->id;
        $cart = new Cart($id_cart);
        $products = $cart->getProducts(false, false, null, false);

        foreach ($products as $product) {
            $id_booking = Booking::getBookingIdByDatas($product['id_product'], $id_cart);
            $book = new Booking($id_booking);
            if ($book->add_time == 1) {
                continue;
            }
            $this->updateBooking($product['id_product'], $id_cart, 1, true);
        }
    }

    private function cleanGhostDevices()
    {
        $apiDevices = ApiController::getInstance()->get("/api/crd/devices");

        $devices = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "dioqaapiconnexion_product`");

        foreach ($devices as $device) {
            $id_crd = $device['id_crd'];
            $deviceFound = array_filter($apiDevices, function ($e) use ($id_crd) {
                return $e->deviceId == $id_crd;
            });

            if (empty($deviceFound)) {
                $this->setLogTest(
                    'cleanGhostDevices',
                    [$device],
                    __DIR__ . '/logs_error/log_' . date('y-m-d-H') . 'h.log'
                );
                $product = new \Product($device['id_product']);
                $product->delete();
            }
        }
    }

    private function orderCategory($data)
    {
        $datas = $this->setTimeCrd($data->productId, $data->gradeId, $data->time);
        $this->handleCatOrder($datas);
    }

    private function orderAllCategories()
    {
        $datas = Db::getInstance()->executeS("SELECT * FROM `ps_dioqaapiconnexion_time`");
        foreach ($datas as $data) {
            $this->handleCatOrder($data);
        }
    }

    private function setTimeCrd($productId, $gradeId, $time)
    {
        $productCrd = ApiController::getInstance()->get("/api/crd/product/$productId/detail");

        if (!$productCrd) {
            return;
        }

        $model = ApiController::getInstance()->get("/api/crd/model/$productCrd->modelId/detail");

        $data = [
            "id_crd_product" => $productId,
            "id_grade" => $gradeId,
            "time" => $time,
            "id_productType" => $productCrd->productTypeId,
            "id_brand" => $productCrd->brandId,
            "id_model" => $productCrd->modelId,
            "id_group" => $model->groupId
        ];

        $exist = Db::getInstance()->getValue("SELECT `id_time` FROM `ps_dioqaapiconnexion_time` WHERE `id_crd_product` = $productId AND `id_grade` = $gradeId");

        if ($exist) {
            Db::getInstance()->update('dioqaapiconnexion_time', $data, "id_time = $exist");
        } else {
            Db::getInstance()->insert('dioqaapiconnexion_time', $data);
        }

        return $data;
    }

    private function handleCatOrder($data)
    {
        /* $this->updateCatPositionByType($data['id_productType'], 'productType'); */
        $this->updateCatPositionByType($data['id_brand'], 'brand');
        $this->updateCatPositionByType($data['id_group'], 'group');
        $this->updateCatPositionByType($data['id_model'], 'model');
    }

    function updateCatPositionByType($id_crd, $type)
    {
        $catIds = $this->getCatIdByCrdId($id_crd, $type);
        $catSeoIds = $this->getCatIdByCrdId($id_crd, $type . 'Seo');

        $catIds = array_merge($catIds, $catSeoIds);

        $times = $this->getTimeBytype($type);

        if (empty($catIds) || empty($times)) {
            return;
        }

        $position = null;

        foreach ($times as $key => $value) {
            if ($value['id_crd2'] == $id_crd) {
                $position = $key;
                break;
            }
        }

        if ($type == 'model') {
            $this->orderProductPositionByModelId($id_crd, $catIds);
        }

        if ($position === null) {
            return;
        }

        foreach ($catIds as $key => $catId) {
            var_dump([$catId['id_category'], $position]);
            $cat = new Category($catId['id_category']);
            $cat->addPosition($position);
            Category::cleanPositions($cat->id_parent);
        }
    }

    private function orderProductPositionByModelId($id_crd, $catIds)
    {
        $times = $this->getTimeByModelId($id_crd);
        $ordered = [];
        $products = $this->getDevicesByModelId($id_crd);

        foreach ($times as $time) {
            foreach ($products as $product) {
                if ($product['gradeId'] == $time['id_grade']) {
                    $ordered[] = $product['id_product'];
                }
            }
        }

        foreach ($catIds as $catId) {
            foreach ($ordered as $key => $prId) {
                $this->updateProductPosition($prId, $catId['id_category'], $key + 1);
            }
            \Product::cleanPositions($catId['id_category']);
            var_dump($catId['id_category']);
        }
    }

    private function getTimeByModelId($id_crd)
    {
        $sql = "SELECT * FROM ps_dioqaapiconnexion_time where id_model = $id_crd order by time";
        return Db::getInstance()->executeS($sql);
    }

    private function getCatIdByCrdId($id_crd, $type)
    {
        $typeString = "'%" . $type . '-' . $id_crd . "'";

        return Db::getInstance()->executeS("SELECT `id_category` FROM `ps_dioqaapiconnexion_category` WHERE `type` like $typeString");
    }

    private function getTimeBytype($type)
    {
        $sql = "SELECT AVG(time) as avg, id_$type as id_crd2, t.*
        FROM ps_dioqaapiconnexion_time t
        GROUP BY id_crd2
        ORDER BY avg;";
        return Db::getInstance()->executeS($sql);
    }

    private function getDevicesByModelId($id_model)
    {
        $sql = "SELECT * FROM `ps_dioqaapiconnexion_product` WHERE `modelId` = $id_model";
        return Db::getInstance()->executeS($sql);
    }

    private function updateProductPosition($id_product, $id_category, $position)
    {
        //move all product in category above position
        Db::getInstance()->execute("UPDATE `ps_category_product` SET `position` = `position` + 1 WHERE `id_category` = $id_category AND `position` >= $position");

        //move product to position
        Db::getInstance()->update('category_product', ['position' => $position], "id_category = $id_category AND id_product = $id_product");
    }

    private function setCategoriesImage($data)
    {
        $pr = new ProductCrd();
        $catsForImages = $pr->getCatsForImages($data->modelId);
        $pr->addCategoriesImages($catsForImages, $data->colorId, $data->image);

        /* foreach ($catsForImages as $key => $category) {
            $cat = new Category($category['id_category']);
            $cat->active = 1;
            $cat->update();
        } */
    }

    private function emptyModel()
    {
        $products = ApiController::getInstance()->get('/api/crd/products');
        $productGroup = [];
        $modelIds = [];
        $productNoImage = [];
        foreach ($products as $key => $product) {
            /* if ($product->image) {
            $productGroup[$product->modelId] = $product;
            } */

            /* if (!$product->image) {
                $productNoImage[] = [$product->productId, $product->modelName];
            } */

            if ($product->modelId == 870) {
                var_dump($product);
            }
        }

        return;
        foreach ($productGroup as $key => $value) {
            $modelIds[] = $key;
        }

        $models = ApiController::getInstance()->get('/api/crd/essentials/model');

        $emptyModels = [];
        foreach ($models as $key => $model) {
            if (!in_array($model->modelId, $modelIds)) {
                $emptyModels[] = $model->modelId;
            }
        }

        /* var_dump(json_encode($emptyModels, JSON_PRETTY_PRINT)); */

        foreach ($emptyModels as $key => $modelId) {
            $catId = CategoryCrd::getTableLinkLikeStatic($modelId, "%model-$modelId");

            if (empty($catId)) {
                continue;
            }

            $cat = new Category($catId[0]['id_category']);
            $cat->active = 0;
            try {
                $cat->update();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        /* $productGroup2 = []; */

        /* foreach ($products as $key => $product) {
                if (!in_array($product->modelId, $modelIds)) {
                    if ($product->image) {
                        $productGroup2[$product->modelId] = $product;
                    }
                }
            }

            foreach ($productGroup2 as $modelId => $product) {
                $pr = new ProductCrd();
                $catsForImages = $pr->getCatsForImages($modelId);
                $pr->addCategoriesImages($catsForImages, 1, $product->image);
            } */
    }

    public function cleanLogsAllType()
    {
        $types = ['base', 'cron', 'error', 'order', 'test', 'stock', 'webhook'];

        foreach ($types as $key => $value) {
            $folderPath = _PS_MODULE_DIR_ . $this->name . "/logs_$value";
            $this->cleanLogs($folderPath);
        }
    }

    private function cleanLogs($folderPath)
    {
        $listFiles = scandir($folderPath, SCANDIR_SORT_ASCENDING);
        $maxDuration = 60 * 60 * 24 * 7;

        foreach ($listFiles as $file) {
            if (strpos($file, '.log') === false) continue;

            $filemtime = time() - filemtime($folderPath . '/' . $file);
            if ($filemtime < $maxDuration) {
                break;
            }

            unlink($folderPath . '/' . $file);
        }
    }
}
