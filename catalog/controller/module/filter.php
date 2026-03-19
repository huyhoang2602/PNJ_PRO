<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class Filter extends \Opencart\System\Engine\Controller {

    public function beforeGetProducts(string &$route, array &$args): void {
        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('module_dc_minimal_filter');
        $status = (int)($settings['module_dc_minimal_filter_status'] ?? 0);

        if (!$status) {
            return;
        }

        $active = [
            'price_min'        => $this->request->get['price_min'] ?? 0,
            'price_max'        => $this->request->get['price_max'] ?? 0,
            'manufacturer_ids' => isset($this->request->get['manufacturer_id']) ? explode(',', (string)$this->request->get['manufacturer_id']) : [],
            'category_ids'     => isset($this->request->get['sub_cat']) ? explode(',', (string)$this->request->get['sub_cat']) : [],
            'attr'             => $this->request->get['attr'] ?? [],
            'opt'              => isset($this->request->get['opt']) ? explode(',', (string)$this->request->get['opt']) : [],
            'filter_ids'       => isset($this->request->get['filter']) ? explode(',', (string)$this->request->get['filter']) : [],
            'stock'            => $this->request->get['stock'] ?? '',
        ];

        if (
            !$active['price_min'] &&
            !$active['price_max'] &&
            empty($active['manufacturer_ids']) &&
            empty($active['category_ids']) &&
            empty($active['attr']) &&
            empty($active['opt']) &&
            empty($active['filter_ids']) &&
            !$active['stock']
        ) {
            return;
        }

        $this->load->model('extension/dc_minimal/module/filter');
        $query_parts = $this->model_extension_dc_minimal_module_filter->buildFilterCondition($active);

        $this->log->write("PNJ FILTER ACTIVE: " . json_encode($active));
        $this->log->write("PNJ FILTER PARTS: " . json_encode($query_parts));

        $args[0]['filter_pnj_joins'] = $query_parts['joins'];
        $args[0]['filter_pnj_where'] = $query_parts['where'];
    }
}