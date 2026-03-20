<?php
// Diagnostic script to check payment settings
if (is_file(__DIR__ . '/../../config.php')) {
	require_once(__DIR__ . '/../../config.php');
} else {
	die('config.php not found');
}

$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

$sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE `key` LIKE 'payment_%status' OR `key` LIKE 'payment_cod_%'";
$result = $mysqli->query($sql);

echo "PAYMENT SETTINGS:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['key'] . ": " . $row['value'] . "\n";
}

// Check Geo Zones
$sql = "SELECT * FROM " . DB_PREFIX . "geo_zone";
$result = $mysqli->query($sql);
echo "\nGEO ZONES:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['geo_zone_id'] . " | Name: " . $row['name'] . "\n";
}

$mysqli->close();
?>
