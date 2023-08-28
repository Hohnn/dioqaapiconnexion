<?php

$sql = [];
$sql[_DB_PREFIX_ . 'dioqaapiconnexion_task'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_task` (
            `id_task` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `status` varchar(45) NOT NULL,
            `action` varchar(45) NOT NULL,
            `data` longtext NOT NULL,
            `error` longtext,
            PRIMARY KEY (`id_task`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_product'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_product` (
            `id_product` int(10) unsigned NOT NULL,
            `id_crd` int(11) DEFAULT NULL,
            PRIMARY KEY (`id_product`),
            CONSTRAINT `fk_product` FOREIGN KEY (`id_product`) REFERENCES `ps_product` (`id_product`) ON DELETE CASCADE ON UPDATE NO ACTION
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_manufacturer_image'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_manufacturer_image` (
            `id_image` int(10) unsigned NOT NULL,
            `id_manufacturer` int(10) DEFAULT NULL,
            `hash` varchar(250) DEFAULT NULL,
            PRIMARY KEY (`id_image`),
            CONSTRAINT `fk_image` FOREIGN KEY (`id_image`) REFERENCES `ps_image` (`id_image`) ON DELETE CASCADE ON UPDATE NO ACTION
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_manufacturer'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_manufacturer` (
            `id_manufacturer` int(10) unsigned NOT NULL,
            `id_crd` int(11) DEFAULT NULL,
            PRIMARY KEY (`id_manufacturer`),
            CONSTRAINT `fk_manufacturer` FOREIGN KEY (`id_manufacturer`) REFERENCES `ps_manufacturer` (`id_manufacturer`) ON DELETE CASCADE ON UPDATE NO ACTION
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_image'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_image` (
            `id_image` int(10) unsigned NOT NULL,
            `id_product` int(11) DEFAULT NULL,
            `hash` varchar(250) DEFAULT NULL,
            PRIMARY KEY (`id_image`),
            CONSTRAINT `ps_dioqaapiconnexion_image_ibfk_1` FOREIGN KEY (`id_image`) REFERENCES `ps_image` (`id_image`) ON DELETE CASCADE
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_feature_value'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_feature_value` (
            `id_feature_value` int(10) unsigned NOT NULL,
            `id_crd` int(11) DEFAULT NULL,
            `type` varchar(45) DEFAULT NULL,
            PRIMARY KEY (`id_feature_value`),
            CONSTRAINT `fk_feature_value` FOREIGN KEY (`id_feature_value`) REFERENCES `ps_feature_value` (`id_feature_value`) ON DELETE NO ACTION ON UPDATE NO ACTION
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_feature'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_feature` (
            `id_feature` int(10) unsigned NOT NULL,
            `id_crd` varchar(45) DEFAULT NULL,
            PRIMARY KEY (`id_feature`),
            CONSTRAINT `fk_feature` FOREIGN KEY (`id_feature`) REFERENCES `ps_feature` (`id_feature`) ON DELETE CASCADE ON UPDATE NO ACTION
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_category'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_category` (
            `id_category` int(10) unsigned NOT NULL,
            `id_crd` int(11) DEFAULT NULL,
            `type` varchar(45) DEFAULT NULL,
            PRIMARY KEY (`id_category`),
            CONSTRAINT `ps_dioqaapiconnexion_category_ibfk_1` FOREIGN KEY (`id_category`) REFERENCES `ps_category` (`id_category`) ON DELETE CASCADE
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[_DB_PREFIX_ . 'dioqaapiconnexion_booking'] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dioqaapiconnexion_booking` (
            `id_booking` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_product` int(10) NOT NULL,
            `id_crd` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
            `id_cart` int(10) NOT NULL,
            `quantity` int(11) NOT NULL,
            `date` datetime NOT NULL,
            PRIMARY KEY (`id_booking`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

return $sql;
