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
                'blocks'     => []
            ];

            if (!empty($tab_info['blocks'])) {
                foreach ($tab_info['blocks'] as $block) {
                    $block_data = [
                        'title' => $block['title'][$lang_id] ?? '',
                        'type'  => $block['type'],
                        'cols'  => (int)($block['cols'] ?? 1),
                        'icon'  => $block['icon'],
                        'items' => []
                    ];

                    $limit = (int)($block['limit'] ?? 10);
                    $source_id = (int)($block['source_id'] ?? 0);

                    switch ($block['type']) {
                        case 'manufacturer':
                            $results = $this->model_extension_dc_minimal_module_mega_menu->getManufacturersByGender($gender_attr_id, $gender_val, $limit);
                            foreach ($results as $result) {
                                $block_data['items'][] = [
                                    'name' => $result['name'],
                                    'href' => $this->url->link('product/manufacturer.info', 'manufacturer_id=' . $result['manufacturer_id'] . '&attr[' . $gender_attr_id . '][]=' . urlencode($gender_val)),
                                    'thumb'=> $result['image'] ? $this->model_tool_image->resize($result['image'], 100, 100) : ''
                                ];
                            }
                            break;

                        case 'category':
                            $results = $this->model_extension_dc_minimal_module_mega_menu->getCategoriesByGender($gender_attr_id, $gender_val, $source_id, $limit);
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
                                // For attribute clicks, we need a base path or just search
                                // Suggestion: Link to a "All products" category (id=0 or specific) or use the current if context exists.
                                // Defaulting to category search if no path specified in block. 
                                // Better: allow source_id to be attribute_id but maybe block has a base_category_id param too? 
                                // Let's use route=product/search for now if no path.
                                $block_data['items'][] = [
                                    'name' => $result['name'],
                                    'href' => $this->url->link('product/category', 'path=0&attr[' . $gender_attr_id . '][]=' . urlencode($gender_val) . '&attr[' . $source_id . '][]=' . urlencode($result['name']))
                                ];
                            }
                            break;
                        
                        case 'attribute_group':
                             // Implementation for attribute group discovery if needed
                            break;

                        case 'custom':
                            // Custom links can be handled here if source_id is URL or block has list
                            break;
                    }

                    if (!empty($block_data['items'])) {
                        $tab_data['blocks'][] = $block_data;
                    } else {
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
