<?php
/**
 * 2007-2025 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

// Independent tables first
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_catalog` (
    `id_target_catalog` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `value` BIGINT,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_target_catalog`),
    UNIQUE KEY `name` (`name`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_competitor` (
    `id_target_competitor` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `logo` varchar(255) NOT NULL,
    `url` varchar(255) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_target_competitor`),
    UNIQUE KEY `name` (`name`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Tables depending on target_catalog
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_websites` (
    `id_target_website` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `url` varchar(255) NOT NULL UNIQUE,
    `logo` varchar(255) NOT NULL,
    `target_catalog_value` int(255) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_target_website`),
    UNIQUE KEY `name` (`name`, `target_catalog_value`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Tables depending on target_competitor
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_competitor_price_description` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_competitor` int(11) NOT NULL,
    `price_tag` varchar(50) NOT NULL,
    `price_attribute` text NOT NULL,
    `description_tag` varchar(50) NOT NULL,
    `description_attribute` text,
    `stock_tag` varchar(50),
    `stock_attribute` text,
    PRIMARY KEY (`id`),
    KEY `id_competitor` (`id_competitor`),
    FOREIGN KEY (`id_competitor`) REFERENCES `' . _DB_PREFIX_ . 'target_competitor` (`id_target_competitor`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_competitor_product` (
    `id_product` int(11) NOT NULL AUTO_INCREMENT,
    `id_competitor` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `url` varchar(255) NOT NULL UNIQUE,
    `price` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `stock` varchar(255) NOT NULL,
    `date_add` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_product`),
    FOREIGN KEY (`id_competitor`) REFERENCES `' . _DB_PREFIX_ . 'target_competitor` (`id_target_competitor`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'targets_products` (
    `id_target_product` INT(11) NOT NULL AUTO_INCREMENT,
    `id_target_website` INT(11) NOT NULL,
    `id_product` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `img_url` varchar(255) NOT NULL,
    `price` varchar(255) NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (`id_target_product`),
    FOREIGN KEY (`id_target_website`) REFERENCES `' . _DB_PREFIX_ . 'target_websites` (`id_target_website`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
// Tables depending on target_websites and target_competitor
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_competitor_relation` (
    `id_target_competitor_relation` INT(11) NOT NULL AUTO_INCREMENT,
    `id_target` INT(11) NOT NULL,
    `id_competitor` INT(11) NOT NULL,
    `priority` int(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_target_competitor_relation`),
    FOREIGN KEY (`id_target`) REFERENCES `' . _DB_PREFIX_ . 'target_websites` (`id_target_website`) ON DELETE CASCADE,
    FOREIGN KEY (`id_competitor`) REFERENCES `' . _DB_PREFIX_ . 'target_competitor` (`id_target_competitor`) ON DELETE CASCADE,
    UNIQUE KEY `id_target` (`id_target`, `id_competitor`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_products_relation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_product_competitor` int(11) NOT NULL,
    `id_product_target` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_product_competitor`) REFERENCES `' . _DB_PREFIX_ . 'target_competitor_product` (`id_product`) ON DELETE CASCADE,
    FOREIGN KEY (`id_product_target`) REFERENCES `' . _DB_PREFIX_ . 'targets_products` (`id_target_product`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'target_competitor_product_history` (
    `id_history` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` int(11) NOT NULL,
    `old_price` varchar(255) NOT NULL,
    `new_price` varchar(255) NOT NULL,
    `date_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_history`),
    KEY `id_product` (`id_product`),
    FOREIGN KEY (`id_product`) REFERENCES `' . _DB_PREFIX_ . 'target_competitor_product` (`id_product`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TRIGGER target_competitor_product_price_history
BEFORE UPDATE ON `' . _DB_PREFIX_ . 'target_competitor_product`
FOR EACH ROW
BEGIN
    IF NEW.price != OLD.price THEN
        INSERT INTO `' . _DB_PREFIX_ . 'target_competitor_product_history` (id_product, old_price, new_price)
        VALUES (OLD.id_product, OLD.price, NEW.price);

        DELETE FROM `' . _DB_PREFIX_ . 'target_competitor_product_history`
        WHERE id_product = OLD.id_product
        AND id_history NOT IN (
            SELECT id_history FROM (
                SELECT id_history
                FROM `' . _DB_PREFIX_ . 'target_competitor_product_history`
                WHERE id_product = OLD.id_product
                ORDER BY date_update DESC
                LIMIT 3
            ) tmp
        );
    END IF;
END;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}