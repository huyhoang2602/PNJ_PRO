<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Payment;

class DcVnpay extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dc_minimal/payment/dc_vnpay', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/dc_minimal/payment/dc_vnpay.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['payment_dc_vnpay_tmn_code'] = $this->config->get('payment_dc_vnpay_tmn_code');
		$data['payment_dc_vnpay_hash_secret'] = $this->config->get('payment_dc_vnpay_hash_secret');
		$data['payment_dc_vnpay_url'] = $this->config->get('payment_dc_vnpay_url');
        $data['payment_dc_vnpay_status'] = $this->config->get('payment_dc_vnpay_status');
		$data['payment_dc_vnpay_sort_order'] = $this->config->get('payment_dc_vnpay_sort_order');
        $data['payment_dc_vnpay_order_status_id'] = $this->config->get('payment_dc_vnpay_order_status_id');

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/payment/dc_vnpay', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/payment/dc_vnpay')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('payment_dc_vnpay', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/dc_minimal/payment/dc_vnpay')) {
			$this->load->model('user/user_group');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dc_minimal/payment/dc_vnpay');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dc_minimal/payment/dc_vnpay');
		}
	}
}
