<?php
namespace Opencart\Catalog\Model\Extension\DcMinimal\Shipping;

require_once(DIR_SYSTEM . 'library/vendorshipping/Client.php');

class VendorShipping extends \Opencart\System\Engine\Model {
	public function getQuote(array $address): array {
		$this->load->language('extension/dc_minimal/shipping/vendor_shipping');

		if (!$this->config->get('shipping_vendor_shipping_status')) {
			return [];
		}

		$this->load->model('localisation/geo_zone');
		$results = $this->model_localisation_geo_zone->getGeoZone((int)$this->config->get('shipping_vendor_shipping_geo_zone_id'), (int)$address['country_id'], (int)$address['zone_id']);

		if ($this->config->get('shipping_vendor_shipping_geo_zone_id') && !$results) {
			return [];
		}

		$sub_total = $this->cart->getSubTotal();
		$weight = $this->cart->getWeight();
		$free_threshold = (float)$this->config->get('shipping_vendor_shipping_free_threshold');

		$quote_data = [];

		if ($free_threshold > 0 && $sub_total >= $free_threshold) {
			$quote_data['vendor_shipping'] = [
				'code'         => 'vendor_shipping.vendor_shipping',
				'name'         => $this->language->get('text_free'),
				'cost'         => 0.00,
				'tax_class_id' => 0,
				'text'         => $this->currency->format(0, $this->session->data['currency'])
			];
		} else {
			$client = new \VendorShipping\Client($this->registry);
			
			$cart_data = [
				'subtotal' => $sub_total,
				'weight'   => $weight,
				'currency' => $this->session->data['currency'],
				'items'    => []
			];

			foreach ($this->cart->getProducts() as $product) {
				$cart_data['items'][] = [
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'quantity'   => $product['quantity'],
					'price'      => $product['price'],
					'weight'     => $product['weight']
				];
			}

			$api_response = $client->quote($address, $cart_data);

			if ($api_response && isset($api_response['success']) && $api_response['success']) {
				$cost = (float)$api_response['cost'];
				$name = $api_response['service_name'];
				if (!empty($api_response['eta_text'])) {
					$name .= ' (' . sprintf($this->language->get('text_eta'), $api_response['eta_text']) . ')';
				}

				$quote_data['vendor_shipping'] = [
					'code'         => 'vendor_shipping.vendor_shipping',
					'name'         => $name,
					'cost'         => $cost,
					'tax_class_id' => (int)$this->config->get('shipping_vendor_shipping_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($cost, (int)$this->config->get('shipping_vendor_shipping_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
				];
			} else {
				// Fallback
				$fallback_cost = $this->config->get('shipping_vendor_shipping_fallback_cost');
				if ($fallback_cost !== '' && $fallback_cost !== null) {
					$cost = (float)$fallback_cost;
					$quote_data['vendor_shipping'] = [
						'code'         => 'vendor_shipping.vendor_shipping',
						'name'         => $this->language->get('text_description') . ' (Standard)',
						'cost'         => $cost,
						'tax_class_id' => (int)$this->config->get('shipping_vendor_shipping_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, (int)$this->config->get('shipping_vendor_shipping_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
					];
				}
			}
		}

		if (!$quote_data) {
			return [];
		}

		return [
			'code'       => 'vendor_shipping',
			'name'       => $this->language->get('heading_title'),
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('shipping_vendor_shipping_sort_order'),
			'error'      => false
		];
	}
}
