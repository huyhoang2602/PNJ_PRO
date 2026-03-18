<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

/**
 * Filter configuration for dc_minimal theme.
 * This is an administrative module used to manage filter settings.
 */
class Filter extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/dc_minimal/module/filter');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->checkEvents();

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/dc_minimal/module/filter', 'user_token=' . $this->session->data['user_token'])
            ]
        ];

        $data['save'] = $this->url->link('extension/dc_minimal/module/filter.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $this->load->model('extension/dc_minimal/module/filter');
        $this->load->model('setting/setting');
        
        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_filter');

        $data['status'] = $settings['module_dc_minimal_filter_status'] ?? 0;
        
        if (!empty($settings['module_dc_minimal_filter_groups'])) {
            $data['filter_groups'] = $settings['module_dc_minimal_filter_groups'];
        } else {
            $data['filter_groups'] = $this->model_extension_dc_minimal_module_filter->getDefaultGroups();
        }

        $data['attributes']       = $this->model_extension_dc_minimal_module_filter->getAttributeList();
        $data['options']          = $this->model_extension_dc_minimal_module_filter->getOptionList();
        $data['filter_groups_oc'] = $this->model_extension_dc_minimal_module_filter->getOcFilterGroups();

        $data['user_token'] = $this->session->data['user_token'];
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dc_minimal/module/filter', $data));
    }

    public function save(): void {
        $this->load->language('extension/dc_minimal/module/filter');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/dc_minimal/module/filter')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');
            $post_data = $this->request->post;

            if (isset($post_data['filter_groups']) && is_array($post_data['filter_groups'])) {
                foreach ($post_data['filter_groups'] as &$group) {
                    $group['enabled']    = isset($group['enabled']) ? 1 : 0;
                    $group['show_count'] = isset($group['show_count']) ? 1 : 0;
                    $group['sort_order'] = (int)($group['sort_order'] ?? 0);
                    $group['source_id']  = (int)($group['source_id'] ?? 0);
                    $group['routes']     = $group['routes'] ?? ['category', 'manufacturer'];
                }
                unset($group);
            }

            $this->model_setting_setting->editSetting('module_dc_minimal_filter', [
                'module_dc_minimal_filter_status' => (int)($post_data['status'] ?? 0),
                'module_dc_minimal_filter_groups' => $post_data['filter_groups'] ?? []
            ]);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function checkEvents(): void {
        $this->load->model('setting/event');
        
        $events = [
            [
                'code' => 'dc_minimal_filter_query',
                'trigger' => 'catalog/model/catalog/product/getProducts/before',
                'action' => 'extension/dc_minimal/module/filter|beforeGetProducts'
            ],
            [
                'code' => 'dc_minimal_filter_total',
                'trigger' => 'catalog/model/catalog/product/getTotalProducts/before',
                'action' => 'extension/dc_minimal/module/filter|beforeGetProducts'
            ]
        ];

        // Clean up redundant view events (now handled in startup/dc_minimal)
        $this->model_setting_event->deleteEventByCode('dc_minimal_filter_category');
        $this->model_setting_event->deleteEventByCode('dc_minimal_filter_manufacturer');

        foreach ($events as $e) {
            $event_info = $this->model_setting_event->getEventByCode($e['code']);
            if (!$event_info) {
                $this->model_setting_event->addEvent([
                    'code'        => $e['code'],
                    'description' => 'PNJ Filter',
                    'trigger'     => $e['trigger'],
                    'action'      => $e['action'],
                    'status'      => true,
                    'sort_order'  => 0
                ]);
            } elseif ($event_info['trigger'] != $e['trigger'] || $event_info['action'] != $e['action']) {
                $this->model_setting_event->deleteEventByCode($e['code']);
                $this->model_setting_event->addEvent([
                    'code'        => $e['code'],
                    'description' => 'PNJ Filter',
                    'trigger'     => $e['trigger'],
                    'action'      => $e['action'],
                    'status'      => true,
                    'sort_order'  => 0
                ]);
            }
        }
    }
}
