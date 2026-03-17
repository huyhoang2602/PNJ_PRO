<?php
require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

$db = new \Opencart\System\Library\DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

$category_id = 59; // Based on screenshot path=59

echo "--- Products in Category $category_id ---\n";
$query = $db->query("SELECT p.product_id, pd.name, p.manufacturer_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = '" . (int)$category_id . "' AND pd.language_id = '1'");
foreach ($query->rows as $row) {
    echo "ID: " . $row['product_id'] . " | Name: " . $row['name'] . " | Manufacturer ID: " . $row['manufacturer_id'] . "\n";
}

echo "\n--- Manufacturers in Category $category_id (via query) ---\n";
$manufacturers = $db->query("SELECT DISTINCT m.manufacturer_id, m.name FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "product p ON (m.manufacturer_id = p.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = '" . (int)$category_id . "' AND p.status = '1' ORDER BY m.name ASC");
foreach ($manufacturers->rows as $row) {
    echo "ID: " . $row['manufacturer_id'] . " | Name: " . $row['name'] . "\n";
}

echo "\n--- All Manufacturers ---\n";
$all = $db->query("SELECT manufacturer_id, name FROM " . DB_PREFIX . "manufacturer");
foreach ($all->rows as $row) {
    echo "ID: " . $row['manufacturer_id'] . " | Name: " . $row['name'] . "\n";
}

echo "\n--- Attribute Groups ---\n";
$groups = $db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "attribute_group ag LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE agd.language_id = '1'");
foreach ($groups->rows as $row) {
    echo "ID: " . $row['attribute_group_id'] . " | Name: " . $row['name'] . "\n";
}
unlink(__FILE__);
