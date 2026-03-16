<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class HeroBanner extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
        $data['banners'] = [];

        if (!empty($setting['banners'])) {
            $this->load->model('tool/image');
            foreach ($setting['banners'] as $banner) {
                if (is_file(DIR_IMAGE . html_entity_decode($banner['image'], ENT_QUOTES, 'UTF-8'))) {
                    // Kích thước banner lớn cho Desktop, có thể custom thêm
                    $image = $this->model_tool_image->resize(html_entity_decode($banner['image'], ENT_QUOTES, 'UTF-8'), 1920, 600);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 1920, 600);
                }

                $data['banners'][] = [
                    'title' => $banner['title'],
                    'link'  => $banner['link'],
                    'image' => $image
                ];
            }
        }

		return $this->load->view('extension/dc_minimal/module/hero_banner', $data);
	}
}
