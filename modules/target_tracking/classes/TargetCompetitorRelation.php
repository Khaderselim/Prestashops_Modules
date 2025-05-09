<?php

/**
 * This file is part of the Target Tracking module for PrestaShop.
 * It manages the relationship between target websites and competitor websites.
 */
class TargetCompetitorRelation extends ObjectModel
{
    /** @var int $id_target_competitor The unique identifier for the target competitor relation
     * @var int $id_target The ID of the target website
     * @var int $id_competitor The ID of the competitor website
     * @var int $priority The priority of the competitor for the target
     */
    public $id_target_competitor;
    public $id_target;
    public $id_competitor;
    public $priority;
    /**
     * Definition of the database table and fields for the target competitor relation model.
     */
    public static $definition = array(
        'table' => 'target_competitor_relation',
        'primary' => 'id_target_competitor',
        'fields' => array(
            'id_target' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'id_competitor' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'priority' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
        )
    );

    /**
     * Retrieves the target competitor relations for a given target ID.
     *
     * @param int $id_target The ID of the target website.
     * @return array An array of target competitor relations.
     */
    public static function getCompetitorsByTarget($id_target)
    {
        $sql = 'SELECT tc.*, c.name , c.logo , c.url , c.active
                FROM ' . _DB_PREFIX_ . 'target_competitor_relation tc
                LEFT JOIN ' . _DB_PREFIX_ . 'target_websites t ON tc.id_target = t.id_target_website
                LEFT JOIN ' . _DB_PREFIX_ . 'target_competitor c ON tc.id_competitor = c.id_target_competitor
                WHERE tc.id_target = ' . (int)$id_target . ' AND c.active = 1
                ORDER BY c.name ASC';

        return Db::getInstance()->executeS($sql);
    }

}