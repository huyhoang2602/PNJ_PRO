<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class Brand extends \Opencart\System\Engine\Controller {
    public function index(): string {
        // Tải ngôn ngữ (Sử dụng key i18n JSON bạn đã thiết lập)
        $this->load->language('extension/dc_minimal/module/brand');

        $this->load->model('catalog/manufacturer');
        $this->load->model('tool/image');

        $data['manufacturers'] = [];

        // Lấy tất cả thương hiệu từ hệ thống
        $results = $this->model_catalog_manufacturer->getManufacturers();

        foreach ($results as $result) {
            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], 150, 100);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 150, 100);
            }

            $data['manufacturers'][] = [
                'name' => $result['name'],
                'image' => $image,
                'href' => $this->url->link('product/manufacturer|info', 'manufacturer_id=' . $result['manufacturer_id'])
            ];
        }

        // Kiểm tra xem file twig đã tồn tại chưa trước khi render
        return $this->load->view('extension/dc_minimal/module/brand', $data);
    }
}