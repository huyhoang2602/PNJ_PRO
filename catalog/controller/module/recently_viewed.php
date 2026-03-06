<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class RecentlyViewed extends \Opencart\System\Engine\Controller {
    
    // Hàm này được gọi tự động bởi Event mỗi khi xem trang sản phẩm
    // Thêm mixed &$output vào cuối danh sách tham số
// Khi dùng trigger |before, hàm chỉ nhận 2 tham số: $route và $args
public function addHistory(string &$route, array &$args): void {
    // Kiểm tra product_id có trong request không
    $this->log->write('XAC NHAN: Event da goi vao ham addHistory!');
    if (isset($this->request->get['product_id'])) {
        $product_id = (int)$this->request->get['product_id'];
        // Ghi log
        $this->log->write('RECENTLY_VIEWED: Đã lưu ID ' . $product_id . ' vào lịch sử (Trigger: After)');
    
        // Khởi tạo session nếu chưa có
        if (!isset($this->session->data['recently_viewed'])) {
            $this->session->data['recently_viewed'] = [];
        }
        $this->log->write('SESSION saved: ' . json_encode($this->session->data['recently_viewed']));
        // Xóa ID cũ nếu đã tồn tại để tránh trùng lặp và đưa lên đầu
        if (($key = array_search($product_id, $this->session->data['recently_viewed'])) !== false) {
            unset($this->session->data['recently_viewed'][$key]);
        }

        // Thêm ID sản phẩm mới vào đầu mảng
        array_unshift($this->session->data['recently_viewed'], $product_id);

        // Giới hạn danh sách tối đa 10 sản phẩm
        $this->session->data['recently_viewed'] = array_slice($this->session->data['recently_viewed'], 0, 10);
        
        }
}

    public function index(): string {
        $this->load->language('extension/dc_minimal/module/recently_viewed');
       
        $data['products'] = [];

        if (!empty($this->session->data['recently_viewed'])) {
            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            foreach ($this->session->data['recently_viewed'] as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    if ($product_info['image']) {
                        $image = $this->model_tool_image->resize($product_info['image'], 200, 200);
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', 200, 200);
                    }

                    $data['products'][] = [
                        'product_id'  => $product_info['product_id'],
                        'thumb'       => $image,
                        'name'        => $product_info['name'],
                        'price'       => $this->currency->format($product_info['price'], $this->session->data['currency']),
                        'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
                    ];
                }
            }
        }

        if ($data['products']) {
            return $this->load->view('extension/dc_minimal/module/recently_viewed', $data);
        }
        return '';
    }
}