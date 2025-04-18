<?php
/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the target website functionality.
 */
class TargetWebsite extends ObjectModel{
    /**
     * @var int $id_target_website The unique identifier for the target website
     * @var string $name The name of the target website
     * @var string $url The URL of the target website
     * @var string $logo The logo of the target website
     * @var int $target_catalog_value The catalog value for the target website
     * @var bool $active Indicates if the target website is active
     */
    public $id_target_website;
    public $name;
    public $url;
    public $logo;
    public $target_catalog_value;
    public $active;
    /**
     * Definition of the database table and fields for the target websites model.
     */
    public static $definition = array(
        'table' => 'target_websites',
        'primary' => 'id_target_website',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true),
            'logo' => array('type' => self::TYPE_STRING),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'target_catalog_value' => array('type' => self::TYPE_INT)
        )
    );



}