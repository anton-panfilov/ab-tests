<?php

include "../vendor/autoload.php";

use AP\ABTest\IntIdBased\XOptions\DataModel\Option;
use AP\ABTest\IntIdBased\XOptions\DataModel\OptionsCollection;
use AP\ABTest\IntIdBased\XOptions\OptionSelector;


$options1 = new OptionsCollection([
    new Option(element: "first", weight: 4),
    //new Group(element: "second", weight: 2),
    new Option(element: "second", weight: 1),
]);

$options2 = new OptionsCollection([
    new Option(element: "n1", weight: 1),
    new Option(element: "n2", weight: 3),
    new Option(element: "n3", weight: 1),
    //new Group(element: "n4", weight: 2),
    //new Group(element: "n5", weight: 2),
]);

$res  = [];
$res1 = [];
$res2 = [];

foreach ($options1->all() as $g1) {
    $res1[$g1->getLabel()] = 0;
    foreach ($options2->all() as $g2) {
        $key       = "{$g1->getLabel()}_{$g2->getLabel()}";
        $res[$key] = 0;
    }
}

foreach ($options2->all() as $g2) {
    $res2[$g2->getLabel()] = 0;
}

for ($i = -10000; $i < 10000; $i++) {
    $r1  = OptionSelector::getOption(options: $options1, item: $i, offset: 0)->getLabel();
    $r2  = OptionSelector::getOption(options: $options2, item: $i, offset: 1)->getLabel();
    $key = "{$r1}_$r2";
    if (!isset($res[$key])) {
        $res[$key] = 0;
    }
    $res[$key]++;
    $res1[$r1]++;
    $res2[$r2]++;

    //echo "$i $r2\n";
}

//asort($res);
//$res = array_reverse($res);

var_export($res);
var_export($res1);
var_export($res2);