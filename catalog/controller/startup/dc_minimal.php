<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Startup;

use Opencart\System\Helper\Extension\Theme\Theme;

require_once DIR_EXTENSION . 'dc_minimal/system/helper/theme.php';

class DcMinimal extends \Opencart\System\Engine\Controller {
	public function index(): void {
        $this->log->write("PNJ DEBUG: Startup DcMinimal index called. Theme: " . $this->config->get('config_theme'));
		// Only register if theme is active globally AND extension status is 1
		if ($this->config->get('config_theme') == 'dc_minimal' && $this->config->get('theme_dc_minimal_status')) {
            $this->log->write("PNJ DEBUG: Theme active, registering events...");
			// Register controller events for asset injection (happens BEFORE controller runs)
			$this->event->register('controller/*/before', new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.before'));
			
			// Register view events for template overrides and data injection
			$this->event->register('view/*/before', new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.event'));

            // Register language events to catch individual language file loads
            $this->event->register('language/*/after', new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.eventLanguage'));

            // Register model events for dynamic filters
            $this->event->register('model/catalog/product.getProducts/before', new \Opencart\System\Engine\Action('extension/dc_minimal/module/filter.beforeGetProducts'));
            $this->event->register('model/catalog/product.getTotalProducts/before', new \Opencart\System\Engine\Action('extension/dc_minimal/module/filter.beforeGetProducts'));
		}
	}

    /**
     * Event handler for language loading.
     * Overrides core strings with theme-specific ones.
     */
    public function eventLanguage(string &$route, string &$prefix, string &$code, array &$output): void {
        $lang_page = $route;

        if (strpos($lang_page, 'extension/dc_minimal/') === 0) {
            $lang_page = str_replace('extension/dc_minimal/', '', $lang_page);
        }

        if (strpos($lang_page, 'extension/opencart/') === 0) {
            $lang_page = str_replace('extension/opencart/', '', $lang_page);
        }

        $translated = $this->loadThemeLanguageFile($lang_page, $this->getLanguageCode($code));

        if ($prefix && $translated) {
            foreach ($translated as $key => $value) {
                $translated[$prefix . '_' . $key] = $value;
                unset($translated[$key]);
            }
        }

        if ($translated) {
            foreach ($translated as $key => $value) {
                if (is_string($value)) {
                    $this->language->set($key, $value);
                }
            }

            $output = array_merge($output, $translated);
        }
    }

    private function getLanguageCode(string $code = ''): string {
        if ($code) {
            return $code;
        }

        return (string)($this->request->get['language'] ?? $this->config->get('config_language') ?: $this->config->get('language_code'));
    }

    private function loadThemeLanguageFile(string $route, string $code): array {
        $route = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $route);
        $code = preg_replace('/[^a-zA-Z0-9_\-]/', '', $code);

        if (!$route || !$code) {
            return [];
        }

        $file = DIR_EXTENSION . 'dc_minimal/catalog/language/' . $code . '/' . $route . '.php';

        if (!is_file($file)) {
            return [];
        }

        $_ = [];

        require($file);

        return $_;
    }

	public function before(string &$route, array &$args): void {
		// Inject assets on header load (using controller event to ensure Document is updated correctly)
		if ($route == 'common/header' || $route == 'extension/dc_minimal/common/header') {
			$this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/dc_minimal.css');
			$this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/stylesheet.css');
			
            // Load more CSS specifically for the homepage and brand pages
            $route_get = (string)($this->request->get['route'] ?? 'common/home');
            
            if ($route_get == 'common/home') {
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/home.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/hero.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/featured.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/category.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/brand.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/ship.css');
            } elseif (strpos($route_get, 'product/manufacturer') !== false) {
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/brand.css');
            } elseif (strpos($route_get, 'account/') !== false) {
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/account.css');
            } elseif (strpos($route_get, 'checkout/') !== false) {
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/checkout.css');
            }

            // MEGA MENU ASSETS
            $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/mega_menu.css');
            $this->document->addScript('extension/dc_minimal/catalog/view/javascript/mega_menu.js');
		}
	}

	public function event(string &$route, array &$data): void {
        $this->log->write("PNJ DEBUG: event() trigger caught for route: " . $route);
		// Template Override
		if (strpos($route, 'extension/') === false) {
			$view_path = DIR_EXTENSION . 'dc_minimal/catalog/view/template/' . $route . '.twig';
			if (is_file($view_path)) {
				$route = 'extension/dc_minimal/' . $route;
			}
		} elseif (strpos($route, 'extension/opencart/') !== false) {
            $stripped_route = str_replace('extension/opencart/', '', $route);
            $view_path = DIR_EXTENSION . 'dc_minimal/catalog/view/template/' . $stripped_route . '.twig';
            if (is_file($view_path)) {
                $route = 'extension/dc_minimal/' . $stripped_route;
            }
        }
        
        // --- Internationalization Injection ---
        // For any view being rendered, try to load a corresponding language file from our theme extension
        // This ensures core pages like checkout/cart get our Vietnamese strings even if core lacks them.
        
        $lang_page = $route;
        
        // Strip theme prefix if present
        if (strpos($lang_page, 'extension/dc_minimal/') === 0) {
            $lang_page = str_replace('extension/dc_minimal/', '', $lang_page);
        }
        
        // Strip core extension prefix if present (for totals, etc)
        if (strpos($lang_page, 'extension/opencart/') === 0) {
            $lang_page = str_replace('extension/opencart/', '', $lang_page);
        }

        if ($lang_page) {
            $language_code = $this->getLanguageCode();
            $data['language_code'] = $language_code;

            if ($lang_page !== 'common/header') {
                $data['language'] = $language_code;
            }

            $protected_language_keys = preg_match('/^(account|checkout)\//', $lang_page) ? [] : array_fill_keys(array_keys($data), true);

            // Load base theme translations
            $base_translated = $this->loadThemeLanguageFile($language_code, $language_code);
            if ($base_translated) {
                $data = $this->mergeThemeTranslations($data, $base_translated, $protected_language_keys);
            }

            // Load the page-specific language file
            $translated = $this->loadThemeLanguageFile($lang_page, $language_code);
            if ($translated) {
                $data = $this->mergeThemeTranslations($data, $translated, $protected_language_keys);
            }
            
            // Special handling for sub-views like 'checkout/cart_list' -> load 'checkout/cart'
            if (strpos($lang_page, '_') !== false) {
                $lang_base = explode('_', $lang_page)[0];
                $translated_base = $this->loadThemeLanguageFile($lang_base, $language_code);
                if ($translated_base) {
                    $data = $this->mergeThemeTranslations($data, $translated_base, $protected_language_keys);
                }
            }

            // FINAL DEBUG LOGGING
            if (strpos($lang_page, 'checkout') !== false) {
                $this->log->write("PNJ DEBUG FINAL: Page: " . $lang_page . " | Entry: " . ($data['column_image'] ?? 'N/A'));
            }
        }
        // ---------------------------------------

		// Inject data for specific routes
		if ($route == 'extension/dc_minimal/common/header' || $route == 'common/header') {
			$this->load->language('extension/dc_minimal/dc_minimal');
            $this->load->model('catalog/manufacturer');

            $language_code = $this->getLanguageCode();
            $locale_translated = $this->loadThemeLanguageFile('vi-vn', $language_code);

            $data['lang'] = $language_code;
            if (!empty($locale_translated['direction'])) {
                $data['direction'] = $locale_translated['direction'];
            }
			
			$hotline = $this->config->get('theme_dc_minimal_phone');
			if (is_array($hotline)) {
				$data['dc_hotline'] = (string)reset($hotline);
			} else {
				$data['dc_hotline'] = (string)$hotline;
			}

            // GET TOP SEARCH CATEGORIES
            $data['search_categories'] = [];
            $this->load->model('catalog/category');
            $categories = $this->model_catalog_category->getCategories(0);
            foreach ($categories as $category) {
                if (count($data['search_categories']) >= 6) break;
                $data['search_categories'][] = [
                    'name' => (string)$category['name'],
                    'href' => (string)$this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'], true)
                ];
            }

            // GET BRANDS FOR SEARCH MODAL
            $data['brands'] = [];
            $this->load->model('catalog/manufacturer');
            $this->load->model('tool/image');
            
            // Get custom brand logos from Slider module settings
            $custom_brands = $this->config->get('module_brand_brands');
            $brand_logo_map = [];
            if (!empty($custom_brands) && is_array($custom_brands)) {
                foreach ($custom_brands as $brand) {
                    if (!empty($brand['manufacturer_id']) && !empty($brand['logo'])) {
                        $brand_logo_map[$brand['manufacturer_id']] = $brand['logo'];
                    }
                }
            }
            
            $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
            foreach ($manufacturers as $manufacturer) {
                // Priority: module_brand_brands (logo) > manufacturer record (image)
                $image_to_use = $brand_logo_map[$manufacturer['manufacturer_id']] ?? ($manufacturer['image'] ?? '');
                $image_path = html_entity_decode($image_to_use, ENT_QUOTES, 'UTF-8');
                
                if ($image_path && is_file(DIR_IMAGE . $image_path)) {
                    $image = $this->model_tool_image->resize($image_path, 100, 50);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 100, 50);
                }

                $data['brands'][] = [
                    'name'  => (string)$manufacturer['name'],
                    'thumb' => $image,
                    'href'  => (string)$this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $manufacturer['manufacturer_id'], true)
                ];
            }

            // GET PRODUCTS FOR SEARCH MODAL
            $data['search_products'] = [];
            $this->load->model('catalog/product');
            $this->load->model('tool/image');
            
            $results = $this->model_catalog_product->getProducts(['sort' => 'p.viewed', 'order' => 'DESC', 'start' => 0, 'limit' => 3]);
            foreach ($results as $result) {
                if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
                    $image = $this->model_tool_image->resize($result['image'], 200, 200);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 200, 200);
                }

                $data['search_products'][] = [
                    'name'  => $result['name'],
                    'thumb' => $image,
                    'href'  => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'], true)
                ];
            }

