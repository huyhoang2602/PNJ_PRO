<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class MegaMenu extends \Opencart\System\Engine\Controller {
    public function index(): string {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_mega_menu');

        if (empty($settings['module_dc_minimal_mega_menu_status'])) {
            return '';
        }

        $cache_on = !empty($settings['module_dc_minimal_mega_menu_cache_status']);
        $lang_id = (int)$this->config->get('config_language_id');
        $cache_key = 'dc_minimal.mega_menu.' . $lang_id . '.' . (int)$this->config->get('config_store_id');

        if ($cache_on) {
            $cached_data = $this->cache->get($cache_key);
            if ($cached_data) {
                return $this->load->view('extension/dc_minimal/module/mega_menu', $cached_data);
            }
        }

        $gender_attr_id = (int)($settings['module_dc_minimal_mega_menu_gender_attr_id'] ?? 0);
        $tabs_config = $settings['module_dc_minimal_mega_menu_tabs'] ?? [];
        
        // Build brand logo map from Brand module settings (Global & Instances)
        $brand_logo_map = [];
        
        // Check global settings
        $brand_settings = $this->config->get('module_brand_brands');
        if (!empty($brand_settings)) {
            foreach ($brand_settings as $b) {
                if (!empty($b['manufacturer_id']) && !empty($b['logo'])) {
                    $brand_logo_map[$b['manufacturer_id']] = $b['logo'];
                }
            }
        }

        // Check module instances (OpenCart 4 stores instances in `module` table)
        $module_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = 'brand'");
        foreach ($module_query->rows as $module) {
            $module_settings = json_decode($module['setting'], true);
            if (!empty($module_settings['module_brand_brands'])) {
                foreach ($module_settings['module_brand_brands'] as $b) {
                    if (!empty($b['manufacturer_id']) && !empty($b['logo'])) {
                        $brand_logo_map[$b['manufacturer_id']] = $b['logo'];
                    }
                }
            }
        }

        $this->load->model('extension/dc_minimal/module/mega_menu');
        $this->load->model('tool/image');

        $data['tabs'] = [];

        foreach ($tabs_config as $tab_row => $tab_info) {
            $gender_val = $tab_info['gender_val'] ?? '';
            if (!$gender_val) continue;

            $tab_data = [
                'id'         => 'tab-' . $tab_row,
                'title'      => $tab_info['title'][$lang_id] ?? $gender_val,
                'gender_val' => $gender_val,
                'href'       => $this->url->link('product/category', 'path=0&attr[' . $gender_attr_id . '][]=' . urlencode($gender_val)),
                'menu_items' => [] // Will contain groups
            ];

            if (!empty($tab_info['blocks'])) {
                $groups = [];

                foreach ($tab_info['blocks'] as $block) {
                    $display_group_info = $block['display_group'] ?? [];
                    $display_group = is_array($display_group_info) ? trim($display_group_info[$lang_id] ?? '') : trim($display_group_info);
                    
                    // If no group, use block title as group key for unique left item
                    $group_key = $display_group ?: ('single-' . bin2hex(random_bytes(4)));
                    $group_title = $display_group ?: ($block['title'][$lang_id] ?? '');

                    if (!isset($groups[$group_key])) {
                        $groups[$group_key] = [
                            'title'  => $group_title,
                            'icon'   => $block['icon'] ?? '',
                            'blocks' => []
                        ];
                    } elseif (empty($groups[$group_key]['icon']) && !empty($block['icon'])) {
                        $groups[$group_key]['icon'] = $block['icon'];
                    }

                    $block_data = [
                        'title' => $block['title'][$lang_id] ?? '',
                        'type'  => $block['type'] ?? 'category',
                        'cols'  => $block['cols'] ?? 1,
                        'items' => []
                    ];

                    // Data Fetching
                    $limit = (int)($block['limit'] ?? 10);
                    $source_id = (int)($block['source_id'] ?? 0);
                    $selection_mode = $block['selection_mode'] ?? 'auto';
                    $selected_ids = $block['selected_ids'] ?? [];

                    switch ($block['type']) {
                        case 'manufacturer':
                            if (($block['selection_mode'] ?? 'auto') === 'manual') {
                                $results = $this->model_extension_dc_minimal_module_mega_menu->getManufacturersByIds($block['selected_ids'] ?? []);
                            } else {
                                $results = $this->model_extension_dc_minimal_module_mega_menu->getManufacturersByGender($gender_attr_id, $gender_val, $limit);
                            }
                            foreach ($results as $result) {
                                // Use custom logo if available in brand module, NEVER fallback to manufacturer image (avoid model photos)
                                $logo_image = $brand_logo_map[$result['manufacturer_id']] ?? '';
                                
                                $block_data['items'][] = [
                                    'name'  => $result['name'],
                                    'href'  => $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $result['manufacturer_id']),
                                    'thumb' => $logo_image ? $this->model_tool_image->resize($logo_image, 160, 80) : ''
                                ];
                            }
                            break;

                        case 'category':
                            if ($selection_mode == 'manual' && !empty($selected_ids)) {
                                $results = $this->model_extension_dc_minimal_module_mega_menu->getCategoriesByIds($selected_ids);
                            } else {
                                $results = $this->model_extension_dc_minimal_module_mega_menu->getCategoriesByGender($gender_attr_id, $gender_val, $source_id, $limit);
                            }
                            foreach ($results as $result) {
                                $block_data['items'][] = [
                                    'name' => $result['name'],
                                    'href' => $this->url->link('product/category', 'path=' . $result['category_id'] . '&attr[' . $gender_attr_id . '][]=' . urlencode($gender_val))
                                ];
                            }
                            break;

                        case 'attribute':
                            $results = $this->model_extension_dc_minimal_module_mega_menu->getAttributeValuesByGender($gender_attr_id, $gender_val, $source_id, $limit);
                            foreach ($results as $result) {
                                $block_data['items'][] = [
                                    'name' => $result['name'],
                                    'href' => $this->url->link('product/category', 'path=0&attr[' . $gender_attr_id . '][]=' . urlencode($gender_val) . '&attr[' . $source_id . '][]=' . urlencode($result['name']))
                                ];
                            }
                            break;
                        
                        case 'attribute_group':
                            $results = $this->model_extension_dc_minimal_module_mega_menu->getAttributeGroupsByGender($gender_attr_id, $gender_val, $limit);
                            foreach ($results as $result) {
                                $block_data['items'][] = [
                                    'name' => $result['name'],
                                    'href' => '#' // Group details link if needed
                                ];
                            }
                            break;

                        case 'custom':
                            $block_data['items'][] = [
                                'name' => $block_data['title'],
                                'href' => $block['source_id'] // treat source_id as URL or param
                            ];
                            break;
                    }

                    if (!empty($block_data['items'])) {
                        $groups[$group_key]['blocks'][] = $block_data;
                    }
                }

                // Filter out empty groups
                foreach ($groups as $group) {
                    if (!empty($group['blocks'])) {
                        $tab_data['menu_items'][] = $group;
                    }
                }
            }

            $data['tabs'][] = $tab_data;
        }

        $data['search_status'] = !empty($settings['module_dc_minimal_mega_menu_search_status']);
        
        $output = $this->load->view('extension/dc_minimal/module/mega_menu', $data);

        if ($cache_on) {
            $this->cache->set($cache_key, $data);
        }

        return $output;
    }
}
