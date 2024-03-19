<?php

namespace AP\ABTest\IntIdBased\XOptions;

use AP\ABTest\IntIdBased\XOptions\DataModel\Option;
use AP\ABTest\IntIdBased\XOptions\DataModel\OptionsCollection;
use AP\ABTest\IntIdBased\XOptions\Exception\NotFound;

class OptionSelector
{
    static public function checkInt(OptionsCollection $options, int $item, int $offset): Option
    {
        $total = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                $total += $group->weight;
            }
        }

        $rand = abs(ceil($item / pow(2, $offset))) % $total;

        $total = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                if ($rand < $total + $group->weight) {
                    return $group;
                }
                $total += $group->weight;
            }
        }

        throw new NotFound();
    }

    static public function sqlCases(
        OptionsCollection $options,
        callable          $sqlEscapeString,
        string            $itemField = 'id',
        int               $offset = 0,
        string            $SQL_CEIL = 'CEIL',
        string            $SQL_MOD = 'MOD',
        string            $SQL_ABS = 'ABS',
        string            $SQL_POW = 'POW',
    ): string
    {

        $total = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                $total += $group->weight;
            }
        }
        $cases = [];
        $min   = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                $label = $sqlEscapeString($group->getLabel());

                if ($group->weight == 1) {
                    $where = "= $min";
                } else {
                    $max   = $min + $group->weight - 1;
                    $where = "between $min and $max";
                }

                $cases[] =
                    "when $SQL_MOD($SQL_ABS($SQL_CEIL($itemField/$SQL_POW(2, $offset))),$total) $where then $label";
                $min     += $group->weight;
            }
        }
        return "case " . implode(" ", $cases) . " end";
    }
}
