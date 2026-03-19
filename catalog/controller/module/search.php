<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class Search extends \Opencart\System\Engine\Controller {
    public function autocomplete(): void {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            $filter_data = [
                'filter_search' => $this->request->get['filter_name'],
                'start'         => 0,
                'limit'         => 5
            ];

            $results = $this->model_catalog_product->getProducts($filter_data);

            foreach ($results as $result) {
                if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
                    $image = $this->model_tool_image->resize($result['image'], 200, 200);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 200, 200);
                }

                $json[] = [
                    'product_id' => $result['product_id'],
                    'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'thumb'      => $image,
                    'href'       => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'], true)
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
