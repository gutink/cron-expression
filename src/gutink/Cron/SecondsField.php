<?php

namespace gutink\Cron;

use DateTime;


/**
 * Seconds field.  Allows: * , / -
 */
class SecondsField extends AbstractField
{
    public function isSatisfiedBy(DateTime $date, $value)
    {
        return $this->isSatisfied($date->format('s'), $value);
    }

    public function increment(DateTime $date, $invert = false, $parts = null)
    {
        if (is_null($parts)) {
            if ($invert) {
                $date->modify('-1 second');
            } else {
                $date->modify('+1 second');
            }
            return $this;
        }

        $parts = strpos($parts, ',') !== false ? explode(',', $parts) : array($parts);
        $seconds = array();
        foreach ($parts as $part) {
            $seconds = array_merge($seconds, $this->getRangeForExpression($part, 59));
        }

        $current_minute = $date->format('s');
        $position = $invert ? count($seconds) - 1 : 0;
        if (count($seconds) > 1) {
            for ($i = 0; $i < count($seconds) - 1; $i++) {
                if ((!$invert && $current_minute >= $seconds[$i] && $current_minute < $seconds[$i + 1]) ||
                    ($invert && $current_minute > $seconds[$i] && $current_minute <= $seconds[$i + 1])) {
                    $position = $invert ? $i : $i + 1;
                    break;
                }
            }
        }

        if ((!$invert && $current_minute >= $seconds[$position]) || ($invert && $current_minute <= $seconds[$position])) {
            $date->modify(($invert ? '-' : '+') . '1 minute');
            $date->setTime($date->format('H'), $date->format('i'), $invert ? 59 : 0);
        }
        else {
            $date->setTime($date->format('H'), $date->format('i'), $seconds[$position]);
        }

        return $this;
    }

    public function validate($value)
    {
        return (bool) preg_match('/^[\*,\/\-0-9]+$/', $value);
    }
}
