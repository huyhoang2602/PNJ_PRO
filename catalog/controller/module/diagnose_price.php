<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DiagnosePrice extends \Opencart\System\Engine\Controller {
    public function index() {
        $cg_id = (int)$this->config->get('config_customer_group_id');
        $price_expr = "COALESCE((SELECT (CASE WHEN ps.type = 'P' THEN (p.price - (p.price * (ps.price / 100))) WHEN ps.type = 'S' THEN (p.price - ps.price) ELSE ps.price END) FROM `" . DB_PREFIX . "product_discount` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '{$cg_id}' AND ps.quantity = '1' AND ps.special = '1' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), COALESCE((SELECT (CASE WHEN pd.type = 'P' THEN (p.price - (p.price * (pd.price / 100))) WHEN pd.type = 'S' THEN (p.price - pd.price) ELSE pd.price END) FROM `" . DB_PREFIX . "product_discount` pd WHERE pd.product_id = p.product_id AND pd.customer_group_id = '{$cg_id}' AND pd.quantity = '1' AND pd.special = '0' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW())) ORDER BY pd.priority ASC, pd.price ASC LIMIT 1), p.price))";

        $sql = "SELECT p.product_id, pd.name, p.price as base_price, {$price_expr} as effective_price 
                FROM " . DB_PREFIX . "product p 
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                WHERE p.manufacturer_id = 11 AND pd.language_id = 1";
        
        $query = $this->db->query($sql);
        
        echo "<pre>";
        print_r($query->rows);
        echo "</pre>";
        exit;
    }
}
