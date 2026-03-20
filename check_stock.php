<?php
// Diagnostic script to check stock for "Nhẫn cưới uyên ương" using mysqli
if (is_file(__DIR__ . '/../../config.php')) {
	require_once(__DIR__ . '/../../config.php');
} else {
	die('config.php not found');
}

$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Find product
$sql = "SELECT p.product_id, p.quantity, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%Nhẫn cưới%'";
$result = $mysqli->query($sql);

if ($result->num_rows == 0) {
    die("No product found");
}

while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['product_id'] . " | Name: " . $row['name'] . " | Total Qty: " . $row['quantity'] . "\n";
    
    // Options
    $opt_sql = "SELECT ovd.name as option_value, pov.quantity, pov.subtract 
                FROM " . DB_PREFIX . "product_option_value pov 
                LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) 
                WHERE pov.product_id = " . $row['product_id'];
    $opt_result = $mysqli->query($opt_sql);
    
    if ($opt_result->num_rows > 0) {
        echo "  OPTIONS:\n";
        while ($opt = $opt_result->fetch_assoc()) {
            echo "    Value: " . $opt['option_value'] . " | Qty: " . $opt['quantity'] . " | Subtract: " . $opt['subtract'] . "\n";
        }
    } else {
        echo "  NO OPTIONS\n";
    }
}

$mysqli->close();
?>
