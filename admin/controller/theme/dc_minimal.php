<?php
    namespace Opencart\Admin\Controller\Extension\DcMinimal\Theme;
    class DcMinimal extends \Opencart\System\Engine\Controller {
        public function index(): void {
            $this->load->language('extension/dc_minimal/theme/dc_minimal');

            $this->document->setTitle($this->language->get('heading_title'));

            if (isset($this->request->get['store_id'])) {
                $store_id = (int)$this->request->get['store_id'];
            } else {
                $store_id = 0;
            }

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/dc_minimal/theme/dc_minimal', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
            ];

            $data['save'] = $this->url->link('extension/dc_minimal/theme/dc_minimal.save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
            $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme');

            $fields = [
               
                'theme_dc_minimal_phone',
                'theme_dc_minimal_email',
                'theme_dc_minimal_banner',
                'theme_dc_minimal_header_bg',
                'theme_dc_minimal_header_color',
                'theme_dc_minimal_title_color',
                'theme_dc_minimal_primary_bg',
                'theme_dc_minimal_primary',
                'theme_dc_minimal_secondary_bg',
                'theme_dc_minimal_secondary',
                'theme_dc_minimal_status',
                'theme_dc_minimal_featured',
                'theme_dc_minimal_latest',
                'theme_dc_minimal_special',
                'theme_dc_minimal_bestseller',
               
                'theme_dc_minimal_phone_label',
                'theme_dc_minimal_email_label',
                'theme_dc_minimal_cart_label',
                'theme_dc_minimal_search_label',
                'theme_dc_minimal_free_shipping',
                'theme_dc_minimal_header_title',
                'theme_dc_minimal_header_subtitle',
            ];

            foreach ($fields as $field) {
                $data[$field] = $this->config->get($field);
            }

            $this->load->model('tool/image');
            if (!empty($data['theme_dc_minimal_banner']) && is_file(DIR_IMAGE . $data['theme_dc_minimal_banner'])) {
                $data['banner_thumb'] = $this->model_tool_image->resize($data['theme_dc_minimal_banner'], 100, 100);
            } else {
                $data['banner_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
            }
            $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

            $this->load->model('localisation/language');
            $data['languages'] = $this->model_localisation_language->getLanguages();

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('extension/dc_minimal/theme/dc_minimal', $data));
        }

        public function save(): void {
            $this->load->language('extension/dc_minimal/theme/dc_minimal');

            if (isset($this->request->get['store_id'])) {
                $store_id = (int)$this->request->get['store_id'];
            } else {
                $store_id = 0;
            }

            $json = [];

            if (!$this->user->hasPermission('modify', 'extension/dc_minimal/theme/dc_minimal')) {
                $json['error'] = $this->language->get('error_permission');
            }

            if (!$json) {
                $this->load->model('setting/setting');

                $this->model_setting_setting->editSetting('theme_dc_minimal', $this->request->post, $store_id);

                $json['success'] = $this->language->get('text_success');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }

        public function install(): void {
            if ($this->user->hasPermission('modify', 'extension/theme')) {
      
                $startup_data = [
                    'code'        => 'dc_minimal',
                    'description' => 'Design Cart Minimal 4',
                    'action'      => 'catalog/extension/dc_minimal/startup/dc_minimal',
                    'status'      => 1,
                    'sort_order'  => 0
                ];

                $this->load->model('setting/startup');

                $this->model_setting_startup->addStartup($startup_data);
            }
        }

        public function uninstall(): void {
            if ($this->user->hasPermission('modify', 'extension/theme')) {
                $this->load->model('setting/startup');

                $this->model_setting_startup->deleteStartupByCode('dc_minimal');
            }
        }
}