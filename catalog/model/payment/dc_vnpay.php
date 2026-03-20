<?php
namespace Opencart\Catalog\Model\Extension\DcMinimal\Payment;

class DcVnpay extends \Opencart\System\Engine\Model {
	public function getMethods(array $address = []): array {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$method_data = [];

		if ($this->config->get('payment_dc_vnpay_status')) {
			$method_data = [
				'code'       => 'dc_vnpay',
				'name'       => $this->language->get('heading_title'),
				'option'     => [
					'dc_vnpay' => [
						'code' => 'dc_vnpay.dc_vnpay',
						'name' => $this->language->get('heading_title')
					]
				],
				'sort_order' => $this->config->get('payment_dc_vnpay_sort_order')
			];
		}

		return $method_data;
	}
}
