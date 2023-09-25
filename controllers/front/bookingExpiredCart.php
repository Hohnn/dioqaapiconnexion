<?php

class DioqaapiconnexionBookingExpiredCartModuleFrontController extends ModuleFrontController
{

    public $ajax;

    public function postProcess()
    {
        $this->ajax = 1;

        try {
            $this->handleBooking();
        } catch (\Throwable $th) {
            $this->module->setLogTest(
                'DioqaapiconnexionBookingExpiredCartModuleFrontController : ' . $th->__toString(),
                null,
                __DIR__ . '/../../logs_error/log_' . date('y-m-d-H') . 'h.log'
            );
        }

        Tools::redirect($_SERVER['HTTP_REFERER']);
    }

    private function handleBooking()
    {
        $id_cart = $this->context->cart->id;
        $id_customer = $this->context->customer->id;

        $products = $this->getProductsFromPost();

        foreach ($products as $key => $data) {
            $id_product = $data['id_product'];
            $id_customization = $data['id_customization'];
            if ($data['addBooking']) {
                $this->module->deleteBooking($id_product, $id_cart);

                $isPossible = $this->module->isBookingPossible($id_product);
                if ($isPossible && !$isPossible['isBooked']) {
                    $this->module->addBooking($id_product, $id_cart);
                } else {
                    $this->context->cart->deleteProduct($id_product, 0, $id_customization);
                }
            } else {
                $this->context->cart->deleteProduct($id_product, 0, $id_customization);
                $this->module->deleteBooking($id_product, $id_cart);
            }
        }
    }

    private function getProductsFromPost()
    {
        $products = array_filter(
            $_POST,
            fn ($key) => str_contains($key, 'book_product_'),
            ARRAY_FILTER_USE_KEY
        );

        $array = [];

        foreach ($products as $key => $bool) {
            $ids = $this->getIds($key);
            $data = [
                "id_product" => (int) $ids[0],
                "id_customization" => (int) isset($ids[1]) ? $ids[1] : 0,
                "addBooking" => $bool === "true" ? true : false
            ];
            $array[] = $data;
        }

        return $array;
    }

    private function getIds($string)
    {
        preg_match_all('/\d+/', $string, $matches);

        return $matches[0];
    }
}
