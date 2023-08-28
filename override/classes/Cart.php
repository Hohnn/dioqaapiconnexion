<?php

class Cart extends CartCore
{

    public function updateQty(
        $quantity,
        $id_product,
        $id_product_attribute = null,
        $id_customization = false,
        $operator = 'up',
        $id_address_delivery = 0,
        Shop $shop = null,
        $auto_add_cart_rule = true,
        $skipAvailabilityCheckOutOfStock = false,
        bool $preserveGiftRemoval = true,
        bool $useOrderPrices = false
    ) {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }

        if (Validate::isLoadedObject(Context::getContext()->customer)) {
            if ($id_address_delivery == 0 && (int) $this->id_address_delivery) {
                // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = $this->id_address_delivery;
            } elseif ($id_address_delivery == 0) {
                // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int) Address::getFirstCustomerAddressId(
                    (int) Context::getContext()->customer->id
                );
            } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) {
                // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
            }
        } else {
            $id_address_delivery = 0;
        }

        $quantity = (int) $quantity;
        $id_product = (int) $id_product;
        $id_product_attribute = (int) $id_product_attribute;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);

        if ($id_product_attribute) {
            $combination = new Combination((int) $id_product_attribute);
            if ($combination->id_product != $id_product) {
                return false;
            }
        }

        /* If we have a product combination, the minimal quantity is set with the one of this combination */
        if (!empty($id_product_attribute)) {
            $minimal_quantity = (int) Attribute::getAttributeMinimalQty($id_product_attribute);
        } else {
            $minimal_quantity = (int) $product->minimal_quantity;
        }

        if (!Validate::isLoadedObject($product)) {
            die(Tools::displayError());
        }

        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }

        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }

        $data = [
            'cart' => $this,
            'product' => $product,
            'id_product_attribute' => $id_product_attribute,
            'id_customization' => $id_customization,
            'quantity' => $quantity,
            'operator' => $operator,
            'id_address_delivery' => $id_address_delivery,
            'shop' => $shop,
            'auto_add_cart_rule' => $auto_add_cart_rule,
        ];

        /* @deprecated deprecated since 1.6.1.1 */
        // Hook::exec('actionBeforeCartUpdateQty', $data);
        Hook::exec('actionCartUpdateQuantityBefore', $data);

        if ((int) $quantity <= 0) {
            return $this->deleteProduct($id_product, $id_product_attribute, (int) $id_customization, (int) $id_address_delivery, $preserveGiftRemoval, $useOrderPrices);
        }

        if (
            !$product->available_for_order
            || (Configuration::isCatalogMode()
                && !defined('_PS_ADMIN_DIR_')
            )
        ) {
            return false;
        }

        /* Check if the product is already in the cart */
        $cartProductQuantity = $this->getProductQuantity(
            $id_product,
            $id_product_attribute,
            (int) $id_customization,
            (int) $id_address_delivery
        );

        /* Update quantity if product already exist */
        if (!empty($cartProductQuantity['quantity'])) {
            $productQuantity = Product::getQuantity($id_product, $id_product_attribute, null, $this);
            $availableOutOfStock = Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($product->id));

            if ($operator == 'up') {
                $updateQuantity = '+ ' . $quantity;
                $newProductQuantity = $productQuantity - $quantity;

                if ($newProductQuantity < 0 && !$availableOutOfStock && !$skipAvailabilityCheckOutOfStock) {
                    return false;
                }
            } elseif ($operator == 'down') {
                $cartFirstLevelProductQuantity = $this->getProductQuantity(
                    (int) $id_product,
                    (int) $id_product_attribute,
                    $id_customization
                );
                $updateQuantity = '- ' . $quantity;

                if (
                    $cartFirstLevelProductQuantity['quantity'] <= 1
                    || $cartProductQuantity['quantity'] - $quantity <= 0
                ) {
                    return $this->deleteProduct((int) $id_product, (int) $id_product_attribute, (int) $id_customization, (int) $id_address_delivery, $preserveGiftRemoval, $useOrderPrices);
                }
            } else {
                return false;
            }

            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                    SET `quantity` = `quantity` ' . $updateQuantity . '
                    WHERE `id_product` = ' . (int) $id_product .
                    ' AND `id_customization` = ' . (int) $id_customization .
                    (!empty($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') . '
                    AND `id_cart` = ' . (int) $this->id . (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ? ' AND `id_address_delivery` = ' . (int) $id_address_delivery : '') . '
                    LIMIT 1'
            );
        } elseif ($operator == 'up') {
            /* Add product to the cart */

            $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                        FROM ' . _DB_PREFIX_ . 'product p
                        ' . Product::sqlStock('p', $id_product_attribute, true, $shop) . '
                        WHERE p.id_product = ' . $id_product;

            $result2 = Db::getInstance()->getRow($sql);

            // Quantity for product pack
            if (Pack::isPack($id_product)) {
                $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute, null, $this);
            }

            if (isset($result2['out_of_stock']) && !Product::isAvailableWhenOutOfStock((int) $result2['out_of_stock']) && !$skipAvailabilityCheckOutOfStock) {
                if ((int) $quantity > $result2['quantity']) {
                    return false;
                }
            }

            if ((int) $quantity < $minimal_quantity) {
                return -1;
            }

            $result_add = Db::getInstance()->insert('cart_product', [
                'id_product' => (int) $id_product,
                'id_product_attribute' => (int) $id_product_attribute,
                'id_cart' => (int) $this->id,
                'id_address_delivery' => (int) $id_address_delivery,
                'id_shop' => $shop->id,
                'quantity' => (int) $quantity,
                'date_add' => date('Y-m-d H:i:s'),
                'id_customization' => (int) $id_customization,
            ]);

            if (!$result_add) {
                return false;
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        CartRule::autoRemoveFromCart(null, $useOrderPrices);
        if ($auto_add_cart_rule) {
            CartRule::autoAddToCart($context, $useOrderPrices);
        }

        if ($product->customizable) {
            return $this->_updateCustomizationQuantity(
                (int) $quantity,
                (int) $id_customization,
                (int) $id_product,
                (int) $id_product_attribute,
                (int) $id_address_delivery,
                $operator
            );
        }

        Hook::exec('actionCartUpdateQuantityAfter', $data);

        return true;
    }
}
