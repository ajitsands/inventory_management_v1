<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../Models/Item.php';

try {
    $items = Item::getAll();
    echo "<h3>Items returned by Item::getAll() (" . count($items) . " total):</h3>";
    echo "<pre>";
    print_r($items);
    echo "</pre>";
} catch (\Exception $e) {
    echo "<h3 style='color:red;'>Error running Item::getAll():</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
