<?php
require_once("../../../config/config.inc.php");
require_once("../../../init.php");

use Dioqaapiconnexion\Entity\Booking;
use Dioqaapiconnexion\Controller\ApiController;
use Dioqaapiconnexion\Entity\ProductCrd;

$cartId = null;

if (isset($_POST['action']) && $_POST['action'] == 'checkBooking') {
    $data = [];
    $book = [];
    $context = Context::getContext();

    if (!$context->cart->id) {
        returnDatas(false);
    }

    $id_cart = $context->cart->id;

    $book = Booking::getBookingsByCartId($id_cart);
    $data['bookings'] = $book;
    returnDatas($data);
}

if (isset($_POST['action']) && $_POST['action'] == 'ajaxIsBookingPossible') {
    $data = [];
    $product = new ProductCrd($_POST['id_product']);
    $id_crd = $product->getCRDProductId();
    $stocks = ApiController::getInstance()->get("/api/crd/stocks/device/$id_crd");

    $data['stocks'] = $stocks;
    if (!empty($stocks)) {

        $stock = array_shift($stocks);

        $data['IsBookingPossible'] = (bool) $stock->quantity > $stock->bookingQuantity;

        returnDatas($data);
    }

    returnDatas($data);
}

function returnDatas($data)
{
    echo json_encode($data);
    exit;
}
