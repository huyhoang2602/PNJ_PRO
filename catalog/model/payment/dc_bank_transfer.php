<?php
namespace Opencart\Catalog\Model\Extension\DcMinimal\Payment;

class DcBankTransfer extends \Opencart\System\Engine\Model {
	public function getMethods(array $address = []): array {
		$this->load->language('extension/dc_minimal/payment/dc_bank_transfer');

		if ($this->config->get('payment_dc_bank_transfer_geo_zone_id')) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_dc_bank_transfer_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

			if ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		} else {
			$status = true;
		}

		$method_data = [];

		if ($status) {
			$option_data['dc_bank_transfer'] = [
				'code' => 'dc_bank_transfer.dc_bank_transfer',
				'name' => $this->language->get('heading_title')
			];

			$method_data = [
				'code'       => 'dc_bank_transfer',
				'name'       => $this->language->get('heading_title'),
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_dc_bank_transfer_sort_order')
			];
		}

		return $method_data;
	}
}
