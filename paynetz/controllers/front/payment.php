<?php
class PaynetzPaymentModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		
		$cart = $this->context->cart;

		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		
		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'x_invoice_num' => $cart->id
		));
		
		$this->setTemplate('payment_execution.tpl');
	}
}
