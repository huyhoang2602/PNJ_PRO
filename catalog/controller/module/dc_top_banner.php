<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DcTopBanner extends \Opencart\System\Engine\Controller {
	public function index(array $setting = []): string {
		$module_id = (int)($setting['module_id'] ?? $this->request->get['module_id'] ?? 0);

		$this->load->model('setting/module');

		$setting = $this->model_setting_module->getModule($module_id);

		if ($setting) {
			$this->log->write('DC Top Banner Module: Setting found. Status: ' . (isset($setting['status']) ? $setting['status'] : 'N/A'));
		} else {
			$this->log->write('DC Top Banner Module: Setting NOT found for ID: ' . $module_id);
		}

		if ($setting && isset($setting['status']) && $setting['status']) {
			$data['title'] = html_entity_decode($setting['title'], ENT_QUOTES, 'UTF-8');
			$data['link'] = $setting['link'];
			$data['button'] = html_entity_decode($setting['button'], ENT_QUOTES, 'UTF-8');

			if (is_file(DIR_IMAGE . html_entity_decode($setting['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['image'] = HTTP_SERVER . 'image/' . html_entity_decode($setting['image'], ENT_QUOTES, 'UTF-8');
			} else {
				$data['image'] = '';
			}

			return $this->load->view('extension/dc_minimal/module/dc_top_banner', $data);
		}

		return '';
	}
}
