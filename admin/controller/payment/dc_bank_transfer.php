<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Payment;

class DcBankTransfer extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dc_minimal/payment/dc_bank_transfer');

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
			'href' => $this->url->link('extension/dc_minimal/payment/dc_bank_transfer', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/dc_minimal/payment/dc_bank_transfer.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['payment_dc_bank_transfer_bank'] = [];
		foreach ($data['languages'] as $language) {
			$data['payment_dc_bank_transfer_bank'][$language['language_id']] = $this->config->get('payment_dc_bank_transfer_bank_' . $language['language_id']);
		}

		$data['payment_dc_bank_transfer_bank_name'] = $this->config->get('payment_dc_bank_transfer_bank_name');
		$data['payment_dc_bank_transfer_bank_bin'] = $this->config->get('payment_dc_bank_transfer_bank_bin');
		$data['payment_dc_bank_transfer_account_name'] = $this->config->get('payment_dc_bank_transfer_account_name');
		$data['payment_dc_bank_transfer_account_number'] = $this->config->get('payment_dc_bank_transfer_account_number');
		$data['payment_dc_bank_transfer_branch'] = $this->config->get('payment_dc_bank_transfer_branch');
		$data['payment_dc_bank_transfer_transfer_prefix'] = $this->config->get('payment_dc_bank_transfer_transfer_prefix');
		
		$data['payment_dc_bank_transfer_order_status_id'] = $this->config->get('payment_dc_bank_transfer_order_status_id');

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['payment_dc_bank_transfer_geo_zone_id'] = $this->config->get('payment_dc_bank_transfer_geo_zone_id');

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['payment_dc_bank_transfer_status'] = $this->config->get('payment_dc_bank_transfer_status');
		$data['payment_dc_bank_transfer_sort_order'] = $this->config->get('payment_dc_bank_transfer_sort_order');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/payment/dc_bank_transfer', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/payment/dc_bank_transfer');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/payment/dc_bank_transfer')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('payment_dc_bank_transfer', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function install(): void {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dc_minimal/payment/dc_bank_transfer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dc_minimal/payment/dc_bank_transfer');
    }
}
