<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Payment;

class DcVnpay extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (isset($this->session->data['order_id'])) {
			$tmnCode = $this->config->get('payment_dc_vnpay_tmn_code');
			$hashSecret = $this->config->get('payment_dc_vnpay_hash_secret');
			$vnp_Url = $this->config->get('payment_dc_vnpay_url') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
			
            // Fix: OpenCart might return &amp;, VNPAY needs raw &
			$vnp_ReturnUrl = str_replace('&amp;', '&', $this->url->link('extension/dc_minimal/payment/dc_vnpay.callback', 'language=' . $this->config->get('config_language')));

			$vnp_TxnRef = $this->session->data['order_id'];
			$vnp_OrderInfo = "Thanh toan don hang #" . $this->session->data['order_id'];
            
            // Get Absolute Latest Total from Cart (most reliable during checkout)
            $totals = [];
            $taxes = $this->cart->getTaxes();
            $total = 0;
            $this->load->model('checkout/cart');
            ($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

            // Use the calculated total
			$vnp_Amount = (int)round($total * 100);
            
			$vnp_Locale = 'vn';
			$vnp_IpAddr = $this->request->server['REMOTE_ADDR'];

			$inputData = array(
				"vnp_Version" => "2.1.0",
				"vnp_TmnCode" => $tmnCode,
				"vnp_Amount" => $vnp_Amount,
				"vnp_Command" => "pay",
				"vnp_CreateDate" => date('YmdHis'),
				"vnp_CurrCode" => "VND",
				"vnp_IpAddr" => $vnp_IpAddr,
				"vnp_Locale" => $vnp_Locale,
				"vnp_OrderInfo" => $vnp_OrderInfo,
				"vnp_OrderType" => "topup",
				"vnp_ReturnUrl" => $vnp_ReturnUrl,
				"vnp_TxnRef" => $vnp_TxnRef,
			);

			ksort($inputData);
			$query = "";
			$i = 0;
			$hashdata = "";
			foreach ($inputData as $key => $value) {
				if ($i == 1) {
					$hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
				} else {
					$hashdata .= urlencode($key) . "=" . urlencode($value);
					$i = 1;
				}
				$query .= urlencode($key) . "=" . urlencode($value) . '&';
			}

			$vnp_Url = $vnp_Url . "?" . $query;
			if (isset($hashSecret)) {
				$vnpSecureHash = hash_hmac('sha512', $hashdata, $hashSecret);
				$vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
			}

            // Bind to template variables
            $data['vnp_qr_url'] = "https://quickchart.io/qr?size=250&text=" . urlencode($vnp_Url);
            $data['vnp_payment_url'] = $vnp_Url;
            $data['order_id'] = $order_info['order_id'];
            
            // Display total logic - show the exact formatted total the user sees in the summary
            $data['total'] = $this->currency->format($total, $this->session->data['currency']);
            $data['language'] = $this->config->get('config_language');

			return $this->load->view('extension/dc_minimal/payment/dc_vnpay', $data);
		}

		return '';
	}

	public function confirm(): void {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!$json) {
			$this->load->model('checkout/order');
            // We set it to "Pending" initially, IPN will update it to "Complete".
			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), $this->language->get('text_instruction'), true);
			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function callback(): void {
		$this->load->language('extension/dc_minimal/payment/dc_vnpay');

		$inputData = array();
		foreach ($this->request->get as $key => $val) {
			if (substr($key, 0, 4) == "vnp_") {
				$inputData[$key] = $val;
			}
		}

		$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
		unset($inputData['vnp_SecureHash']);
		ksort($inputData);
		$i = 0;
		$hashData = "";
		foreach ($inputData as $key => $value) {
			if ($i == 1) {
				$hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
			} else {
				$hashData = $hashData . urlencode($key) . "=" . urlencode($value);
				$i = 1;
			}
		}

		$secureHash = hash_hmac('sha512', $hashData, $this->config->get('payment_dc_vnpay_hash_secret'));

		if ($secureHash == $vnp_SecureHash && isset($inputData['vnp_ResponseCode'])) {
			if ($inputData['vnp_ResponseCode'] == '00') {
                // Localhost IPN Fallback: Update order status to paid if IPN hasn't reached us
                if (isset($this->session->data['order_id'])) {
                    $this->load->model('checkout/order');
                    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                    if ($order_info && $order_info['order_status_id'] != $this->config->get('payment_dc_vnpay_order_status_id')) {
                        $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_dc_vnpay_order_status_id'), 'VNPAY Callback Success Return (Localhost Fallback)', true);
                    }
                }
				// Payment Success -> Go to Success
				$this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true));
			} else {
				// Payment Failed or Canceled -> Go to Checkout
				$this->session->data['error'] = "Giao dịch thanh toán VNPAY đã bị hủy hoặc không thành công.";
				$this->response->redirect($this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'), true));
			}
		} else {
			// Invalid signature or missing data
			$this->session->data['error'] = "Phản hồi từ VNPAY không hợp lệ.";
			$this->response->redirect($this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'), true));
		}
	}

	public function ipn(): void {
		$inputData = array();
		foreach ($this->request->get as $key => $val) {
			if (substr($key, 0, 4) == "vnp_") {
				$inputData[$key] = $val;
			}
		}

		$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
		unset($inputData['vnp_SecureHash']);
		ksort($inputData);
		$i = 0;
		$hashData = "";
		foreach ($inputData as $key => $value) {
			if ($i == 1) {
				$hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
			} else {
				$hashData = $hashData . urlencode($key) . "=" . urlencode($value);
				$i = 1;
			}
		}

		$secureHash = hash_hmac('sha512', $hashData, $this->config->get('payment_dc_vnpay_hash_secret'));
		$order_id = $inputData['vnp_TxnRef'] ?? 0;

		if ($secureHash == $vnp_SecureHash) {
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
					$this->model_checkout_order->addHistory($order_id, $this->config->get('payment_dc_vnpay_order_status_id'), 'VNPAY Payment Successful', true);
					echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
				} else {
					echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success (Failed Payment)']);
				}
			} else {
				echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
			}
		} else {
			echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
		}
	}

    public function check(): void {
        $json = ['status' => 'pending'];
        if (isset($this->request->get['order_id'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
            if ($order_info && $order_info['order_status_id'] == $this->config->get('payment_dc_vnpay_order_status_id')) {
                $json['status'] = 'success';
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
