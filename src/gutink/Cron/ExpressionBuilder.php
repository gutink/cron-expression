<?php
/**
 * Author: Haibin
 */

namespace gutink\Cron;

/**
 * Class ExpressionBuilder
 * crontab表达式生成器
 * 每个字段默认是*
 * @package gutink\Cron
 */
class ExpressionBuilder
{
    const SECOND = 0;
    const MINUTE = 1;
    const HOUR = 2;
    const DAY_OF_MONTH = 3;
    const MONTH = 4;
    const DAY_OF_WEEK = 5;
    const YEAR = 6;

    private $currentField = 0;
    private $fields = array(
        array('every' => true),
        array('every' => true),
        array('every' => true),
        array('every' => true),
        array('every' => true),
        array('every' => true),
        array('every' => true),
    );

    /**
     * 开始 second 字段
     * @return $this
     */
    public function beginSecondField()
    {
        $this->currentField = self::SECOND;
        return $this;
    }

    /**
     * 开始 minute 字段
     * @return $this
     */
    public function beginMinuteField()
    {
        $this->currentField = self::MINUTE;
        return $this;
    }

    /**
     * 开始 hour 字段
     * @return $this
     */
    public function beginHourField()
    {
        $this->currentField = self::HOUR;
        return $this;
    }

    /**
     * 开始 day of month 字段
     * @return $this
     */
    public function beginDayOfMonthField()
    {
        $this->currentField = self::DAY_OF_MONTH;
        return $this;
    }

    /**
     * 开始 month 字段
     * @return $this
     */
    public function beginMonthField()
    {
        $this->currentField = self::MONTH;
        return $this;
    }

    /**
     * 开始 day of week 字段
     * @return $this
     */
    public function beginDayOfWeekField()
    {
        $this->currentField = self::DAY_OF_WEEK;
        return $this;
    }

    /**
     * 开始 year 字段
     * @return $this
     */
    public function beginYearField()
    {
        $this->currentField = self::YEAR;
        return $this;
    }

    /**
     * 每一个当前字段的周期
     * @return $this
     */
    public function every()
    {
        $this->unsetPre($this->currentField);
        $this->fields[$this->currentField]['every'] = true;
        return $this;
    }

    /**
     * 范围
     * @param int|string $from 范围开始
     * @param int|string $to 范围结束
     * @return $this
     */
    public function range($from, $to)
    {
        $this->unsetPre($this->currentField);
        $this->fields[$this->currentField]['range'] = $from . '-' . $to;
        $this->toggleDayField();
        return $this;
    }

    /**
     * 增量
     * @param $step 增量大小
     * @return $this
     */
    public function step($step)
    {
        $this->fields[$this->currentField]['step'] = $step;
        $this->toggleDayField();
        return $this;
    }

    /**
     * 列表中的值
     * @param $array 列表
     * @return $this
     */
    public function some($array)
    {
        $this->unsetPre($this->currentField);
        $this->fields[$this->currentField]['some'] = $array;
        $this->toggleDayField();
        return $this;
    }

    /**
     * 具体的值
     * @param int|string $num 具体的值
     * @return $this
     */
    public function at($num)
    {
        $this->unsetPre($this->currentField);
        $this->fields[$this->currentField]['at'] = $num;
        $this->toggleDayField();
        return $this;
    }

    /**
     * 倒数
     * @param null|int|string $num
     */
    public function last($num = null)
    {
        if($this->currentField === self::DAY_OF_WEEK || $this->currentField === self::DAY_OF_MONTH) {
            $this->fields[$this->currentField] = array();
            $this->fields[$this->currentField]['last'] = $num;
        }
    }

    /**
     * 不支持
     * @param $num
     */
    public function workday($num)
    {

    }

    /**
     * 不支持
     * @param $num
     */
    public function calendar($num) {

    }

    /**
     * 只用于 day of week 字段，表示当月第几周的 周$num，0表示周日
     * @param $num
     */
    public function week($num) {
        if($this->currentField === self::DAY_OF_WEEK) {
            $this->fields[self::DAY_OF_WEEK]['week'] = $num;
        }
    }

    /**
     * 直接设置字段的字符串
     * @param string $field
     * @return $this
     */
    public function set($field = "*")
    {
        $this->fields[$this->currentField] = array();
        $this->fields[$this->currentField]['value'] = $field;
        if(strcmp($field, '*') !== 0) {
            $this->toggleDayField();
        }
        return $this;
    }

    /**
     * 如果设置day of month字段，day of week字段需要设置为？，反之亦然
     */
    private function toggleDayField() {
        if($this->currentField === self::DAY_OF_MONTH) {
            $this->fields[self::DAY_OF_WEEK]['any'] = true;
        } else if($this->currentField === self::DAY_OF_WEEK) {
            $this->fields[self::DAY_OF_MONTH]['any'] = true;
        }
    }


    /**
     * 重置
     * @param int $fieldIndex 索引
     */
    private function unsetPre($fieldIndex)
    {
        unset($this->fields[$fieldIndex]['every']);
        unset($this->fields[$fieldIndex]['range']);
        unset($this->fields[$fieldIndex]['some']);
        unset($this->fields[$fieldIndex]['at']);
    }

    /**
     * 根据条件生成crontab表达式字符串
     * @return string
     */
    public function build()
    {
        // 每个部分的字符串表示
        $filedValues = array();

        foreach ($this->fields as $index => $field) {
            // 如果设置了value，则忽略其他部分
            if (isset($field['value'])) {
                $filedValues[$index] = $field['value'];
            } else if (isset($field['any']) && $field['any'] === true) {
                $filedValues[$index] = '?';
            } else if(isset($field['last'])) {
                $last = $field['last'];
                if(!is_null($last)) {
                    $filedValues[$index] = $last;
                }
                $filedValues[$index] .= 'L';
            } else {
                if (isset($field['at'])) {
                    $filedValues[$index] = $field['at'];
                } else if (isset($field['some']) && is_array($field['some'])) {
                    $filedValues[$index] = join(',', $field['some']);
                } else if (isset($field['range'])) {
                    $filedValues[$index] = $field['range'];
                } else {
                    $filedValues[$index] = '*';
                }

                if (isset($field['step'])) {
                    $filedValues[$index] .= '/' . $field['step'];
                }
            }
            if(isset($field['week'])) {
                $filedValues[$index] .= '#' . $field['week'];
            }
        }

        return join(" ", $filedValues);
    }

}
