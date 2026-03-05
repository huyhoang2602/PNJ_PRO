<?php
    namespace Opencart\Catalog\Controller\Extension\DcMinimal\Startup;
    use Opencart\System\Helper\Extension\Theme\Theme;
    require_once DIR_EXTENSION . 'dc_minimal/system/helper/theme.php';

    class DcMinimal extends \Opencart\System\Engine\Controller {
        public function index(): void {
            if ($this->config->get('config_theme') == 'dc_minimal' && $this->config->get('theme_dc_minimal_status')) {
                $this->event->register(
                    'view/*/before',
                    new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.addThemeSettings')
               );
                $this->event->register(
                    'view/*/before',
                    new \Opencart\System\Engine\Action('extension/dc_minimal/startup/dc_minimal.event')
                );
            }
        }

        public function event(string &$route, array &$args, mixed &$output): void {

            $override_path = DIR_EXTENSION . 'dc_minimal/catalog/view/template/' . $route . '.twig';
            if (is_file($override_path)) {
                $route = 'extension/dc_minimal/' . $route;
                
            }
        }

        public function addThemeSettings(string &$route, array &$args, mixed &$output): void { 
            
            if (!isset($args[0]) || !is_array($args[0])) {
                $args[0] = [];
            }
            $fields = [
                'phone', 'email', 'banner', 'header_bg', 'header_color', 'title_color',
                'primary_bg', 'primary', 'secondary_bg', 'secondary', 'status',
                'featured', 'latest', 'special', 'bestseller',
                'phone_label', 'email_label', 'cart_label', 'search_label', 'free_shipping',
                'header_title', 'header_subtitle'
            ];
            foreach ($fields as $f) {
                $args[0]['theme_dc_minimal_' . $f] = $this->config->get('theme_dc_minimal_' . $f);
            }

            $args[0]['language_id'] = $this->config->get('config_language_id');

            $is_home = false;

            if(!isset($this->request->get['route']) OR $this->request->get['route'] == 'common/home' OR $this->request->get['route'] == ''){
                $is_home = true;
            }

            $args[0]['theme_dc_minimal_header_bg'] = Theme::toRgba($args[0]['theme_dc_minimal_header_bg'], 0.6); 

            $args[0]['home_page'] = $is_home;
        }
    }