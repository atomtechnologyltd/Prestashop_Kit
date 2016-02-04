<?php
class PaynetzResponseModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		
		$cart = $this->context->cart;
			
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'paynetz')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
			
		);
		if($_POST['f_code'] == "Ok"){
			$this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
		}else{
			$error_message = "Transaction is failed, Try again";
			$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
			'order-opc' : 'order';
			$url = _PS_VERSION_ >= '1.5' ?
				'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';
			$url .= 'step=3&cgv=1&paynetzerror=1&message='.$error_message;

			Tools::redirect($url);

			exit;
		}
	}
}
