<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Payment;

class DcBankTransfer extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('extension/dc_minimal/payment/dc_bank_transfer');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['bank'] = nl2br($this->config->get('payment_dc_bank_transfer_bank_' . $this->config->get('config_language_id')));
		
		$data['bank_name'] = $this->config->get('payment_dc_bank_transfer_bank_name');
		$data['account_name'] = $this->config->get('payment_dc_bank_transfer_account_name');
		$data['account_number'] = $this->config->get('payment_dc_bank_transfer_account_number');
		$data['branch'] = $this->config->get('payment_dc_bank_transfer_branch');
		
		$prefix = $this->config->get('payment_dc_bank_transfer_transfer_prefix');
		$data['transfer_content'] = $prefix . $this->session->data['order_id'];
		
		$data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
		
		// VietQR - Always convert to VND for the QR API
		$bin = $this->config->get('payment_dc_bank_transfer_bank_bin');
		$amount_vnd = (int)round($this->currency->convert($order_info['total'], $order_info['currency_code'], 'VND'));
		
		$data['qr_url'] = "https://img.vietqr.io/image/{$bin}-{$data['account_number']}-compact2.png?amount={$amount_vnd}&addInfo=" . urlencode($data['transfer_content']) . "&accountName=" . urlencode($data['account_name']);

		$data['language'] = $this->config->get('config_language');

		return $this->load->view('extension/dc_minimal/payment/dc_bank_transfer', $data);
	}

	public function confirm(): void {
		$this->load->language('extension/dc_minimal/payment/dc_bank_transfer');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = 'Order ID missing!';
		}

		if (!$json) {
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$prefix = $this->config->get('payment_dc_bank_transfer_transfer_prefix');
			$transfer_content = $prefix . $this->session->data['order_id'];

			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= "Bank: " . $this->config->get('payment_dc_bank_transfer_bank_name') . "\n";
			$comment .= "Account: " . $this->config->get('payment_dc_bank_transfer_account_number') . " (" . $this->config->get('payment_dc_bank_transfer_account_name') . ")\n";
			$comment .= "Content: " . $transfer_content . "\n\n";
			$comment .= $this->config->get('payment_dc_bank_transfer_bank_' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_dc_bank_transfer_order_status_id'), $comment, true);

			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
