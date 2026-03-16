<?php
namespace Opencart\Admin\Controller\Extension\DcMinimal\Module;

class RecentlyViewed extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/dc_minimal/module/recently_viewed');

        $this->document->setTitle($this->language->get('heading_title'));

        // Khởi tạo $data để tránh lỗi Undefined variable
        $data = [];

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        // Đường dẫn action cho form save
        $data['save'] = $this->url->link('extension/dc_minimal/module/recently_viewed|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Lấy giá trị status từ cấu hình hệ thống
        
        $data['module_recently_viewed_status'] = $this->config->get('module_recently_viewed_status');

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dc_minimal/module/recently_viewed', $data));
    }

    // Bổ sung hàm save xử lý lưu dữ liệu qua AJAX
    public function save(): void {
        $this->load->language('extension/dc_minimal/module/recently_viewed');

        $json = [];

        // Kiểm tra quyền chỉnh sửa
        if (!$this->user->hasPermission('modify', 'extension/dc_minimal/module/recently_viewed')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            // Lưu dữ liệu vào bảng oc_setting với tiền tố module_recently_viewed
            $this->model_setting_setting->editSetting('module_recently_viewed', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

public function install(): void {
    $this->load->model('setting/event');
    
    // Đảm bảo xóa sạch các bản ghi cũ nếu có
    $this->model_setting_event->deleteEventByCode('dc_minimal_recently_viewed');

    // Đăng ký Event mới
    $this->model_setting_event->addEvent([
        'code'        => 'dc_minimal_recently_viewed',
        'description' => 'Lưu lịch sử xem sản phẩm cho theme dc_minimal',
        'trigger'     => 'catalog/controller/product/product/before', 
        'action'      => 'extension/dc_minimal/module/recently_viewed.addHistory',
        'status'      => 1,
        'sort_order'  => 1
    ]);
}

    public function uninstall(): void {
        if ($this->user->hasPermission('modify', 'extension/dc_minimal/module/recently_viewed')) {
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEventByCode('dc_minimal_recently_viewed');
        }
    }
}