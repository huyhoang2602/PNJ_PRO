<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DcCollection extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/dc_minimal/module/dc_collection');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

        $language_id = (int)$this->config->get('config_language_id');
		$data['title'] = !empty($setting['title'][$language_id]) ? $setting['title'][$language_id] : ($setting['title'] ?? 'Bộ sưu tập mới');
        
        $data['uid'] = rand(1000, 99999);
        $data['collections'] = [];

        if (!empty($setting['collections'])) {
            foreach ($setting['collections'] as $key => $collection) {
                $image = '';
                if (is_file(DIR_IMAGE . html_entity_decode($collection['image'], ENT_QUOTES, 'UTF-8'))) {
                    $image = HTTP_SERVER . 'image/' . html_entity_decode($collection['image'], ENT_QUOTES, 'UTF-8');
                }

                $products = [];
                if (!empty($collection['product'])) {
                    foreach ($collection['product'] as $product_id) {
                        $product_info = $this->model_catalog_product->getProduct($product_id);

                        if ($product_info) {
                            if (is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
                                $product_image = $this->model_tool_image->resize(html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'), 300, 300);
                            } else {
                                $product_image = $this->model_tool_image->resize('placeholder.png', 300, 300);
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

                            $products[] = [
                                'product_id'  => $product_info['product_id'],
                                'thumb'       => $product_image,
                                'name'        => $product_info['name'],
                                'description' => oc_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
                                'price'       => $price,
                                'special'     => $special,
                                'tax'         => ($this->config->get('config_tax') ? $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']) : false),
                                'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
                            ];
                        }
                    }
                }

                $language_id = (int)$this->config->get('config_language_id');
                $collection_title = !empty($collection['title'][$language_id]) ? $collection['title'][$language_id] : (is_array($collection['title']) ? reset($collection['title']) : $collection['title']);

                $data['collections'][] = [
                    'id'       => $key,
                    'title'    => $collection_title,
                    'image'    => $image,
                    'link'     => $collection['link'] ?? '',
                    'products' => $products
                ];
            }
        }

        if (empty($data['collections'])) {
            return '';
        }

		return $this->load->view('extension/dc_minimal/module/dc_collection', $data);
	}
}
