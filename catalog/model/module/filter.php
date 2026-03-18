<?php
namespace Opencart\Catalog\Model\Extension\DcMinimal\Module;

/**
 * Integrated filter model for dc_minimal theme.
 * Handles SQL query generation and filter data retrieval.
 */
class Filter extends \Opencart\System\Engine\Model {

    // Scope queries to category or manufacturer
    private function contextJoin(array $ctx, string $p_alias = 'p'): string {
        if ($ctx['type'] === 'category') {
            return "INNER JOIN `" . DB_PREFIX . "product_to_category` p2c ON ({$p_alias}.product_id = p2c.product_id AND p2c.category_id = '" . (int)$ctx['id'] . "')";
        }
        return '';
    }

    private function contextWhere(array $ctx, string $p_alias = 'p'): string {
        if ($ctx['type'] === 'manufacturer') {
            return "AND {$p_alias}.manufacturer_id = '" . (int)$ctx['id'] . "'";
        }
        return '';
    }

    public function getPriceRange(array $ctx): array {
        $join = $this->contextJoin($ctx);
        $where = $this->contextWhere($ctx);
        $cg_id = (int)$this->config->get('config_customer_group_id');
        
        $price_expr = "COALESCE((SELECT (CASE WHEN ps.type = 'P' THEN (p.price - (p.price * (ps.price / 100))) WHEN ps.type = 'S' THEN (p.price - ps.price) ELSE ps.price END) FROM `" . DB_PREFIX . "product_discount` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '{$cg_id}' AND ps.quantity = '1' AND ps.special = '1' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), COALESCE((SELECT (CASE WHEN pd.type = 'P' THEN (p.price - (p.price * (pd.price / 100))) WHEN pd.type = 'S' THEN (p.price - pd.price) ELSE pd.price END) FROM `" . DB_PREFIX . "product_discount` pd WHERE pd.product_id = p.product_id AND pd.customer_group_id = '{$cg_id}' AND pd.quantity = '1' AND pd.special = '0' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW())) ORDER BY pd.priority ASC, pd.price ASC LIMIT 1), p.price))";

        // Correct OC4 price range query 
        $sql = "SELECT MIN({$price_expr}) AS `min`, MAX({$price_expr}) AS `max` FROM `" . DB_PREFIX . "product` p {$join} WHERE p.status = '1' {$where}";
        
        $query = $this->db->query($sql);
        
        return ['min' => (float)($query->row['min'] ?? 0), 'max' => (float)($query->row['max'] ?? 0)];
    }

    public function getManufacturers(array $ctx, bool $show_count = false): array {
        $count_col = $show_count ? ', COUNT(DISTINCT p.product_id) AS `count`' : '';
        $join = $this->contextJoin($ctx);
        $where = $this->contextWhere($ctx);
        $query = $this->db->query("SELECT DISTINCT m.manufacturer_id AS `id`, m.name {$count_col} FROM `" . DB_PREFIX . "product` p INNER JOIN `" . DB_PREFIX . "manufacturer` m ON (p.manufacturer_id = m.manufacturer_id) {$join} WHERE p.status = '1' AND m.manufacturer_id > 0 {$where} GROUP BY m.manufacturer_id ORDER BY m.name ASC");
        return $query->rows;
    }

    public function getSubCategories(array $ctx, bool $show_count = false): array {
        if ($ctx['type'] !== 'category') return [];
        $count_col = $show_count ? ', COUNT(DISTINCT p2c2.product_id) AS `count`' : '';
        $lang_id = (int)$this->config->get('config_language_id');
        $query = $this->db->query("SELECT c.category_id AS `id`, cd.name {$count_col} FROM `" . DB_PREFIX . "category` c INNER JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id AND cd.language_id = '{$lang_id}') LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c2 ON (c.category_id = p2c2.category_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (p2c2.product_id = p.product_id AND p.status = '1') WHERE c.parent_id = '" . (int)$ctx['id'] . "' AND c.status = '1' GROUP BY c.category_id ORDER BY c.sort_order ASC, cd.name ASC");
        return $query->rows;
    }

    public function getAttributeValues(array $ctx, int $attr_id, bool $show_count = false): array {
        $join = $this->contextJoin($ctx);
        $where = $this->contextWhere($ctx);
        $lang_id = (int)$this->config->get('config_language_id');
        $count_col = $show_count ? ', COUNT(DISTINCT p.product_id) AS `count`' : '';
        $query = $this->db->query("SELECT DISTINCT pa.text AS `name` {$count_col} FROM `" . DB_PREFIX . "product_attribute` pa INNER JOIN `" . DB_PREFIX . "product` p ON (pa.product_id = p.product_id) {$join} WHERE pa.attribute_id = '{$attr_id}' AND pa.language_id = '{$lang_id}' AND p.status = '1' AND pa.text != '' {$where} GROUP BY pa.text ORDER BY pa.text ASC");
        $rows = [];
        foreach ($query->rows as $row) { $rows[] = ['id' => $row['name'], 'name' => $row['name'], 'count' => $row['count'] ?? null]; }
        return $rows;
    }

