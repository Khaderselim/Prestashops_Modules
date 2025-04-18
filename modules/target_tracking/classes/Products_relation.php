<?php

/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the relationship between target products and competitor products.
 */
class Products_relation extends ObjectModel
{
    /** @var int $id The unique identifier for the product relation
     * @var int $id_product_competitor The ID of the competitor product
     * @var int $id_product_target The ID of the target product
     */
    public $id;
    public $id_product_competitor;
    public $id_product_target;
    /**
     * Definition of the database table and fields for the products relation model.
     */
    public static $definition = array(
        'table' => 'target_products_relation',
        'primary' => 'id',
        'fields' => array(
            'id_product_competitor' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_product_target' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
        )
    );

    /**
     * Retrieves the product relations for a given target product ID.
     *
     * @param int $id_product_client The ID of the target product.
     * @return array An array of product relations.
     */
    public static function getProductRelations($id_product_client)
    {
        $sql = 'SELECT r.*, cp.name, cp.url, cp.price, co.logo, co.active, cp.id_product, cph.new_price, cph.old_price, cp.stock, c.price as client_price, tcr.priority,cp.date_add 
        FROM ' . _DB_PREFIX_ . 'target_products_relation r
        LEFT JOIN ' . _DB_PREFIX_ . 'target_competitor_product cp ON r.id_product_competitor = cp.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'targets_products c ON r.id_product_target = c.id_target_product
        LEFT JOIN ' . _DB_PREFIX_ . 'target_competitor co ON cp.id_competitor = co.id_target_competitor
        LEFT JOIN ' . _DB_PREFIX_ . 'target_competitor_relation tcr ON cp.id_competitor = tcr.id_competitor AND c.id_target_website = tcr.id_target
        LEFT JOIN (
            SELECT ph.*
            FROM ' . _DB_PREFIX_ . 'target_competitor_product_history ph
            INNER JOIN (
                SELECT id_product, MAX(date_update) as max_date
                FROM ' . _DB_PREFIX_ . 'target_competitor_product_history
                GROUP BY id_product
            ) latest ON ph.id_product = latest.id_product
            AND ph.date_update = latest.max_date
        ) cph ON cp.id_product = cph.id_product
        WHERE r.id_product_target = ' . (int)$id_product_client . '
        ORDER BY tcr.priority DESC';
        return Db::getInstance()->executeS($sql);
    }
}