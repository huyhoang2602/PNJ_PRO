<?php
    namespace Opencart\Catalog\Controller\Extension\DcMinimal\Catalog\Controller\Checkout;

    class DcCart extends \Opencart\System\Engine\Controller {


        public function index(): void {
            $json = [
                'status' => 'ok',
                'message' => 'Kontroler działa!',
                'time' => date('Y-m-d H:i:s')
            ];
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }


        public function edit(): void {
            $this->load->language('checkout/cart');

            $json = [];

            if (!empty($this->request->post['quantity'])) {
                foreach ($this->request->post['quantity'] as $key => $value) {
                    if ((int)$value > 0) {
                        $this->cart->update($key, $value);
                    } else {
                        $this->cart->remove($key);
                    }
                }
                $json['success'] = $this->language->get('text_remove');
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['reward']);
            } else {
                $json['error'] = 'No data to update.';
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }
