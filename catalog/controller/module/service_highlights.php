<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class ServiceHighlights extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
        $this->load->language('extension/dc_minimal/module/service_highlights');

        $data['services'] = [];

        if (!empty($setting['services'])) {
            $this->load->model('tool/image');
            foreach ($setting['services'] as $service) {
                if (is_file(DIR_IMAGE . html_entity_decode($service['image'], ENT_QUOTES, 'UTF-8'))) {
                    $image = $this->model_tool_image->resize(html_entity_decode($service['image'], ENT_QUOTES, 'UTF-8'), 60, 60);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 60, 60);
                }

                $data['services'][] = [
                    'title'       => $service['title'],
                    'description' => $service['description'],
                    'tooltip'     => isset($service['tooltip']) ? html_entity_decode($service['tooltip'], ENT_QUOTES, 'UTF-8') : '',
                    'thumb'       => $image
                ];
            }
        }

		return $this->load->view('extension/dc_minimal/module/service_highlights', $data);
	}
}
