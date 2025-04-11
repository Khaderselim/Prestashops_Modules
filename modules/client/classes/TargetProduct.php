<?php

/**
 * This file is part of the Client module for PrestaShop.
 * It manages the target product functionality.
 */
class TargetProduct extends ObjectModel
{
    /** @var int $id_product The unique identifier for the target product
     * @var int $id_target The ID of the target associated with this product
     * @var string $name The name of the target product
     * @var string $price The price of the target product
     * @var string $url The URL of the target product
     * @var string $date_add The date the target product was added
     * @var string $date_upd The date the target product was last updated
     * */
    public $id_product;
    public $id_target;
    public $name;
    public $price;
    public $url;
    public $date_add;
    public $date_upd;

    /**
     * Definition of the database table and fields for the target products model.
     */
    public static $definition = array(
        'table' => 'target_product',
        'primary' => 'id_product',
        'fields' => array(
            'id_target' => array('type' => self::TYPE_INT, 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'required' => true),
            'price' => array('type' => self::TYPE_STRING, 'required' => true),
            'url' => array('type' => self::TYPE_STRING, 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        )
    );

    /**
     * Retrieves products by target ID.
     * @param int $id_target The ID of the target.
     * @return array An array of target products.
     */
    public static function getProductsByTarget($id_target)
    {
        return Db::getInstance()->executeS('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'target_product`
            WHERE id_target = ' . (int)$id_target . '
            ORDER BY date_upd DESC
        ');
    }
}