    public function getOptionValues(array $ctx, int $option_id, bool $show_count = false): array {
        $join = $this->contextJoin($ctx);
        $where = $this->contextWhere($ctx);
        $lang_id = (int)$this->config->get('config_language_id');
        $count_col = $show_count ? ', COUNT(DISTINCT p.product_id) AS `count`' : '';
        $query = $this->db->query("SELECT DISTINCT ov.option_value_id AS `id`, ovd.name {$count_col} FROM `" . DB_PREFIX . "product_option_value` pov INNER JOIN `" . DB_PREFIX . "product` p ON (pov.product_id = p.product_id) INNER JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id AND ovd.language_id = '{$lang_id}') {$join} WHERE pov.option_id = '{$option_id}' AND p.status = '1' {$where} GROUP BY ov.option_value_id ORDER BY ov.sort_order ASC, ovd.name ASC");
        return $query->rows;
    }

    public function getOcFilterValues(array $ctx, int $filter_group_id, bool $show_count = false): array {
        $join = $this->contextJoin($ctx);
        $where = $this->contextWhere($ctx);
        $lang_id = (int)$this->config->get('config_language_id');
        $count_col = $show_count ? ', COUNT(DISTINCT p.product_id) AS `count`' : '';
        $query = $this->db->query("SELECT DISTINCT f.filter_id AS `id`, fd.name {$count_col} FROM `" . DB_PREFIX . "product_filter` pf INNER JOIN `" . DB_PREFIX . "product` p ON (pf.product_id = p.product_id) INNER JOIN `" . DB_PREFIX . "filter` f ON (pf.filter_id = f.filter_id AND f.filter_group_id = '{$filter_group_id}') INNER JOIN `" . DB_PREFIX . "filter_description` fd ON (f.filter_id = fd.filter_id AND fd.language_id = '{$lang_id}') {$join} WHERE p.status = '1' {$where} GROUP BY f.filter_id ORDER BY f.sort_order ASC, fd.name ASC");
        return $query->rows;
    }

    public function buildFilterCondition(array $active): array {
        $joins = '';
        $where = '';
        $lang_id = (int)$this->config->get('config_language_id');
        if (!empty($active['manufacturer_ids'])) {
            $ids = implode(',', array_map('intval', $active['manufacturer_ids']));
            $where .= " AND p.manufacturer_id IN ({$ids})";
        }
        if (!empty($active['category_ids'])) {
            $ids = implode(',', array_map('intval', $active['category_ids']));
            $joins .= " INNER JOIN `" . DB_PREFIX . "product_to_category` pnj_f_p2c ON (p.product_id = pnj_f_p2c.product_id AND pnj_f_p2c.category_id IN ({$ids}))";
        }
        if (!empty($active['attr']) && is_array($active['attr'])) {
            foreach ($active['attr'] as $attr_id => $values) {
                if (!is_array($values) || empty($values)) continue;
                $attr_id = (int)$attr_id;
                $vals = implode("','", array_map([$this->db, 'escape'], $values));
                $alias = 'pnj_f_attr_' . $attr_id;
                $joins .= " INNER JOIN `" . DB_PREFIX . "product_attribute` {$alias} ON ({$alias}.product_id = p.product_id AND {$alias}.attribute_id = '{$attr_id}' AND {$alias}.language_id = '{$lang_id}' AND {$alias}.text IN ('{$vals}'))";
            }
        }
        if (!empty($active['opt'])) {
            $ids = implode(',', array_map('intval', $active['opt']));
            $joins .= " INNER JOIN `" . DB_PREFIX . "product_option_value` pnj_f_pov ON (p.product_id = pnj_f_pov.product_id AND pnj_f_pov.option_value_id IN ({$ids}))";
        }
        if (!empty($active['filter_ids'])) {
            $ids = implode(',', array_map('intval', $active['filter_ids']));
            $joins .= " INNER JOIN `" . DB_PREFIX . "product_filter` pnj_f_pf ON (p.product_id = pnj_f_pf.product_id AND pnj_f_pf.filter_id IN ({$ids}))";
        }
        if (!empty($active['price_min']) || !empty($active['price_max'])) {
            $cg_id = (int)$this->config->get('config_customer_group_id');
            // Effective price subquery matching OC4 product model logic
            $price_expr = "COALESCE((SELECT (CASE WHEN ps.type = 'P' THEN (p.price - (p.price * (ps.price / 100))) WHEN ps.type = 'S' THEN (p.price - ps.price) ELSE ps.price END) FROM `" . DB_PREFIX . "product_discount` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '{$cg_id}' AND ps.quantity = '1' AND ps.special = '1' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), COALESCE((SELECT (CASE WHEN pd.type = 'P' THEN (p.price - (p.price * (pd.price / 100))) WHEN pd.type = 'S' THEN (p.price - pd.price) ELSE pd.price END) FROM `" . DB_PREFIX . "product_discount` pd WHERE pd.product_id = p.product_id AND pd.customer_group_id = '{$cg_id}' AND pd.quantity = '1' AND pd.special = '0' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW())) ORDER BY pd.priority ASC, pd.price ASC LIMIT 1), p.price))";
            
            if (!empty($active['price_min'])) $where .= " AND {$price_expr} >= '" . (float)$active['price_min'] . "'";
            if (!empty($active['price_max'])) $where .= " AND {$price_expr} <= '" . (float)$active['price_max'] . "'";
        }
        if (!empty($active['stock'])) {
            if ($active['stock'] === 'instock') $where .= " AND p.quantity > 0";
            elseif ($active['stock'] === 'outofstock') $where .= " AND p.quantity <= 0";
        }
        return ['joins' => $joins, 'where' => $where];
    }
}
