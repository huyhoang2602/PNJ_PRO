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

		$data['save'] = $this->url->link('extension/dc_minimal/theme/dc_minimal|save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme');

		$fields = [
			'theme_dc_minimal_phone',
			'theme_dc_minimal_email',
			'theme_dc_minimal_banner',
			'theme_dc_minimal_header_bg',
			'theme_dc_minimal_footer_bg',
			'theme_dc_minimal_primary_color',
			'theme_dc_minimal_secondary_color',
			'theme_dc_minimal_status'
		];

		$this->load->model('setting/setting');

		$setting_info = $this->model_setting_setting->getSetting('theme_dc_minimal', $store_id);

		foreach ($fields as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (isset($setting_info[$field])) {
				$data[$field] = $setting_info[$field];
			} else {
				$data[$field] = '';
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/theme/dc_minimal', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/theme/dc_minimal');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/theme/dc_minimal')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('theme_dc_minimal', $this->request->post, $store_id);

            // SYNC ACTIVE THEME:
            if (isset($this->request->post['theme_dc_minimal_status']) && $this->request->post['theme_dc_minimal_status']) {
                $this->model_setting_setting->editValue('config', 'config_theme', 'dc_minimal', $store_id);
            } else {
                $this->model_setting_setting->editValue('config', 'config_theme', 'basic', $store_id);
            }

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/marketplace/extension')) {
			$startup_data = [
				'code'        => 'dc_minimal',
				'description' => 'Design Cart Minimal 4 Startup',
				'action'      => 'catalog/extension/dc_minimal/startup/dc_minimal',
				'status'      => 1,
				'sort_order'  => 0
			];

			$this->load->model('setting/startup');
			$this->model_setting_startup->addStartup($startup_data);
		}
	}

	public function uninstall(): void {
		if ($this->user->hasPermission('modify', 'extension/marketplace/extension')) {
			$this->load->model('setting/startup');
			$this->model_setting_startup->deleteStartupByCode('dc_minimal');
		}
	}
}