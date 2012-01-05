<?php

//require_once '../../main/classes/content/Item.php';

require_once '../../main/classes/charts/Line.php';

echo '<pre>';

$line = new Line();
$line->addPoint(-1, 1);
$line->addPoint(0, 2);
$line->addPoint(1, 3);
$line->addPoint(2, 4);
$line->addPoint(3, 5);
$line->addPoint(4, 6);
$line->addPoint(5, 7);
$line->addPoint(6, 8);
$line->addPoint(7, 9);
$line->addPoint(8, 10);
$line->addPoint(9, 12);
$line->addPoint(10, 14);
$line->addPoint(11, 16);
$line->addPoint(12, 18);
$line->addPoint(13, 20);


print_r($line->getAbscisses());

print_r($line->getPoints());

$line->minimize();

print_r($line->getPoints());

echo '</pre>';

?>
