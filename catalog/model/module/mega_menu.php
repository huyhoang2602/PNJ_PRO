<?php
namespace Opencart\Catalog\Model\Extension\DcMinimal\Module;

class MegaMenu extends \Opencart\System\Engine\Model {
    /**
     * Get manufacturers that have products matching a specific gender attribute value.
     */
    public function getManufacturersByGender(int $gender_attr_id, string $gender_value, int $limit = 10): array {
        $sql = "SELECT DISTINCT m.manufacturer_id, m.name, m.image 
                FROM `" . DB_PREFIX . "manufacturer` m 
                INNER JOIN `" . DB_PREFIX . "product` p ON (m.manufacturer_id = p.manufacturer_id) 
                INNER JOIN `" . DB_PREFIX . "product_attribute` pa ON (p.product_id = pa.product_id) 
                WHERE pa.attribute_id = '" . (int)$gender_attr_id . "' 
                  AND TRIM(pa.text) = '" . $this->db->escape($gender_value) . "' 
                  AND p.status = '1' 
                ORDER BY m.name ASC";
        
        if ($limit) $sql .= " LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get sub-categories that have products matching a specific gender attribute value.
     */
    public function getCategoriesByGender(int $gender_attr_id, string $gender_value, int $parent_id = 0, int $limit = 10): array {
        $lang_id = (int)$this->config->get('config_language_id');
        
        $sql = "SELECT DISTINCT c.category_id, cd.name 
                FROM `" . DB_PREFIX . "category` c 
                INNER JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id AND cd.language_id = '{$lang_id}') 
                INNER JOIN `" . DB_PREFIX . "product_to_category` p2c ON (c.category_id = p2c.category_id) 
                INNER JOIN `" . DB_PREFIX . "product` p ON (p2c.product_id = p.product_id) 
                INNER JOIN `" . DB_PREFIX . "product_attribute` pa ON (p.product_id = pa.product_id) 
                WHERE pa.attribute_id = '" . (int)$gender_attr_id . "' 
                  AND TRIM(pa.text) = '" . $this->db->escape($gender_value) . "' 
                  AND p.status = '1' 
                  AND c.status = '1'";

        if ($parent_id) {
            $sql .= " AND c.parent_id = '" . (int)$parent_id . "'";
        }

        $sql .= " ORDER BY c.sort_order ASC, cd.name ASC";

        if ($limit) $sql .= " LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get attribute values for a specific attribute_id, filtered by gender.
     */
    public function getAttributeValuesByGender(int $gender_attr_id, string $gender_value, int $target_attr_id, int $limit = 20): array {
        $lang_id = (int)$this->config->get('config_language_id');
        
        $sql = "SELECT DISTINCT pa_target.text AS `name` 
                FROM `" . DB_PREFIX . "product_attribute` pa_target 
                INNER JOIN `" . DB_PREFIX . "product` p ON (pa_target.product_id = p.product_id) 
                INNER JOIN `" . DB_PREFIX . "product_attribute` pa_gender ON (p.product_id = pa_gender.product_id) 
                WHERE pa_gender.attribute_id = '" . (int)$gender_attr_id . "' 
                  AND TRIM(pa_gender.text) = '" . $this->db->escape($gender_value) . "' 
                  AND pa_target.attribute_id = '" . (int)$target_attr_id . "' 
                  AND pa_target.language_id = '{$lang_id}' 
                  AND p.status = '1' 
                  AND pa_target.text != '' 
                ORDER BY pa_target.text ASC";

        if ($limit) $sql .= " LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get attribute groups filtered by gender context (optional if needed for high level discovery).
     */
    public function getAttributeGroupsByGender(int $gender_attr_id, string $gender_value, int $limit = 10): array {
        $lang_id = (int)$this->config->get('config_language_id');
        
        $sql = "SELECT DISTINCT ag.attribute_group_id, agd.name 
                FROM `" . DB_PREFIX . "attribute_group` ag 
                INNER JOIN `" . DB_PREFIX . "attribute_group_description` agd ON (ag.attribute_group_id = agd.attribute_group_id AND agd.language_id = '{$lang_id}') 
                INNER JOIN `" . DB_PREFIX . "attribute` a ON (ag.attribute_group_id = a.attribute_group_id) 
                INNER JOIN `" . DB_PREFIX . "product_attribute` pa_target ON (a.attribute_id = pa_target.attribute_id) 
                INNER JOIN `" . DB_PREFIX . "product` p ON (pa_target.product_id = p.product_id) 
                INNER JOIN `" . DB_PREFIX . "product_attribute` pa_gender ON (p.product_id = pa_gender.product_id) 
                WHERE pa_gender.attribute_id = '" . (int)$gender_attr_id . "' 
                  AND pa_gender.text = '" . $this->db->escape($gender_value) . "' 
                  AND p.status = '1' 
                ORDER BY agd.name ASC";

        if ($limit) $sql .= " LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get manufacturers by specific list of IDs.
     */
    public function getManufacturersByIds(array $manufacturer_ids): array {
        if (empty($manufacturer_ids)) return [];
        $sql = "SELECT DISTINCT m.manufacturer_id, m.name, m.image 
                FROM `" . DB_PREFIX . "manufacturer` m 
                WHERE m.manufacturer_id IN (" . implode(',', array_map('intval', $manufacturer_ids)) . ") 
                ORDER BY FIELD(m.manufacturer_id, " . implode(',', array_map('intval', $manufacturer_ids)) . ")";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * Get categories by specific list of IDs.
     */
    public function getCategoriesByIds(array $category_ids): array {
        if (empty($category_ids)) return [];
        $lang_id = (int)$this->config->get('config_language_id');
        $sql = "SELECT DISTINCT c.category_id, cd.name 
                FROM `" . DB_PREFIX . "category` c 
                INNER JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id AND cd.language_id = '{$lang_id}') 
                WHERE c.category_id IN (" . implode(',', array_map('intval', $category_ids)) . ") 
                ORDER BY FIELD(c.category_id, " . implode(',', array_map('intval', $category_ids)) . ")";
        $query = $this->db->query($sql);
        return $query->rows;
    }
}
