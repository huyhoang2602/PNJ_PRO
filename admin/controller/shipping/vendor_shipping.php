<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Shipping;

class VendorShipping extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/dc_minimal/shipping/vendor_shipping');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dc_minimal/shipping/vendor_shipping', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/dc_minimal/shipping/vendor_shipping.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping');

		$fields = [
			'shipping_vendor_shipping_api_url',
			'shipping_vendor_shipping_api_quote',
			'shipping_vendor_shipping_api_create',
			'shipping_vendor_shipping_api_track',
			'shipping_vendor_shipping_api_token',
			'shipping_vendor_shipping_shop_id',
			'shipping_vendor_shipping_timeout',
			'shipping_vendor_shipping_free_threshold',
			'shipping_vendor_shipping_fallback_cost',
			'shipping_vendor_shipping_tax_class_id',
			'shipping_vendor_shipping_geo_zone_id',
			'shipping_vendor_shipping_status',
			'shipping_vendor_shipping_sort_order',
			'shipping_vendor_shipping_debug',
			'shipping_vendor_shipping_environment'
		];

		foreach ($fields as $field) {
			$data[$field] = $this->config->get($field);
		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dc_minimal/shipping/vendor_shipping', $data));
	}

	public function save(): void {
		$this->load->language('extension/dc_minimal/shipping/vendor_shipping');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dc_minimal/shipping/vendor_shipping')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['shipping_vendor_shipping_status']) {
			if (!$this->request->post['shipping_vendor_shipping_api_url']) {
				$json['error']['api_url'] = $this->language->get('error_api_url');
			}
			if (!$this->request->post['shipping_vendor_shipping_api_quote']) {
				$json['error']['api_quote'] = $this->language->get('error_api_quote');
			}
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('shipping_vendor_shipping', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "vendor_shipping_shipment` (
		  `vendor_shipment_id` INT(11) NOT NULL AUTO_INCREMENT,
		  `order_id` INT(11) NOT NULL,
		  `provider_code` VARCHAR(64) NOT NULL,
		  `service_code` VARCHAR(64) NOT NULL,
		  `tracking_code` VARCHAR(64) DEFAULT NULL,
		  `shipping_fee` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
		  `shipment_status` VARCHAR(32) DEFAULT NULL,
		  `request_payload` TEXT DEFAULT NULL,
		  `response_payload?` TEXT DEFAULT NULL,
		  `date_added` DATETIME NOT NULL,
		  `date_modified` DATETIME NOT NULL,
		  PRIMARY KEY (`vendor_shipment_id`),
		  INDEX `order_id` (`order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		// Add permissions
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dc_minimal/shipping/vendor_shipping');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dc_minimal/shipping/vendor_shipping');
	}

	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "vendor_shipping_shipment`;");
	}
}
