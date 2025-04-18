<?php

/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the target product functionality.
 */
class TargetsProduct extends ObjectModel
{
    public $id_target_product;
    public $id_target_website;
    public $name;
    public $img_url;
    public $price;
    public $description;
    /**
     * Definition of the database table and fields for the target products model.
     */
    public static $definition = array(
        'table' => 'targets_products',
        'primary' => 'id_target_product',
        'fields' => array(
            'id_target_website' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'img_url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
            'price' => array('type' => self::TYPE_STRING),
            'description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
        )
    );

}