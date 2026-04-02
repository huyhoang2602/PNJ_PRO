<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

class Product extends \Opencart\System\Engine\Controller {
	public function autocomplete(): void {
		$this->log->write('PNJ DEBUG: DC Minimal Autocomplete route called!');
		$this->load->language('catalog/product');

		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$filter_name = (string)$this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 10;
		}

		$filter_data = [
			'filter_name' => $filter_name,
			'start'       => ($page - 1) * $limit,
			'limit'       => $limit
		];

		$this->load->model('catalog/product');

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			$json[] = [
				'product_id' => $result['product_id'],
				'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				'model'      => $result['model'],
				'price'      => $result['price']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
