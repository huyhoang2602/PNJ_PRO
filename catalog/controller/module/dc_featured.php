<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DcFeatured extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/dc_minimal/module/dc_featured');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['products'] = [];

		if (!$setting['limit'] || $setting['limit'] <= 4) {
			$setting['limit'] = 20;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if (is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
						$image = $this->model_tool_image->resize(html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					$product_data = [
						'product_id'        => $product_info['product_id'],
						'thumb'             => $image,
						'name'              => $product_info['name'],
                        'manufacturer'      => $product_info['manufacturer'] ?? '',
                        'manufacturer_href' => $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . ($product_info['manufacturer_id'] ?? 0)),
						'description'       => \mb_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
						'price'             => $price,
						'special'           => $special,
						'tax'               => $tax,
						'minimum'           => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
						'rating'            => $product_info['rating'],
						'href'              => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
					];

                    // Event custom discount from modifiers
                    if (isset($product_info['dc_discount_percent'])) {
                        $product_data['dc_discount_percent'] = $product_info['dc_discount_percent'];
                    }

					$data['products'][] = $product_data;
				}
			}
		}

		if ($data['products']) {
            echo "<!-- DC FEATURED LOADED: " . count($data['products']) . " PRODUCTS -->";
			return $this->load->view('extension/dc_minimal/module/dc_featured', $data);

		} else {
			return '';
		}
	}
}
