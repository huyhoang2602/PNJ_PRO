<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

class Brand extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dc_minimal/module/brand');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dc_minimal/module/brand', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/dc_minimal/module/brand.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$data['module_brand_status'] = $this->config->get('module_brand_status');
		
		if ($this->config->get('module_brand_title')) {
			$data['module_brand_title'] = $this->config->get('module_brand_title');
		} else {
			$data['module_brand_title'] = [];
		}

		if ($this->config->get('module_brand_brands')) {
			$data['brands'] = $this->config->get('module_brand_brands');
		} else {
			$data['brands'] = [];
		}

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Get Manufacturers
		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

		foreach ($data['brands'] as &$brand) {
			// Logo Overlay
			if (isset($brand['logo']) && is_file(DIR_IMAGE . html_entity_decode($brand['logo'], ENT_QUOTES, 'UTF-8'))) {
				$brand['logo_thumb'] = $this->model_tool_image->resize(html_entity_decode($brand['logo'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$brand['logo_thumb'] = $data['placeholder'];
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/module/brand', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/module/brand');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/module/brand')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_brand', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}