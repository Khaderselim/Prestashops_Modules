<?php

/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the target catalog functionality.
 */
class Categories extends ObjectModel
{
    /** @var int $id_target_catalog The unique identifier for the target catalog
     * @var string $name The name of the target catalog
     * @var string $value The value associated with the target catalog
     * */
    public $id_target_catalog;
    public $name;
    public $value;
    public $active;
    /**
     * Definition of the database table and fields for the Target's categories model.
     */
    public static $definition = array(
        'table' => 'target_catalog',
        'primary' => 'id_target_catalog',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'value' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool')
        )
    );
}
