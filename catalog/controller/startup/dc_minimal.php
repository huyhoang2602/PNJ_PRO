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

            // Register model events for dynamic filters (reliable runtime registration)
            // OC4 triggers: model/{route}.{method}/before
            // OC4 actions: {extension_route}.{method}
            $this->event->register('model/catalog/product.getProducts/before', new \Opencart\System\Engine\Action('extension/dc_minimal/module/filter.beforeGetProducts'));
            $this->event->register('model/catalog/product.getTotalProducts/before', new \Opencart\System\Engine\Action('extension/dc_minimal/module/filter.beforeGetProducts'));
		}
	}

	public function before(string &$route, array &$args): void {
		// Inject assets on header load (using controller event to ensure Document is updated correctly)
		if ($route == 'common/header' || $route == 'extension/dc_minimal/common/header') {
			$this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/dc_minimal.css');
			$this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/stylesheet.css');
			
            // Load more CSS specifically for the homepage
            if (!isset($this->request->get['route']) || $this->request->get['route'] == 'common/home') {
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/home.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/hero.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/featured.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/category.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/brand.css');
                $this->document->addStyle('extension/dc_minimal/catalog/view/stylesheet/ship.css');
            }
		}
	}

	public function event(string &$route, array &$data, string &$code, string &$output = ''): void {
		// Template Override
		if (strpos($route, 'extension/') === false) {
			$view_path = DIR_EXTENSION . 'dc_minimal/catalog/view/template/' . $route . '.twig';
			if (is_file($view_path)) {
				$route = 'extension/dc_minimal/' . $route;
			}
		}

		// Inject data for specific routes
		if ($route == 'extension/dc_minimal/common/header' || $route == 'common/header') {
			$this->load->language('extension/dc_minimal/dc_minimal');
			
			$hotline = $this->config->get('theme_dc_minimal_phone');
			if (is_array($hotline)) {
				$data['dc_hotline'] = (string)reset($hotline);
			} else {
				$data['dc_hotline'] = (string)$hotline;
			}
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
            $this->injectDynamicFilters('category', $category_id, $data);
        } elseif (in_array($route, ['product/manufacturer_info', 'extension/dc_minimal/product/manufacturer_info'])) {
            $manufacturer_id = (int)($this->request->get['manufacturer_id'] ?? 0);
            $this->injectDynamicFilters('manufacturer', $manufacturer_id, $data);
        }
	}

    private function injectDynamicFilters(string $type, int $id, array &$data): void {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_filter');

        $status = (int)($settings['module_dc_minimal_filter_status'] ?? 0);
        $groups = $settings['module_dc_minimal_filter_groups'] ?? [];

        if (!$status || !is_array($groups) || !$groups) {
            $data['dynamic_filters'] = [];
            $data['price_min'] = $this->request->get['price_min'] ?? '';
            $data['price_max'] = $this->request->get['price_max'] ?? '';
            return;
        }

        $this->load->model('extension/dc_minimal/module/filter');
        $model = $this->model_extension_dc_minimal_module_filter;

        $ctx = ['type' => $type, 'id' => $id];

        usort($groups, function ($a, $b) {
            return (int)($a['sort_order'] ?? 0) <=> (int)($b['sort_order'] ?? 0);
        });

        $dynamic_filters = [];
        $data['price_min_range'] = 0;
        $data['price_max_range'] = 0;

        foreach ($groups as $group) {
            if (empty($group['enabled'])) continue;
            if (!in_array($type, $group['routes'] ?? [])) continue;

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
                    // Add dummy value to ensure it's added to dynamic_filters
                    $group_data['values'] = [['min' => $data['price_min_range'], 'max' => $data['price_max_range']]];
                    break;
                case 'manufacturer':
                    $group_data['values'] = $model->getManufacturers($ctx, $show_count);
                    break;
                case 'category':
                    $group_data['values'] = $model->getSubCategories($ctx, $show_count);
                    break;
                case 'attribute':
                    $group_data['values'] = $model->getAttributeValues($ctx, $group_data['source_id'], $show_count);
                    break;
                case 'option':
                    $group_data['values'] = $model->getOptionValues($ctx, $group_data['source_id'], $show_count);
                    break;
                case 'filter':
                    $group_data['values'] = $model->getOcFilterValues($ctx, $group_data['source_id'], $show_count);
                    break;
                case 'stock':
                    $group_data['values'] = [['id' => 'instock', 'name' => 'Còn hàng'], ['id' => 'outofstock', 'name' => 'Hết hàng']];
                    break;
            }

            if (!empty($group_data['values'])) {
                $dynamic_filters[] = $group_data;
            }
        }

        $data['dynamic_filters'] = $dynamic_filters;
        $data['price_min'] = $this->request->get['price_min'] ?? '';
        $data['price_max'] = $this->request->get['price_max'] ?? '';
    }
}