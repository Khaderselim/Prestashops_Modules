<?php

/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the competitor functionality.
 */
class Competitor extends ObjectModel
{
    /** @var int $id_target_competitor The unique identifier for the competitor
     * @var string $name The name of the competitor
     * @var string $logo The logo of the competitor
     * @var string $url The URL of the competitor
     * @var bool $active Indicates if the competitor is active
     */
    public $id_target_competitor;
    public $name;
    public $logo;
    public $url;
    public $active;
    /**
     * Definition of the database table and fields for the Competitors model.
     */
    public static $definition = array(
        'table' => 'target_competitor',
        'primary' => 'id_target_competitor',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'logo' => array('type' => self::TYPE_STRING),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool')
        )
    );

    /**
     * Retrieves all competitors from the database.
     *
     * @param bool $activeOnly If true, only active competitors are retrieved.
     * @return array An array of competitors.
     */
    public static function getAllCompetitors($activeOnly = true){
        $sql = 'SELECT id_target_competitor, name,  logo
                FROM `'._DB_PREFIX_.'target_competitor`';

        if ($activeOnly) {
            $sql .= ' WHERE active = 1';
        }

        $sql .= ' ORDER BY name ASC';

        $result = Db::getInstance()->executeS($sql);

        $competitors = array();
        return array_merge($competitors, $result ?: array());
    }
}