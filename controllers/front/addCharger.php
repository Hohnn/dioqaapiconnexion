<?php

class DioqaapiconnexionAddChargerModuleFrontController extends ModuleFrontController
{

    public $ajax;

    public function postProcess()
    {
        $this->ajax = 1;

        $id_product = $_POST['id_product'];

        $id_cart = $this->context->cart->id;

        try {
            if (isset($_POST['addCharger'])) {
                $this->addCharger($id_cart, $id_product);
            }

            if (isset($_POST['removeCharger'])) {
                $this->removeCharger($id_cart, $id_product);
            }
        } catch (\Throwable $th) {
            $this->module->setLogTest(
                'DioqaapiconnexionAddChargerModuleFrontController : ' . $th->__toString(),
                null,
                __DIR__ . '/../../logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }

        Tools::redirect($this->context->link->getPageLink('cart') . "?action=show");
    }

    private function addCharger($id_cart, $id_product)
    {
        $msg = pSQL("Avec chargeur");
        $price = 5;

        if ($this->updateCustomizationValue($id_cart, $id_product, $msg, $price)) {
            return;
        }

        $id_address_delivery = $this->context->cart->id_address_delivery;

        $sql = "INSERT INTO ps_customization (id_cart, id_product, id_product_attribute, quantity, in_cart, id_address_delivery) 
                VALUES (" . $id_cart . ", " . $id_product . ", " . 0 . ", " . 1 . ", 1, $id_address_delivery)";
        Db::getInstance()->execute($sql);
        $id_custom = (int) Db::getInstance()->Insert_ID();

        $sql = "INSERT INTO `ps_customized_data` (`id_customization`, `type`, `index`, `value`, `price`) 
                VALUES ($id_custom, '1', '0', \"$msg\", $price)";
        Db::getInstance()->execute($sql);

        $where = "id_cart = " . $id_cart
            . " AND id_product = " . $id_product;
        Db::getInstance()->delete('cart_product', $where);

        return $this->context->cart->updateQty(1, $id_product, 0, $id_custom);
    }

    private function removeCharger($id_cart, $id_product)
    {
        $msg = pSQL("Sans chargeur");
        $price = 0;
        $this->updateCustomizationValue($id_cart, $id_product, $msg, $price);
    }

    private function updateCustomizationValue($id_cart, $id_product, $value, $price)
    {
        $data = [
            'value' => pSQL($value),
            'price' => $price
        ];

        $where = "id_cart = " . $id_cart
            . " AND id_product = $id_product";

        $sql = "SELECT id_customization FROM ps_customization WHERE id_cart = $id_cart AND id_product = $id_product";
        $id_custom = (int) Db::getInstance()->getValue($sql);

        if (!$id_custom) {
            return false;
        }

        $where = "id_customization = $id_custom";

        return Db::getInstance()->update('customized_data', $data, $where);
    }
}
