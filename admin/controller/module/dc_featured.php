<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

class DcFeatured extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dc_minimal/module/dc_featured');

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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/dc_minimal/module/dc_featured', 'user_token=' . $this->session->data['user_token'])
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/dc_minimal/module/dc_featured', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
			];
		}

		if (!isset($this->request->get['module_id'])) {
			$data['save'] = $this->url->link('extension/dc_minimal/module/dc_featured.save', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['save'] = $this->url->link('extension/dc_minimal/module/dc_featured.save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
		}

		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		if (isset($this->request->get['module_id'])) {
			$this->load->model('setting/module');
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		$this->load->model('catalog/product');
		$data['products'] = [];

		if (!empty($module_info['product'])) {
			foreach ($module_info['product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$data['products'][] = [
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					];
				}
			}
		}

		if (isset($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 4;
		}

		if (isset($module_info['width'])) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($module_info['height'])) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
		}

		if (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/module/dc_featured', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/module/dc_featured');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/module/dc_featured')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!oc_validate_length($this->request->post['name'], 3, 64)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$json['error']['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$json['error']['height'] = $this->language->get('error_height');
		}

		if (!$json) {
			$this->load->model('setting/module');

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('dc_minimal.dc_featured', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
