<?php
namespace Opencart\Admin\Model\Extension\DcMinimal\Module;

/**
 * Admin model for dc_minimal filter module.
 * Provides metadata for the configuration UI.
 */
class Filter extends \Opencart\System\Engine\Model {
    public function getDefaultGroups(): array {
        return [
            [
                'enabled'    => 1,
                'type'       => 'price',
                'label'      => 'Khoảng giá',
                'display_type' => 'range',
                'routes'     => ['category', 'manufacturer'],
                'sort_order' => 1
            ],
            [
                'enabled'    => 1,
                'type'       => 'manufacturer',
                'label'      => 'Thương hiệu',
                'display_type' => 'checkbox',
                'show_count' => 1,
                'routes'     => ['category', 'manufacturer'],
                'sort_order' => 2
            ],
            [
                'enabled'    => 1,
                'type'       => 'category',
                'label'      => 'Loại sản phẩm',
                'display_type' => 'checkbox',
                'show_count' => 1,
                'routes'     => ['category', 'manufacturer'],
                'sort_order' => 3
            ]
        ];
    }

    public function getAttributeList(): array {
        $query = $this->db->query("
            SELECT a.attribute_id, ad.name, agd.name AS `group`
            FROM `" . DB_PREFIX . "attribute` a
            LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (a.attribute_id = ad.attribute_id)
            LEFT JOIN `" . DB_PREFIX . "attribute_group_description` agd ON (a.attribute_group_id = agd.attribute_group_id)
            WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY agd.name, ad.name ASC
        ");
        return $query->rows;
    }

    public function getAttributeGroupList(): array {
        $query = $this->db->query("
            SELECT ag.attribute_group_id, agd.name
            FROM `" . DB_PREFIX . "attribute_group` ag
            LEFT JOIN `" . DB_PREFIX . "attribute_group_description` agd ON (ag.attribute_group_id = agd.attribute_group_id)
            WHERE agd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY agd.name ASC
        ");
        return $query->rows;
    }

    public function getOptionList(): array {
        $query = $this->db->query("
            SELECT o.option_id, od.name
            FROM `" . DB_PREFIX . "option` o
            LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id)
            WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY od.name ASC
        ");
        return $query->rows;
    }

    public function getOcFilterGroups(): array {
        $query = $this->db->query("
            SELECT fg.filter_group_id, fgd.name
            FROM `" . DB_PREFIX . "filter_group` fg
            LEFT JOIN `" . DB_PREFIX . "filter_group_description` fgd ON (fg.filter_group_id = fgd.filter_group_id)
            WHERE fgd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY fgd.name ASC
        ");
        return $query->rows;
    }
}
