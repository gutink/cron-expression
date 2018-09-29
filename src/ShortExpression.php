<?php
/**
 * Author: Haibin
 */

namespace Gutink\Cron;

/**
 * Class ShortExpression
 * 短表达式，最小单位是天
 * @package Gutink\Cron
 */
class ShortExpression extends CronExpression
{
    protected static $order = array(self::YEAR, self::MONTH, self::DAY, self::WEEKDAY);
    function __construct($expression, FieldFactory $fieldFactory = null)
    {
        $this->leastParts = 3;
        self::$minPosition = self::DAY;
        parent::__construct($expression, $fieldFactory);
    }
}