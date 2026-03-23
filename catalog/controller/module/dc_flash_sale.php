<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DcFlashSale extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/dc_minimal/module/dc_flash_sale');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['products'] = [];

        if (!empty($setting['title'])) {
            $data['title'] = $setting['title'];
        } else {
            $data['title'] = $this->language->get('heading_title');
        }

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

        $data['end_time'] = $setting['end_time'];

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
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => oc_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, (int)$this->config->get('config_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
						'rating'      => $product_info['rating'],
						'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
					];

                    if (isset($product_info['dc_discount_percent'])) {
                        $product_data['dc_discount_percent'] = $product_info['dc_discount_percent'];
                    }

					$data['products'][] = $product_data;
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/dc_minimal/module/dc_flash_sale', $data);
		} else {
			return '';
		}
	}
}
