<?php
if (!defined('_PS_VERSION_'))
	exit;

class paynetz extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'paynetz';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'ATOM';
		$this->aim_available_currencies = array('INR');

		parent::__construct();

		$this->displayName = 'Paynetz ATOM Payment Gateway';
		$this->description = $this->l('Receive payment with Paynetz');

		/* For 1.0.0 and less compatibility */
		$updateConfig = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		/* Check if cURL is enabled */
		if (!is_callable('curl_exec'))
			$this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		return parent::install() &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('payment') &&
			$this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
			Configuration::updateValue('AUTHORIZE_AIM_DEMO', 1) &&
			Configuration::updateValue('AUTHORIZE_AIM_HOLD_REVIEW_OS', _PS_OS_ERROR_);
	}
	
	public function uninstall()
	{
		Configuration::deleteByName('PAYNETZ_API_MERCHANT_URL');
		Configuration::deleteByName('PAYNETZ_API_SERVER_PORT');
		Configuration::deleteByName('PAYNETZ_API_LOGIN_ID');
		Configuration::deleteByName('PAYNETZ_API_PASSWORD');
		Configuration::deleteByName('PAYNETZ_API_PRODUCT_ID');
		return parent::uninstall();
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR'))
			$this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
		else
			$this->context->smarty->assign('status', 'failed');

		return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
	}

	public function hookBackOfficeHeader()
	{
		//$this->context->controller->addJS($this->_path.'js/authorizeaim.js');
		//$this->context->controller->addCSS($this->_path.'css/authorizeaim.css');
	}

	public function getContent()
	{
		$html = '';

		if (Tools::isSubmit('submitModule'))
		{
			Configuration::updateValue('PAYNETZ_API_MERCHANT_URL', Tools::getvalue('api_merchant_url'));
			Configuration::updateValue('PAYNETZ_API_SERVER_PORT', Tools::getvalue('api_server_port'));
			Configuration::updateValue('PAYNETZ_API_LOGIN_ID', Tools::getvalue('api_login_id'));
			Configuration::updateValue('PAYNETZ_API_PASSWORD', Tools::getvalue('api_password'));
			Configuration::updateValue('PAYNETZ_API_PRODUCT_ID', Tools::getvalue('api_product_id'));
			
			$html .= $this->displayConfirmation($this->l('Configuration updated'));
		}

		// For "Hold for Review" order status
		$currencies = Currency::getCurrencies(false, true);
		$order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

		$this->context->smarty->assign(array(
			'available_currencies' => $this->aim_available_currencies,
			'currencies' => $currencies,
			'module_dir' => $this->_path,
			'order_states' => $order_states,

			'PAYNETZ_API_MERCHANT_URL' => Configuration::get('PAYNETZ_API_MERCHANT_URL'),
			'PAYNETZ_API_SERVER_PORT' => Configuration::get('PAYNETZ_API_SERVER_PORT'),
			'PAYNETZ_API_LOGIN_ID' => Configuration::get('PAYNETZ_API_LOGIN_ID'),
			'PAYNETZ_API_PASSWORD' => Configuration::get('PAYNETZ_API_PASSWORD'),
			'PAYNETZ_API_PRODUCT_ID' => Configuration::get('PAYNETZ_API_PRODUCT_ID'),
		));
				
		return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configuration.tpl');
	}

	public function hookPayment($params)
	{	
		$currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);

		if (!Validate::isLoadedObject($currency))
			return false;
		
		$isFailed = Tools::getValue('paynetzerror');
		
		
		$this->context->smarty->assign('x_invoice_num', (int)$params['cart']->id);
		$this->context->smarty->assign('isFailed', $isFailed);
		
		return $this->display(__FILE__, 'views/templates/hook/paynetz.tpl');
	}

	public function hookHeader()
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.validate.creditcard2-1.0.1.js');
		else
			$this->context->controller->addJqueryPlugin('validate-creditcard');
	}

	/**
	 * Set the detail of a payment - Call before the validate order init
	 * correctly the pcc object
	 * See Authorize documentation to know the associated key => value
	 * @param array fields
	 */
	public function setTransactionDetail($response)
	{
		// If Exist we can store the details
		if (isset($this->pcc))
		{
			$this->pcc->transaction_id = (string)$response[6];

			// 50 => Card number (XXXX0000)
			$this->pcc->card_number = (string)substr($response[50], -4);

			// 51 => Card Mark (Visa, Master card)
			$this->pcc->card_brand = (string)$response[51];

			$this->pcc->card_expiration = (string)Tools::getValue('x_exp_date');

			// 68 => Owner name
			$this->pcc->card_holder = (string)$response[68];
		}
	}
	
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
	
	public function hookPaymentReturn($params)
	{
		echo "HI";exit;
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}


}
