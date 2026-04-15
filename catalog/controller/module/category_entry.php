<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class CategoryEntry extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/dc_minimal/module/category_entry');

		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$data['categories'] = [];

		if (!empty($setting['category_items'])) {
			$category_items = $setting['category_items'];

			// Sort by sort_order
			usort($category_items, function($a, $b) {
                $order_a = isset($a['sort_order']) ? (int)$a['sort_order'] : 0;
                $order_b = isset($b['sort_order']) ? (int)$b['sort_order'] : 0;
				return $order_a - $order_b;
			});

			foreach ($category_items as $item) {
				$category_info = $this->model_catalog_category->getCategory($item['category_id']);

				if ($category_info) {
					$image = !empty($item['custom_image']) ? $item['custom_image'] : $category_info['image'];
					
					if ($image) {
						$thumb = $this->model_tool_image->resize($image, 300, 300);
					} else {
						$thumb = $this->model_tool_image->resize('placeholder.png', 300, 300);
					}

					$language_id = $this->config->get('config_language_id');
					$title = !empty($item['custom_title'][$language_id]) ? $item['custom_title'][$language_id] : $category_info['name'];

					$data['categories'][] = [
						'category_id' => $category_info['category_id'],
						'title'       => $title,
						'thumb'       => $thumb,
						'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category_info['category_id'])
					];
				}
			}
		}

		return $this->load->view('extension/dc_minimal/module/category_entry', $data);
	}
}
