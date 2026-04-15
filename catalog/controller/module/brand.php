<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class Brand extends \Opencart\System\Engine\Controller {
    public function index(array $setting = []): string {
        if (!$setting) {
            return '';
        }

        $language = $this->getCurrentLanguage();
        $language_data = $this->load->language('extension/dc_minimal/module/brand', '', $language['code']);
        $this->load->model('tool/image');
        $this->load->model('catalog/manufacturer');

        $titles = $setting['title'] ?? $setting['module_brand_title'] ?? $this->config->get('module_brand_title');
        $heading_title = $this->getLocalizedValue($titles, $language['id'], $language['code'], false);

        if ($heading_title === '') {
            $heading_title = $language_data['heading_title'] ?? $this->language->get('heading_title');
        }

        if ($heading_title === '' || $heading_title === 'heading_title') {
            $heading_title = $this->getLocalizedValue($titles, $language['id'], $language['code'], true);
        }

        $data['heading_title'] = $heading_title;

        $data['manufacturers'] = [];

        $brands = $setting['module_brand_brands'] ?? $this->config->get('module_brand_brands');

        if (!empty($brands)) {
            // Sort brands by sort_order
            usort($brands, function($a, $b) {
                return (int)$a['sort_order'] - (int)$b['sort_order'];
            });

            foreach ($brands as $brand) {
                if (empty($brand['manufacturer_id'])) continue;

                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($brand['manufacturer_id']);

                if ($manufacturer_info) {
                    // Backdrop Image (from Manufacturer)
                    if ($manufacturer_info['image'] && is_file(DIR_IMAGE . html_entity_decode($manufacturer_info['image'], ENT_QUOTES, 'UTF-8'))) {
                        $image = $this->model_tool_image->resize(html_entity_decode($manufacturer_info['image'], ENT_QUOTES, 'UTF-8'), 400, 480);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', 400, 480);
                    }

                    // Logo Image (Custom Overlay from Module)
                    if (!empty($brand['logo']) && is_file(DIR_IMAGE . html_entity_decode($brand['logo'], ENT_QUOTES, 'UTF-8'))) {
                        $logo = $this->model_tool_image->resize(html_entity_decode($brand['logo'], ENT_QUOTES, 'UTF-8'), 160, 80);
                    } else {
                        $logo = $this->model_tool_image->resize('no_image.png', 160, 80);
                    }

                    $data['manufacturers'][] = [
                        'name'  => $manufacturer_info['name'],
                        'image' => $image,
                        'logo'  => $logo,
                        'href'  => $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') . '&manufacturer_id=' . $brand['manufacturer_id'])
                    ];
                }
            }
        }

        return $this->load->view('extension/dc_minimal/module/brand', $data);
    }

    private function getCurrentLanguage(): array {
        $code = (string)($this->request->get['language'] ?? $this->config->get('config_language'));
        $language_id = (int)$this->config->get('config_language_id');

        if ($code) {
            $this->load->model('localisation/language');
            $language_info = $this->model_localisation_language->getLanguageByCode($code);

            if ($language_info) {
                $code = (string)$language_info['code'];
                $language_id = (int)$language_info['language_id'];
            }
        }

        return [
            'id'   => $language_id,
            'code' => $code
        ];
    }

    private function getLocalizedValue($values, int $language_id, string $language_code, bool $fallback_any = true): string {
        if (is_array($values)) {
            foreach ([$language_id, (string)$language_id, $language_code] as $key) {
                if (isset($values[$key]) && trim((string)$values[$key]) !== '') {
                    return (string)$values[$key];
                }
            }

            if ($fallback_any) {
                foreach ($values as $value) {
                    if (!is_array($value) && trim((string)$value) !== '') {
                        return (string)$value;
                    }
                }
            }
        } elseif (trim((string)$values) !== '') {
            return (string)$values;
        }

        return '';
    }
}
