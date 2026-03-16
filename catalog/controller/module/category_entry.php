<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class CategoryEntry extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
        $data['categories'] = [];

        if (!empty($setting['categories'])) {
            $this->load->model('tool/image');
            foreach ($setting['categories'] as $category) {
                if (is_file(DIR_IMAGE . html_entity_decode($category['image'], ENT_QUOTES, 'UTF-8'))) {
                    $image = $this->model_tool_image->resize(html_entity_decode($category['image'], ENT_QUOTES, 'UTF-8'), 200, 200);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 200, 200);
                }

                $data['categories'][] = [
                    'title' => $category['title'],
                    'link'  => $category['link'],
                    'image' => $image
                ];
            }
        }

		return $this->load->view('extension/dc_minimal/module/category_entry', $data);
	}
}
