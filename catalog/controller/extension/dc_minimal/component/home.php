<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Component;

class Home extends \Opencart\System\Engine\Controller {

    public function index(): string {

        $data['category'] = $this->load->controller(
            'extension/dc_minimal/component/category'
        );

        // Module core OpenCart
        $data['featured'] = $this->load->controller(
            'extension/opencart/module/featured'
        );

        $data['latest'] = $this->load->controller(
            'extension/opencart/module/latest'
        );

        $data['bestseller'] = $this->load->controller(
            'extension/opencart/module/bestseller'
        );

        $data['recently_viewed'] = $this->load->controller(
            'extension/opencart/module/recently_viewed'
        );

        $data['brand'] = $this->load->controller(
            'extension/opencart/module/manufacturer_list'
        );

        return $this->load->view('extension/dc_minimal/common/home', $data);
    }
}