            // FORCE PNJ LOGO FALLBACK IF EMPTY
            if (empty($data['logo'])) {
                $base_url = $this->config->get('config_url');
                if (is_file(\DIR_IMAGE . 'catalog/demo/logopnj.png')) {
                    $data['logo'] = $base_url . 'image/catalog/demo/logopnj.png';
                } elseif (is_file(\DIR_IMAGE . 'catalog/demo/manufacturer/logo_brand_pnj.png')) {
                    $data['logo'] = $base_url . 'image/catalog/demo/manufacturer/logo_brand_pnj.png';
                }
            }

            // DC Top Banner
            $data['top_banner'] = '';
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = 'dc_minimal.dc_top_banner'");
            foreach ($query->rows as $top_banner) {
                $top_banner_setting = json_decode($top_banner['setting'], true);
                if (isset($top_banner_setting['status']) && $top_banner_setting['status']) {
                    $data['top_banner'] = $this->load->controller('extension/dc_minimal/module/dc_top_banner', ['module_id' => $top_banner['module_id']]);
                    break;
                }
            }

            // PNJ MEGA MENU
            $data['mega_menu'] = $this->load->controller('extension/dc_minimal/module/mega_menu');
		}

		// Home page specific data
		if (in_array($route, ['common/home', 'extension/dc_minimal/common/home'])) {
			$data['theme_dc_minimal_primary_color'] = (string)$this->config->get('theme_dc_minimal_primary_color');
			$data['theme_dc_minimal_secondary_color'] = (string)$this->config->get('theme_dc_minimal_secondary_color');
			
			$header_bg = $this->config->get('theme_dc_minimal_header_bg');
			if ($header_bg) {
				$data['theme_dc_minimal_header_bg_rgba'] = \Opencart\System\Helper\Extension\Theme\Theme::toRgba($header_bg, 0.6);
			} else {
				$data['theme_dc_minimal_header_bg_rgba'] = 'rgba(29, 37, 47, 0.6)';
			}
			
			$data['home_page'] = true;
		}

        // PNJ Filter Data Injection
        if (in_array($route, ['product/category', 'extension/dc_minimal/product/category'])) {
            $path = (string)($this->request->get['path'] ?? '');
            $parts = $path ? explode('_', $path) : [];
            $category_id = $parts ? (int)end($parts) : 0;
            
            // Inject action URL for AJAX
            $url = '';
            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }
            $data['action'] = $this->url->link('product/category', 'language=' . $this->config->get('config_language') . $url, true);

            $this->injectDynamicFilters('category', $category_id, $data);
        } elseif (in_array($route, ['product/manufacturer.info', 'product/manufacturer_info', 'extension/dc_minimal/product/manufacturer.info', 'extension/dc_minimal/product/manufacturer_info'])) {
            $manufacturer_id = (int)($this->request->get['manufacturer_id'] ?? 0);
            
            // Inject action URL for AJAX
            $url = '';
            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }
            $data['action'] = $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . $url, true);

            $this->injectDynamicFilters('manufacturer', $manufacturer_id, $data);

            // Inject Manufacturer Image for Banner
            $this->load->model('tool/image');
            $this->load->model('catalog/manufacturer');
            $m_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
            
            if ($m_info) {
                // Check custom logos
                $custom_brands = $this->config->get('module_brand_brands');
                $brand_logo = '';
                if (!empty($custom_brands) && is_array($custom_brands)) {
                    foreach ($custom_brands as $brand) {
                        if ($brand['manufacturer_id'] == $manufacturer_id && !empty($brand['logo'])) {
                            $brand_logo = $brand['logo'];
                            break;
                        }
                    }
                }
                
                $image_to_use = $brand_logo ?: ($m_info['image'] ?? '');
                $image_path = html_entity_decode($image_to_use, ENT_QUOTES, 'UTF-8');
                
                if ($image_path && is_file(DIR_IMAGE . $image_path)) {
                    $data['manufacturer_image'] = $this->model_tool_image->resize($image_path, 200, 200);
                } else {
                    $data['manufacturer_image'] = ''; // No image or placeholder if you prefer
                }
            }
        } elseif (in_array($route, ['product/search', 'extension/dc_minimal/product/search'])) {
            $url = '';
            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }
            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }
            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }
            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }
            
            $data['action'] = $this->url->link('product/search', 'language=' . $this->config->get('config_language') . $url, true);
            $this->injectDynamicFilters('search', 0, $data);
        } elseif (in_array($route, ['product/special', 'extension/dc_minimal/product/special'])) {
            $data['action'] = $this->url->link('product/special', 'language=' . $this->config->get('config_language'), true);
            $this->injectDynamicFilters('special', 0, $data);
        }
 elseif (in_array($route, ['product/manufacturer_list', 'extension/dc_minimal/product/manufacturer_list'])) {
            // Inject images for brand index
            $this->load->model('tool/image');
            
            // Get custom brand logos from Slider module settings
            $custom_brands = $this->config->get('module_brand_brands');
            $brand_logo_map = [];
            if (!empty($custom_brands) && is_array($custom_brands)) {
                foreach ($custom_brands as $brand) {
                    if (!empty($brand['manufacturer_id']) && !empty($brand['logo'])) {
                        $brand_logo_map[$brand['manufacturer_id']] = $brand['logo'];
                    }
                }
            }
            
            if (!empty($data['categories'])) {
                foreach ($data['categories'] as &$category) {
                    if (!empty($category['manufacturer'])) {
                        foreach ($category['manufacturer'] as &$manufacturer) {
                            $manufacturer_id = $manufacturer['manufacturer_id'] ?? 0;
                            
                            // Check if custom logo exists, otherwise use default manufacturer image
                            $image_to_use = $brand_logo_map[$manufacturer_id] ?? ($manufacturer['image'] ?? '');
                            $image_path = html_entity_decode($image_to_use, ENT_QUOTES, 'UTF-8');
                            
                            if ($image_path && is_file(DIR_IMAGE . $image_path)) {
                                $manufacturer['image'] = $this->model_tool_image->resize($image_path, 150, 150);
                            } else {
                                $manufacturer['image'] = $this->model_tool_image->resize('placeholder.png', 150, 150);
                            }
                        }
                    }
                }
            }
        }
	}

    private function mergeThemeTranslations(array $data, array $translations, array $protected_keys): array {
        foreach ($translations as $key => $value) {
            if (!isset($protected_keys[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function injectDynamicFilters(string $type, int $id, array &$data): void {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_filter');

        $status = (int)($settings['module_dc_minimal_filter_status'] ?? 0);
        $groups = $settings['module_dc_minimal_filter_groups'] ?? [];

        // Load language file for filters
        $this->load->language('extension/dc_minimal/dc_minimal');

        if (!$status || !is_array($groups) || !$groups) {
            $data['dynamic_filters'] = [];
            $data['price_min'] = $this->request->get['price_min'] ?? '';
            $data['price_max'] = $this->request->get['price_max'] ?? '';
            
            // UI Strings 
            $this->load->language('extension/dc_minimal/dc_minimal');
            $data['text_sort']  = $this->language->get('text_sort');
            $data['text_limit'] = $this->language->get('text_limit');
            return;
        }

        $this->load->model('extension/dc_minimal/module/filter');
        $model = $this->model_extension_dc_minimal_module_filter;

        // Extract active filters for dependent filtering
        $active = [];
        if (isset($this->request->get['manufacturer_id'])) {
            $active['manufacturer_ids'] = explode(',', (string)$this->request->get['manufacturer_id']);
        }
        if (isset($this->request->get['sub_cat'])) {
            $active['category_ids'] = explode(',', (string)$this->request->get['sub_cat']);
        }
        if (isset($this->request->get['attr']) && is_array($this->request->get['attr'])) {
            $active['attr'] = $this->request->get['attr'];
        }
        if (isset($this->request->get['opt'])) {
            $active['opt'] = explode(',', (string)$this->request->get['opt']);
        }
        if (isset($this->request->get['filter'])) {
            $active['filter_ids'] = explode(',', (string)$this->request->get['filter']);
        }
        if (isset($this->request->get['price_min'])) {
            $active['price_min'] = $this->request->get['price_min'];
        }
        if (isset($this->request->get['price_max'])) {
            $active['price_max'] = $this->request->get['price_max'];
        }
        if (isset($this->request->get['stock'])) {
            $active['stock'] = $this->request->get['stock'];
        }

        $ctx = ['type' => $type, 'id' => $id];

        usort($groups, function ($a, $b) {
            return (int)($a['sort_order'] ?? 0) <=> (int)($b['sort_order'] ?? 0);
        });

        // Ensure language is loaded for non-header contexts (search, special, etc)
        $this->load->language('extension/dc_minimal/dc_minimal');

        $dynamic_filters = [];
        $data['price_min_range'] = 0;
        $data['price_max_range'] = 0;

        $log = "[" . date('H:i:s') . "] Type: $type, ID: $id, Groups Count: " . count($groups) . "\n";

        foreach ($groups as $group) {
            $g_type = $group['type'] ?? 'unknown';
            $g_label = $group['label'] ?? 'no-label';
            
            // Check enabled
            if (isset($group['enabled']) && !$group['enabled']) {
                $log .= "  Skipping group $g_type ($g_label): Not enabled\n";
                continue;
            }
            
            // Check routes
            if (!in_array($type, $group['routes'] ?? [])) {
                $log .= "  Skipping group $g_type ($g_label): Route $type not in " . implode(',', $group['routes'] ?? []) . "\n";
                continue;
            }

            $group_data = [
                'type'      => $group['type'] ?? '',
                'label'     => $group['label'] ?? '',
                'display'   => $group['display_type'] ?? 'checkbox',
                'source_id' => (int)($group['source_id'] ?? 0),
                'values'    => []
            ];

            $show_count = !empty($group['show_count']);

            switch ($group_data['type']) {
                case 'price':
                    $range = $model->getPriceRange($ctx);
                    $data['price_min_range'] = (float)($range['min'] ?? 0);
                    $data['price_max_range'] = (float)($range['max'] ?? 0);
                    $group_data['values'] = [['min' => $data['price_min_range'], 'max' => $data['price_max_range']]];
                    break;
                case 'manufacturer':
                    $group_data['values'] = $model->getManufacturers($ctx, $show_count, $active);
                    break;
                case 'category':
                    $group_data['values'] = $model->getSubCategories($ctx, $show_count, $active);
                    break;
                case 'attribute':
                    $group_data['values'] = $model->getAttributeValues($ctx, $group_data['source_id'], $show_count, $active);
                    break;
                case 'option':
                    $group_data['values'] = $model->getOptionValues($ctx, $group_data['source_id'], $show_count, $active);
                    break;
                case 'filter':
                    $group_data['values'] = $model->getOcFilterValues($ctx, $group_data['source_id'], $show_count, $active);
                    break;
                case 'stock':
                    $group_data['values'] = [
                        ['id' => 'instock', 'name' => $this->language->get('text_instock')],
                        ['id' => 'outofstock', 'name' => $this->language->get('text_outofstock')]
                    ];
                    break;
            }

            // Localize label from setting
            $group_data['label'] = $this->localizeFilterLabel($group_data['label'], $group_data['type']);

            // Keep group if it has values or active filters
            $is_active = false;
            switch ($group_data['type']) {
                case 'manufacturer': $is_active = !empty($active['manufacturer_ids']); break;
                case 'category': $is_active = !empty($active['category_ids']); break;
                case 'attribute': $is_active = !empty($active['attr'][$group_data['source_id']]); break;
                case 'option': $is_active = !empty($active['opt']); break; 
                case 'filter': $is_active = !empty($active['filter_ids']); break;
                case 'price': $is_active = (!empty($active['price_min']) || !empty($active['price_max'])); break;
                case 'stock': $is_active = !empty($active['stock']); break;
            }

            $is_active = !empty($active[$group_data['type']]) || !empty($active['attribute_' . $group_data['source_id']]);
            
            if (!empty($group_data['values']) || $is_active || in_array($group_data['type'], ['price', 'stock'])) {
                $dynamic_filters[] = $group_data;
                $log .= "  Added group $g_type ($g_label). Values count: " . count($group_data['values']) . "\n";
            } else {
                $log .= "  Skipping group $g_type ($g_label): Empty values and not in forced list\n";
            }
        }

        $data['dynamic_filters'] = $dynamic_filters;
        
        $log .= "Added filters: " . implode(', ', array_column($dynamic_filters, 'type')) . "\n";
        file_put_contents(DIR_STORAGE . 'logs/filter_debug.log', $log, FILE_APPEND);
        
        $data['price_min'] = $this->request->get['price_min'] ?? '';
        $data['price_max'] = $this->request->get['price_max'] ?? '';
        
        // Localized UI helper strings
        $data['text_sort']  = $this->language->get('text_sort');
        $data['text_limit'] = $this->language->get('text_limit');
        
        $data['manufacturer_ids'] = isset($this->request->get['manufacturer_id']) ? explode(',', (string)$this->request->get['manufacturer_id']) : [];
        $data['category_ids'] = isset($this->request->get['sub_cat']) ? explode(',', (string)$this->request->get['sub_cat']) : [];
        $data['attr'] = $this->request->get['attr'] ?? [];
        $data['opt'] = isset($this->request->get['opt']) ? explode(',', (string)$this->request->get['opt']) : [];
        $data['filter_ids'] = isset($this->request->get['filter']) ? explode(',', (string)$this->request->get['filter']) : [];
        $data['stock'] = $this->request->get['stock'] ?? '';
        
        // Pass UI text for templates
        $data['text_from']              = $this->language->get('text_from');
        $data['text_to']                = $this->language->get('text_to');
        $data['text_currency_hint_pnj'] = $this->language->get('text_currency_hint_pnj');
        $data['text_apply']             = $this->language->get('text_apply');
        $data['text_clear']             = $this->language->get('text_clear');
        $data['text_clear_all']         = $this->language->get('text_clear_all');
    }


    private function localizeFilterLabel(string $label, string $type): string {
        // 1. Try to match by type-specific key
        $key = 'text_filter_' . $type;
        $translated = $this->language->get($key);
        
        if ($translated != $key) {
            return $translated;
        }

        // 2. Map common hardcoded PNJ labels
        $map = [
            'Khoảng giá'    => $this->language->get('text_filter_price'),
            'Thương hiệu'   => $this->language->get('text_filter_manufacturer'),
            'Loại sản phẩm' => $this->language->get('text_filter_category'),
            'Chất liệu'     => $this->language->get('text_filter_material'),
            'Trọng lượng'   => $this->language->get('text_filter_weight')
        ];

        return $map[$label] ?? $label;
    }
}
