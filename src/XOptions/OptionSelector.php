<?php

namespace AP\ABTest\IntIdBased\XOptions;

use AP\ABTest\IntIdBased\XOptions\DataModel\Option;
use AP\ABTest\IntIdBased\XOptions\DataModel\OptionsCollection;
use AP\ABTest\IntIdBased\XOptions\DataModel\SqlOption;
use AP\ABTest\IntIdBased\XOptions\DataModel\SqlOptionsCollection;
use AP\ABTest\IntIdBased\XOptions\Exception\NotFound;

class OptionSelector
{
    static public function getOption(OptionsCollection $options, int $item, int $offset): Option
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
        $result = "case";
        $cases  = static::sqlCasesCollection(
            options: $options,
            sqlEscapeString: $sqlEscapeString,
            itemField: $itemField,
            offset: $offset,
            SQL_CEIL: $SQL_CEIL,
            SQL_MOD: $SQL_MOD,
            SQL_ABS: $SQL_ABS,
            SQL_POW: $SQL_POW
        );
        foreach ($cases->all() as $case) {
            $result .= " when $case->case_sql then $case->label_sql";

        }
        return "$result end";
    }

    static public function sqlCasesCollection(
        OptionsCollection $options,
        callable          $sqlEscapeString,
        string            $itemField = 'id',
        int               $offset = 0,
        string            $SQL_CEIL = 'CEIL',
        string            $SQL_MOD = 'MOD',
        string            $SQL_ABS = 'ABS',
        string            $SQL_POW = 'POW',
    ): SqlOptionsCollection
    {
        $total = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                $total += $group->weight;
            }
        }
        $cases = new SqlOptionsCollection();
        $min   = 0;
        foreach ($options->all() as $group) {
            if ($group->weight > 0) {
                if ($group->weight == 1) {
                    $where = "= $min";
                } else {
                    $max   = $min + $group->weight - 1;
                    $where = "between $min and $max";
                }
                $cases[] = new SqlOption(
                    label: $group->getLabel(),
                    label_sql: $sqlEscapeString($group->getLabel()),
                    case_sql: "$SQL_MOD($SQL_ABS($SQL_CEIL($itemField/$SQL_POW(2, $offset))),$total) $where"
                );
                $min     += $group->weight;
            }
        }
        return $cases;
    }
}
