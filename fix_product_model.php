<?php
$file = 'c:/laragon/www/opencart-4.1.0.3/catalog/model/catalog/product.php';
$content = file_get_contents($file);

$search = 'if (!empty($data[\'filter_manufacturer_id\'])) {
			$sql .= " AND `p`.`manufacturer_id` = \'" . (int)$data[\'filter_manufacturer_id\'] . "\'";
		}';

$replace = 'if (!empty($data[\'filter_manufacturer_ids\'])) {
			$implode = [];
			foreach ($data[\'filter_manufacturer_ids\'] as $manufacturer_id) {
				$implode[] = (int)$manufacturer_id;
			}
			if ($implode) {
				$sql .= " AND `p`.`manufacturer_id` IN (" . implode(\',\', $implode) . ")";
			}
		}

		if (!empty($data[\'filter_manufacturer_id\'])) {
			$sql .= " AND `p`.`manufacturer_id` = \'" . (int)$data[\'filter_manufacturer_id\'] . "\'";
		}';

// We apply it twice (for getProducts and getTotalProducts)
$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Successfully updated Product.php\n";
unlink(__FILE__);
