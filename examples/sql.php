<?php

include "../vendor/autoload.php";

use AP\ABTest\IntIdBased\XOptions\DataModel\Option;
use AP\ABTest\IntIdBased\XOptions\DataModel\OptionsCollection;
use AP\ABTest\IntIdBased\XOptions\OptionSelector;

class VariantA
{
    public function __construct(public int $foo)
    {
    }

    public function __toString(): string
    {
        return "variantA(min: {$this->foo})";
    }
}

class VariantB
{
    public function __construct()
    {
    }

    public function __toString(): string
    {
        return "variantB(no extra settings)";
    }
}

$options1 = new OptionsCollection([
    new Option(element: new VariantA(999), weight: 4),
    //new Group(element: "second", weight: 2),
    new Option(element: new VariantB(), weight: 1),
    new Option(element: new VariantA(500), weight: 2),
]);

$options2 = new OptionsCollection([
    new Option(element: "n1", weight: 1),
    new Option(element: "n2", weight: 1),
    new Option(element: "n3", weight: 3),
    //new Group(element: "n4", weight: 2),
    //new Group(element: "n5", weight: 2),
]);

$sql = OptionSelector::sqlCases(
    options: $options1,
    sqlEscapeString: function ($v) {
        // it just for example, by security reason, please use function what your database driver provided
        $slash = "'";
        return $slash . addcslashes($v, $slash) . $slash;
    },
    itemField: 'body_cellPhone',
    offset: 6
);

function no_safe_print_sql_cases(string $sql): string
{
    $sql = implode("\nwhen ", explode("when ", $sql));
    if (str_ends_with($sql, " end")) {
        $sql = substr($sql, 0, -4) . "\nend";
    }
    return "$sql\n\n";
}

echo no_safe_print_sql_cases($sql);

