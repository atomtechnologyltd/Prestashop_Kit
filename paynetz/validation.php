<?php
include(dirname(__FILE__). '/../../config/config.inc.php');
include(dirname(__FILE__). '/../../init.php');

/* will include backward file */
include(dirname(__FILE__). '/paynetz.php');

$paynetz = new paynetz();

/* Does the cart exist and is valid? */
$cart = Context::getContext()->cart;

if (!isset($_POST['x_invoice_num']))
{
	Logger::addLog('Missing x_invoice_num', 4);
	die('An unrecoverable error occured: Missing parameter');
}

if (!Validate::isLoadedObject($cart))
{
	Logger::addLog('Cart loading failed for cart '.(int)$_POST['x_invoice_num'], 4);
	die('An unrecoverable error occured with the cart '.(int)$_POST['x_invoice_num']);
}

if ($cart->id != $_POST['x_invoice_num'])
{
	Logger::addLog('Conflict between cart id order and customer cart id');
	die('An unrecoverable conflict error occured with the cart '.(int)$_POST['x_invoice_num']);
}

$customer = new Customer((int)$cart->id_customer);
$invoiceAddress = new Address((int)$cart->id_address_invoice);
$currency = new Currency((int)$cart->id_currency);

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($invoiceAddress) && !Validate::isLoadedObject($currency))
{
	Logger::addLog('Issue loading customer, address and/or currency data');
	die('An unrecoverable error occured while retrieving you data');
}

		$returnUrl = "http://ps.localhost.com/module/paynetz/response";	
		$datenow = date("d/m/Y h:m:s");
		$encodedDate = str_replace(" ", "%20", $datenow);
		$url = Tools::safeOutput(Configuration::get('PAYNETZ_API_MERCHANT_URL'));
		$postFields  = "";
		$postFields .= "&login=".Tools::safeOutput(Configuration::get('PAYNETZ_API_LOGIN_ID'));
		$postFields .= "&pass=".Tools::safeOutput(Configuration::get('PAYNETZ_API_PASSWORD'));
		$postFields .= "&ttype=NBFundTransfer";
		$postFields .= "&prodid=".Tools::safeOutput(Configuration::get('PAYNETZ_API_PRODUCT_ID'));
		$postFields .= "&amt=".number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');
		$postFields .= "&txncurr=INR";
		$postFields .= "&txnscamt=0";
		$postFields .= "&clientcode=".urlencode(base64_encode("007"));
		$postFields .= "&txnid=".$_POST['x_invoice_num'];
		$postFields .= "&date=".$encodedDate;
		$postFields .= "&custacc=1234567890";
		$postFields .= "&ru=".$returnUrl;
		$sendUrl = $url."?".substr($postFields,1)."\n";
		
		$returnData = curlExec($sendUrl);
		$xmlObjArray     = xmltoarray($returnData);
		
		$url = $xmlObjArray['url'];
		$postFields  = "";
		$postFields .= "&ttype=NBFundTransfer";
		$postFields .= "&tempTxnId=".$xmlObjArray['tempTxnId'];
		$postFields .= "&token=".$xmlObjArray['token'];
		$postFields .= "&txnStage=1";
		$url = $url."?".$postFields;
		
		header("Location: ".$url);
		
		
	function xmltoarray($data){
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); 
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($data), $xml_values);
		xml_parser_free($parser);
		
		$returnArray = array();
		$returnArray['url'] = $xml_values[3]['value'];
		$returnArray['tempTxnId'] = $xml_values[5]['value'];
		$returnArray['token'] = $xml_values[6]['value'];

		return $returnArray;
	}

	function curlExec($base_url){
		$ch = curl_init($base_url);
		curl_setopt_array($ch, array(
		CURLOPT_URL            => $base_url,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
		CURLOPT_SSL_VERIFYPEER =>0,
		CURLOPT_SSL_VERIFYHOST => 0
	  ));

	  $results = curl_exec($ch);
	  return $results;
	}