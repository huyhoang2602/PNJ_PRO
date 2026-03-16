<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class BrandShowcase extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
        $this->load->language('extension/dc_minimal/module/brand_showcase');

        $data['brands'] = [];

        if (!empty($setting['brands'])) {
            $this->load->model('tool/image');
            foreach ($setting['brands'] as $brand) {
                if (is_file(DIR_IMAGE . html_entity_decode($brand['image'], ENT_QUOTES, 'UTF-8'))) {
                    $image = $this->model_tool_image->resize(html_entity_decode($brand['image'], ENT_QUOTES, 'UTF-8'), 200, 100);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 200, 100);
                }

                $data['brands'][] = [
                    'name'  => $brand['title'],
                    'link'  => $brand['link'],
                    'thumb' => $image
                ];
            }
        }

        $data['heading_title'] = $setting['name'] ?? $this->language->get('heading_title');

		return $this->load->view('extension/dc_minimal/module/brand_showcase', $data);
	}
}
