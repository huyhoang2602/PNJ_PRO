<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

class MegaMenu extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/dc_minimal/module/mega_menu');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->checkEvents();

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/dc_minimal/module/mega_menu', 'user_token=' . $this->session->data['user_token'])]
        ];

        $data['save'] = $this->url->link('extension/dc_minimal/module/mega_menu.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_mega_menu');

        $data['status'] = $settings['module_dc_minimal_mega_menu_status'] ?? 0;
        $data['gender_attr_id'] = $settings['module_dc_minimal_mega_menu_gender_attr_id'] ?? 81; // Fallback to 81 or detected
        $data['tabs'] = $settings['module_dc_minimal_mega_menu_tabs'] ?? [];
        $data['search_status'] = $settings['module_dc_minimal_mega_menu_search_status'] ?? 1;
        $data['cache_status'] = $settings['module_dc_minimal_mega_menu_cache_status'] ?? 1;

        // Data for selects
        $this->load->model('extension/dc_minimal/module/filter');
        $data['all_attributes'] = $this->model_extension_dc_minimal_module_filter->getAttributeList();
        $data['all_attribute_groups'] = $this->model_extension_dc_minimal_module_filter->getAttributeGroupList();
        
        $this->load->model('catalog/category');
        $data['all_categories'] = $this->model_catalog_category->getCategories(['sort' => 'name']);

        $this->load->model('catalog/manufacturer');
        $data['all_manufacturers'] = $this->model_catalog_manufacturer->getManufacturers(['sort' => 'name']);

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dc_minimal/module/mega_menu', $data));
    }

    public function save(): void {
        $this->load->language('extension/dc_minimal/module/mega_menu');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/dc_minimal/module/mega_menu')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_dc_minimal_mega_menu', [
                'module_dc_minimal_mega_menu_status' => (int)($this->request->post['status'] ?? 0),
                'module_dc_minimal_mega_menu_gender_attr_id' => (int)($this->request->post['gender_attr_id'] ?? 0),
                'module_dc_minimal_mega_menu_tabs' => $this->request->post['tabs'] ?? [],
                'module_dc_minimal_mega_menu_search_status' => (int)($this->request->post['search_status'] ?? 0),
                'module_dc_minimal_mega_menu_cache_status' => (int)($this->request->post['cache_status'] ?? 0)
            ]);

            $this->cache->delete('dc_minimal.mega_menu');

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function checkEvents(): void {
        $this->load->model('setting/event');
        
        $events = [
            [
                'code' => 'dc_minimal_mega_menu_clear_cache_category',
                'trigger' => 'admin/model/catalog/category/editCategory/after',
                'action' => 'extension/dc_minimal/module/mega_menu|clearCache'
            ],
            [
                'code' => 'dc_minimal_mega_menu_clear_cache_product',
                'trigger' => 'admin/model/catalog/product/editProduct/after',
                'action' => 'extension/dc_minimal/module/mega_menu|clearCache'
            ],
            [
                'code' => 'dc_minimal_header_language',
                'trigger' => 'catalog/controller/common/header/before',
                'action' => 'extension/dc_minimal/module/dc_minimal|beforeHeader'
            ],
            [
                'code' => 'dc_minimal_account_language',
                'trigger' => 'catalog/controller/account/*/before',
                'action' => 'extension/dc_minimal/module/dc_minimal|beforeAccount'
            ],
            [
                'code' => 'dc_minimal_checkout_language',
                'trigger' => 'catalog/controller/checkout/*/before',
                'action' => 'extension/dc_minimal/module/dc_minimal|beforeCheckout'
            ],
            [
                'code' => 'dc_minimal_total_language',
                'trigger' => 'catalog/model/extension/opencart/total/*/getTotal/before',
                'action' => 'extension/dc_minimal/module/dc_minimal|beforeTotal'
            ],
            [
                'code' => 'dc_minimal_view_language',
                'trigger' => 'view/*/before',
                'action' => 'extension/dc_minimal/module/dc_minimal|beforeView'
            ]
        ];

        foreach ($events as $e) {
            $event_info = $this->model_setting_event->getEventByCode($e['code']);
            if (!$event_info) {
                $this->model_setting_event->addEvent([
                    'code'        => $e['code'],
                    'description' => 'PNJ Mega Menu Cache Clear',
                    'trigger'     => $e['trigger'],
                    'action'      => $e['action'],
                    'status'      => true,
                    'sort_order'  => 0
                ]);
            }
        }
    }

    public function clearCache(): void {
        $this->cache->delete('dc_minimal.mega_menu');
    }
}
