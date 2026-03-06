<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Component;

use Opencart\System\Engine\Controller;

class Category extends Controller {

	/**
	 * Index method
	 *
	 * @return string
	 */
	public function index(): string { 
		// Load required models
		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		// Get all parent categories
		$categories = $this->model_catalog_category->getCategories(0);

		$data['categories'] = [];

		foreach ($categories as $category) {
			$image = '';

			if ($category['image']) {
				$image = $this->model_tool_image->resize($category['image'], 200, 200);
			}

			$data['categories'][] = [
				'name' => $category['name'],
				'image' => $image,
				'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
			];
		}

		return $this->load->view('extension/dc_minimal/component/category', $data);
	}
}