<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * a special type of 'paypal ':
 * @author Max Milbers
 * @author Valérie Isaksen
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVMPaymentPaypal extends vmPSPlugin {

    // instance of class
    public static $_this = false;

    function __construct(& $subject, $config) {
	if (self::$_this)
	    return self::$_this;
	parent::__construct($subject, $config);

	$this->_loggable = true;
	$this->tableFields = array('id',
	    'virtuemart_order_id',
	    'order_number',
	    'virtuemart_paymentmethod_id',
	    'payment_name',
	    'cost',
	    'tax_id',
	    'paypal_custom',
	    'paypal_response_mc_gross',
	    'paypal_response_mc_currency',
	    'paypal_response_invoice',
	    'paypal_response_protection_eligibility',
	    'paypal_response_payer_id',
	    'paypal_response_tax',
	    'paypal_response_payment_date',
	    'paypal_response_payment_status',
	    'paypal_response_mc_fee',
	    'paypal_response_payer_email',
	    'paypal_response_last_name',
	    'paypal_response_first_name',
	    'paypal_response_business',
	    'paypal_response_receiver_email',
	    'paypal_response_transaction_subject',
	    'paypal_response_residence_country',
	    'paypalresponse_raw',
	    'created_on',
	    'created_by',
	    'modified_on',
	    'modified_by',
	    'locked_on',
	    'locked_by'); //,'created_on','created_by','modified_on','modified_by','locked_on');
// 		self::$_this
	//$this->createPluginTable($this->_tablename);
	self::$_this = $this;
    }

    /**
     * Create the table for this plugin if it does not yet exist.
     * @author Oscar van Eijk
     */
    protected function getTable() {


	return "CREATE TABLE IF NOT EXISTS `" . $this->_tablename . "` (
	    `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT ,
	    `virtuemart_order_id` int(11) UNSIGNED DEFAULT NULL,
	    `order_number` char(32) DEFAULT NULL,
	    `virtuemart_paymentmethod_id` mediumint(1) UNSIGNED DEFAULT NULL,
	    `payment_name` char(255) NOT NULL DEFAULT '',
	    `cost` decimal(10,2) DEFAULT NULL ,
	    `tax_id` int(11) DEFAULT NULL,
	    `paypal_custom` varchar(255)  ,
	    `paypal_response_mc_gross` decimal(10,2) DEFAULT NULL ,
	    `paypal_response_mc_currency` char(10) DEFAULT NULL,
	    `paypal_response_invoice` char(32) DEFAULT NULL,
	    `paypal_response_protection_eligibility` char(128) DEFAULT NULL,
	    `paypal_response_payer_id` char(13) DEFAULT NULL,
	    `paypal_response_tax` decimal(10,2) DEFAULT NULL ,
	    `paypal_response_payment_date` char(28) DEFAULT NULL,
	    `paypal_response_payment_status` char(50) DEFAULT NULL,
	    `paypal_response_mc_fee` decimal(10,2) DEFAULT NULL ,
	    `paypal_response_payer_email` char(128) DEFAULT NULL,
	    `paypal_response_last_name` char(64) DEFAULT NULL,
	    `paypal_response_first_name` char(64) DEFAULT NULL,
	    `paypal_response_business`  char(128) DEFAULT NULL,
	    `paypal_response_receiver_email`  char(128) DEFAULT NULL,
	    `paypal_response_transaction_subject` char(128) DEFAULT NULL,
	    `paypal_response_residence_country` char(2) DEFAULT NULL,
	    `paypalresponse_raw` char DEFAULT NULL,
	    `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
	    `created_by` int(11) NOT NULL DEFAULT 0,
	    `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `modified_by` int(11) NOT NULL DEFAULT 0,
	    `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	    `locked_by` int(11) NOT NULL DEFAULT 0,
	      PRIMARY KEY (`id`)
	    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Paypal Table' AUTO_INCREMENT=1 ;";
    }

    function plgVmConfirmedOrderRenderForm($psType, $order_number, VirtueMartCart $cart, $return_context, &$html, &$new_status) {
	if (!$this->selectedThisType($psType)) {
	    return null;
	}
	if (!($payment = $this->getPluginMethod($cart->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}

	$params = new JParameter($payment->payment_params);

	$this->_debug = $params->get('debug');
	$this->logInfo('plgVmConfirmedOrderRenderPaymentForm order number: ' . $order_number, 'message');

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	if (!class_exists('VirtueMartModelCurrency')
	)
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'currency.php');

	//$usr = & JFactory::getUser();
	$new_status = '';

	$address = $cart->ST;
	if (empty($address)) {
	    $address = $cart->BT;
	}

	$vendorModel = new VirtueMartModelVendor();
	$vendorModel->setId(1);
	$vendor = $vendorModel->getVendor();
	$currencyModel = new VirtueMartModelCurrency();
	$currency = $currencyModel->getCurrency($cart->pricesCurrency);

	$merchant_email = $this->_getMerchantEmail($params);
	if (empty($merchant_email)) {
	    vmInfo(JText::_('VMPAYMENT_PAYPAL_MERCHANT_EMAIL_NOT_SET'));
	    return false;
	}

	$testReq = $params->get('DEBUG') == 1 ? 'YES' : 'NO';
	$post_variables = Array(
	    'cmd' => '_ext-enter',
	    'redirect_cmd' => '_xclick',
	    'upload' => '1',
	    'business' => $merchant_email, //Email address or account ID of the payment recipient (i.e., the merchant).
	    'receiver_email' => $merchant_email, //Primary email address of the payment recipient (i.e., the merchant
	    'order_number' => $order_number,
	    "invoice" => $order_number,
	    'custom' => $return_context,
	    'item_name' => JText::_('VMPAYMENT_PAYPAL_ORDER_NUMBER') . ': ' . $order_number,
	    "amount" => $cart->pricesUnformatted['billTotal'],
	    "currency_code" => $currency->currency_code_3,
	    /*
	     * 1 – L'adresse spécifiée dans les variables pré-remplies remplace l'adresse de livraison enregistrée auprès de PayPal.
	     * Le payeur voit l'adresse qui est transmise mais ne peut pas la modifier.
	     * Aucune adresse n'est affichée si l'adresse n'est pas valable
	     * (par exemple si des champs requis, tel que le pays, sont manquants) ou pas incluse.
	     * Valeurs autorisées : 0, 1. Valeur par défaut : 0
	     */
	    //"address_override" => "1", // 0 ??   Paypal does not allow your country of residence to ship to the country you wish to
	    "first_name" => $address['first_name'],
	    "last_name" => $address['last_name'],
	    "address1" => $address['address_1'],
	    "address2" => isset($address['address_2']) ? $address['address_2'] : '',
	    "zip" => $address['zip'],
	    "city" => $address['city'],
	    "state" => isset($address['virtuemart_state_id']) ? ShopFunctions::getStateByID($address['virtuemart_state_id']) : '',
	    "country" => ShopFunctions::getCountryByID($address['virtuemart_country_id'], 'country_3_code'),
	    "email" => $address['email'],
	    "night_phone_b" => $address['phone_1'],
	    "return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=' . $cart->virtuemart_paymentmethod_id),
	    //"return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
	    "notify_url" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'),
	    "cancel_return" => JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginusercancel&on=' . $order_number . '&pm=' . $cart->virtuemart_paymentmethod_id),
	    //"undefined_quantity" => "0",
	    "ipn_test" => $params->get('debug'),
	    "pal" => "NRUBJXESJTY24",
	    // "image_url" => $vendor_image_url, // TO DO
	    //"no_shipping" => "1",
	    "no_note" => "1");

	/*
	  $i = 1;
	  foreach ($cart->products as $key => $product) {
	  $post_variables["item_name_" . $i] = substr(strip_tags($product->product_name), 0, 127);
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted[$key]['salesPrice'];
	  $post_variables["quantity_" . $i] = $product->quantity;
	  $i++;
	  }
	  if ($cart->pricesUnformatted ['shipmentValue']) {
	  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_SHIPMENT_PRICE');
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['shipmentValue'];
	  $post_variables["quantity_" . $i] = 1;
	  $i++;
	  }
	  if ($cart->pricesUnformatted ['paymentValue']) {
	  $post_variables["item_name_" . $i] = JText::_('VMPAYMENT_PAYPAL_PAYMENT_PRICE');
	  $post_variables["item_number_" . $i] = $i;
	  $post_variables["amount_" . $i] = $cart->pricesUnformatted ['paymentValue'];
	  $post_variables["quantity_" . $i] = 1;
	  $i++;
	  }
	  if (!empty($order->cart->coupon)) {
	  $post_variables["discount_amount_cart"] = $cart->pricesUnformatted['discountAmount'];
	  }
	 */



	// Prepare data that should be stored in the database
	$dbValues['order_number'] = $order_number;
	$dbValues['payment_name'] = parent::renderPluginName($payment, $params);
	$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
	$dbValues['paypal_custom'] = $return_context;

	$this->storePluginInternalData($dbValues);

	$url = $this->_getPaypalUrlHttps($params);

	// add spin image

	$html = '<form action="' . "https://" . $url . '" method="post" name="vm_paypal_form" >';
	$html.= '<input type="image" name="submit" src="http://www.paypal.com/en_US/i/btn/x-click-but6.gif" alt="Click to pay with PayPal - it is fast, free and secure!" />';
	foreach ($post_variables as $name => $value) {
	    $html.= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
	}
	$html.= '</form>';


	$html.= ' <script type="text/javascript">';
	$html.= ' document.vm_paypal_form.submit();';
	$html.= ' </script>';
	//echo $html;
	return 2; // don't delete the cart, don't send email and don't redirect
	//
	//
	 /*

	  $qstring = '?';
	  foreach ($post_variables AS $k => $v) {
	  $qstring .= ( empty($qstring) ? '' : '&')
	  . urlencode($k) . '=' . urlencode($v);
	  }
	  // we can display the logo, or do the redirect
	  $mainframe = JFactory::getApplication();
	  $mainframe->redirect("https://" . $url . $qstring);
	 */

	return false; // don't delete the cart, don't send email
    }

    function plgVmOnResponseReceived($psType, &$virtuemart_order_id, &$html) {
	if (!$this->selectedThisType($psType)) {
	    return null;
	}
// the payment itself should send the parameter needed.
	$virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);

	$vendorId = 0;
	if (!($payment = $this->getPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	$params = new JParameter($payment->payment_params);
	$payment_data = JRequest::get('post');
	vmdebug('plgVmOnResponseReceived', $payment_data);
	$order_number = $payment_data['invoice'];
	$return_context = $payment_data['custom'];
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number);
	$payment_name = $this->renderPluginName($payment, $params);
	$html = $this->_getPaymentResponseHtml($payment_data, $payment_name);

	return true;
    }

    function plgVmOnUserCancel($psType, &$virtuemart_order_id) {
	if (!$this->selectedThisType($psType)) {
	    return null;
	}
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	$order_number = JRequest::getVar('on');
	$payment_method_id = JRequest::getVar('pm');
	if (!$order_number)
	    return false;
	$db = JFactory::getDBO();
	$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename
		. " WHERE  `order_number`= '" . $order_number . "'"
		. ' AND  `virtuemart_paymentmethod_id` = ' . $payment_method_id;
	$db->setQuery($query);
	$virtuemart_order_id = $db->loadResult();

	//fwrite($fp, "order" . $virtuemart_order_id);
	if (!$virtuemart_order_id) {
	    return null;
	}


	return true;
    }

    /*
     *   plgVmOnNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
     * Return:
     *  Plugins that were not selected must return null, otherwise True of False must be returned indicating Success or Failure.
     * Parameters:
     *  None
     *  @author Valerie Isaksen
     */

    function plgVmOnNotification($psType, &$return_context, &$virtuemart_order_id, &$new_status) {

	if (!$this->selectedThisType($psType)) {
	    return null;
	}
	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$paypal_data = JRequest::get('post');
	//$this->_debug = true;
	$order_number = $paypal_data['invoice'];
	$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($paypal_data['invoice']);
	$this->logInfo('plgVmOnNotification: virtuemart_order_id  found ' . $virtuemart_order_id, 'message');

	if (!$virtuemart_order_id) {
	    $this->_debug = true; // force debug here
	    $this->logInfo('plgVmOnNotification: virtuemart_order_id not found ', 'ERROR');
	    // send an email to admin, and ofc not update the order status: exit  is fine
	    $this->sendEmailToVendorAndAdmins(JText::_('VMPAYMENT_PAYPAL_ERROR_EMAIL_SUBJECT'), JText::_('VMPAYMENT_PAYPAL_UNKNOW_ORDER_ID'));
	    exit;
	}
	$vendorId = 1;
	$payment = $this->getDataByOrderId($virtuemart_order_id);
	$paramstring = $this->getVmParams($vendorId, $payment->virtuemart_paymentmethod_id);
	$params = new JParameter($paramstring);

	$this->_debug = $params->get('debug');
	if (!$payment) {
	    $this->logInfo('getDataByOrderId payment not found: exit ', 'ERROR');
	    return null;
	}
	$this->logInfo('paypal_data ' . implode('   ', $paypal_data), 'message');

	// get all know columns of the table
	$db = JFactory::getDBO();
	$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
	$db->setQuery($query);
	$columns = $db->loadResultArray(0);
	$post_msg = '';
	foreach ($paypal_data as $key => $value) {
	    $post_msg .= $key . "=" . $value . "<br />";
	    $table_key = 'paypal_response_' . $key;
	    if (in_array($table_key, $columns)) {
		$response_fields[$table_key] = $value;
	    }
	}
	$response_fields['paypalresponse_raw'] = $return_context = $paypal_data['custom'];
	$response_fields['order_number'] = $order_number;
	$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
	// if not should Add a message in the BE,  send an email, and ofc not update the order status
	if (false) {
	    $query = 'SELECT ' . $this->_tablename . '.`payment_id` FROM ' . $this->_tablename
		    . ' LEFT JOIN #__virtuemart_orders ON   ' . $this->_tablename . '.`virtuemart_order_id` = #__virtuemart_orders.`virtuemart_order_id`
                    WHERE #__virtuemart_orders.`order_number`=' . $paypal_data['invoice']
		    . ' AND #__virtuemart_orders.`order_total` = ' . $paypal_data['mc_gross']
		    // . ' AND #__virtuemart_orders.`order_currency` = ' . $paypal_data['mc_currency']
		    . ' AND ' . $this->_tablename . '.`paypal_custom` = "' . $paypal_data['custom'] . '"';


	    $db = JFactory::getDBO();
	    $db->setQuery($query);
	    $result = $db->loadResult();
	}

	//TODO valerie, the function is now working like the normal tables, $response_fields must be adjusted
// 		$this->updateData($response_fields, $this->_tablename, 'virtuemart_order_id', $virtuemart_order_id);
	$this->storePluginInternalData($response_fields);

	$error_msg = $this->_processIPN($paypal_data, $params);
	$this->logInfo('process IPN ' . $error_msg, 'message');
	if (!(empty($error_msg) )) {
	    $new_status = $params->get('status_canceled');
	    $this->logInfo('process IPN ' . $error_msg . ' ' . $new_status, 'ERROR');
	} else {
	    $this->logInfo('process IPN OK, status', 'message');

	    /*
	     * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
	     * The status of the payment:
	     * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
	     * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	     * Created: A German ELV payment is made using Express Checkout.
	     * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
	     * Expired: This authorization has expired and cannot be captured.
	     * Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
	     * Pending: The payment is pending. See pending_reason for more information.
	     * Refunded: You refunded the payment.
	     * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	     * Processed: A payment has been accepted.
	     * Voided: This authorization has been voided.
	     *
	     */
	    if (empty($paypal_data['payment_status']) || ($paypal_data['payment_status'] != 'Completed' && $paypal_data['payment_status'] != 'Pending')) {
		return false;
	    }
	    $paypal_status = $paypal_data['payment_status'];
	    if (strcmp($paypal_status, 'Completed') == 0) {
		$new_status = $params->get('status_success');
	    }
	}

	$this->logInfo('plgVmOnNotification return new_status' . $new_status, 'message');
	return true;
    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBE()
     */
    function plgVmOnShowOrderBE($psType, $virtuemart_order_id, $payment_method_id) {

	if (!$this->selectedThis($payment_method_id, $psType)) {
	    return null; // Another method was selected, do nothing
	}
	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);
	if (!($paymentTable = $db->loadObject())) {
	    JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$html = '<table class="admintable">' . "\n";
	$html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('PAYPAL_PAYMENT_NAME', $paymentTable->payment_name);
	$code = "paypal_response_";
	foreach ($paymentTable as $key => $value) {
	    if (substr($key, 0, strlen($code)) == $code) {
		$html .= $this->getHtmlRowBE($key, $value);
	    }
	}
	$html .= '</table>' . "\n";
	return $html;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _processIPN($paypal_data, $params) {
	$secure_post = $params->get('secure_post', '0');
	$paypal_url = $this->_getPaypalURL($params);
	// read the post from PayPal system and add 'cmd'
	$post_msg = 'cmd=_notify-validate';
	foreach ($paypal_data as $key => $value) {
	    if ($key != 'view' && $key != 'layout') {
		$value = urlencode($value);
		$post_msg .= "&$key=$value";
	    }
	}

	$this->checkPaypalIps($paypal_data['ipn_test']);

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($post_msg) . "\r\n\r\n";

	if ($secure_post) {
	    // If possible, securely post back to paypal using HTTPS
	    // Your PHP server will need to be SSL enabled
	    $fps = fsockopen('ssl://' . $paypal_url, 443, $errno, $errstr, 30);
	} else {
	    $fps = fsockopen($paypal_url, 80, $errno, $errstr, 30);
	}

	if (!$fps) {
	    $this->sendEmailToVendorAndAdmins("error with paypal", JText::sprintf('VMPAYMENT_PAYPAL_ERROR_POSTING_IPN', $errstr, $errno));
	    return JText::sprintf('VMPAYMENT_PAYPAL_ERROR_POSTING_IPN', $errstr, $errno); // send email
	} else {
	    fputs($fps, $header . $post_msg);
	    while (!feof($fps)) {
		$res = fgets($fps, 1024);

		if (strcmp($res, 'VERIFIED') == 0) {
		    return '';
		} elseif (strcmp($res, 'INVALID') == 0) {
		    $this->sendEmailToVendorAndAdmins("error with paypal IPN NOTIFICATION", JText::_('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . $res);
		    return JText::_('VMPAYMENT_PAYPAL_ERROR_IPN_VALIDATION') . $res;
		}
	    }
	}

	fclose($fps);
	return '';
    }

    function _getMerchantEmail($params) {
	return $params->get('sandbox') ? $params->get('sandbox_merchant_email') : $params->get('paypal_merchant_email');
    }

    function _getPaypalUrl($params) {

	$url = $params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

	return $url;
    }

    function _getPaypalUrlHttps($params) {
	$url = $this->_getPaypalUrl($params);
	$url = $url . '/cgi-bin/webscr';

	return $url;
    }

    /*
     * CheckPaypalIPs
     * Cannot be checked with Sandbox
     * From VM1.1
     */

    function checkPaypalIps($test_ipn) {
	return;
	// Get the list of IP addresses for www.paypal.com and notify.paypal.com
	$paypal_iplist = array();
	$paypal_iplist = gethostbynamel('www.paypal.com');
	$paypal_iplist2 = array();
	$paypal_iplist2 = gethostbynamel('notify.paypal.com');
	$paypal_iplist3 = array();
	$paypal_iplist3 = array('216.113.188.202', '216.113.188.203', '216.113.188.204', '66.211.170.66');
	$paypal_iplist = array_merge($paypal_iplist, $paypal_iplist2, $paypal_iplist3);

	$paypal_sandbox_hostname = 'ipn.sandbox.paypal.com';
	$remote_hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

	$valid_ip = false;

	if ($paypal_sandbox_hostname == $remote_hostname) {
	    $valid_ip = true;
	    $hostname = 'www.sandbox.paypal.com';
	} else {
	    $ips = "";
	    // Loop through all allowed IPs and test if the remote IP connected here
	    // is a valid IP address
	    if (in_array($_SERVER['REMOTE_ADDR'], $paypal_iplist)) {
		$valid_ip = true;
	    }
	    $hostname = 'www.paypal.com';
	}

	if (!$valid_ip) {


	    $mailsubject = "PayPal IPN Transaction on your site: Possible fraud";
	    $mailbody = "Error code 506. Possible fraud. Error with REMOTE IP ADDRESS = " . $_SERVER['REMOTE_ADDR'] . ".
                        The remote address of the script posting to this notify script does not match a valid PayPal ip address\n
            These are the valid IP Addresses: $ips

            The Order ID received was: $invoice";
	    $this->sendEmailToVendorAndAdmins($mailsubject, $mailbody);


	    exit();
	}

	if (!($hostname == "www.sandbox.paypal.com" && $test_ipn == 1 )) {
	    $res = "FAILED";
	    $mailsubject = "PayPal Sandbox Transaction";
	    $mailbody = "Hello,
		A fatal error occured while processing a paypal transaction.
		----------------------------------
		Hostname: $hostname
		URI: $uri
		A Paypal transaction was made using the sandbox without your site in Paypal-Debug-Mode";
	    //vmMail($mosConfig_mailfrom, $mosConfig_fromname, $debug_email_address, $mailsubject, $mailbody );
	    $this->sendEmailToVendorAndAdmins($mailsubject, $mailbody);
	}
    }

    function _getPaymentResponseHtml($paypal_data, $payment_name) {
	vmdebug('paypal response', $paypal_data);

	$html = '<table>' . "\n";
	$html .= $this->getHtmlRow('PAYPAL_PAYMENT_NAME', $payment_name);
	$html .= $this->getHtmlRow('PAYPAL_ORDER_NUMBER', $paypal_data['invoice']);
	$html .= $this->getHtmlRow('PAYPAL_AMOUNT', $paypal_data['mc_gross'] . " " . $paypal_data['mc_currency']);

	$html .= '</table>' . "\n";

	return $html;
    }

    function getCosts($params, $cart_prices) {
	return $params->get('cost', 0);
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $payment, $cart_prices) {

	$params = new JParameter($payment->payment_params);
	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $params->get('min_amount', 0) AND $amount <= $params->get('max_amount', 0)
		OR
		($params->get('min_amount', 0) <= $amount AND ($params->get('max_amount', '') == '') ));

	$countries = array();
	$country_list = $params->get('countries');
	if (!empty($country_list)) {
	    if (!is_array($country_list)) {
		$countries[0] = $country_list;
	    } else {
		$countries = $country_list;
	    }
	}
	// probably did not gave his BT:ST address
	if (!is_array($address)) {
	    $address = array();
	    $address['virtuemart_country_id'] = 0;
	}

	if (!isset($address['virtuemart_country_id']))
	    $address['virtuemart_country_id'] = 0;
	if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
	    if ($amount_cond) {
		return true;
	    }
	}

	return false;
    }

}

// No closing tag
