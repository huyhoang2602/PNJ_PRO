<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class ServiceHighlights extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
        $this->load->language('extension/dc_minimal/module/service_highlights');

        $data['services'] = [];

        if (!empty($setting['services'])) {
            $this->load->model('tool/image');
            $language_id = (int)$this->config->get('config_language_id');
            foreach ($setting['services'] as $service) {
                if (is_file(DIR_IMAGE . html_entity_decode($service['image'], ENT_QUOTES, 'UTF-8'))) {
                    $image = $this->model_tool_image->resize(html_entity_decode($service['image'], ENT_QUOTES, 'UTF-8'), 60, 60);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 60, 60);
                }

                $title = !empty($service['title'][$language_id]) ? $service['title'][$language_id] : (is_array($service['title']) ? reset($service['title']) : $service['title']);
                $description = !empty($service['description'][$language_id]) ? $service['description'][$language_id] : (is_array($service['description']) ? reset($service['description']) : $service['description']);
                $tooltip = !empty($service['tooltip'][$language_id]) ? html_entity_decode($service['tooltip'][$language_id], ENT_QUOTES, 'UTF-8') : (is_array($service['tooltip']) ? html_entity_decode(reset($service['tooltip']), ENT_QUOTES, 'UTF-8') : $service['tooltip']);

                $data['services'][] = [
                    'title'       => $title,
                    'description' => $description,
                    'tooltip'     => $tooltip,
                    'thumb'       => $image
                ];
            }
        }

		return $this->load->view('extension/dc_minimal/module/service_highlights', $data);
	}
}
