<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Startup;

use Opencart\System\Helper\Extension\Theme\Theme;

require_once DIR_EXTENSION . 'dc_minimal/system/helper/theme.php';

class DcMinimal extends \Opencart\System\Engine\Controller {
	public function index(): void {
		// Only register if theme is active globally AND extension status is 1
		if ($this->config->get('config_theme') == 'dc_minimal' && $this->config->get('theme_dc_minimal_status')) {
			// Register controller events for asset injection (happens BEFORE controller runs)
			$this->event->register('controller/*/before', new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.before'));
			
			// Register view events for template overrides and data injection
			$this->event->register('view/*/before', new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.event'));
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
	}
}