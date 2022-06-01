<?php

if (!defined('AREA')) {
    die('Access denied');
}

if (defined('PAYMENT_NOTIFICATION')) {
    fn_mark_payment_started($_GET['order_id']);

    if (isset($_POST['responseCode'])) {
	
	    // Get the password
	    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", (int)$_GET['order_id']);
	    $processor_data = fn_get_payment_method_data($payment_id);
	
	    $order_info = fn_get_order_info($_GET['order_id']);

	    if (($_POST['responseCode'] === '0') && ($order_info)) {
	        if (isset($_POST['signature'])) {
	            $sign = $_POST;
	            unset($sign['signature']);
	            $signature = fn_payment_network_create_signature($sign, $processor_data['processor_params']['passphrase']);
	        }
	
	        if (isset($signature) && $signature !== $_POST['signature']) {
	            $pp_response['order_status'] = 'F';
	            $pp_response["reason_text"] = "Signature not matched on Payment Network payment response.";
	
	        } else {
	            $pp_response['order_status'] = "P";
	            $pp_response["reason_text"] = $_POST['responseMessage'];
	            $pp_response["transaction_id"] = $_POST['xref'];
	
	        }
	    } else {
	        $pp_response['order_status'] = 'F';
	        $pp_response["reason_text"] = "Payment Failed - Detail: \"" . $_POST['responseMessage'] . "\"";
	    }
	} else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = "An unknown error occured";	
	}

    fn_finish_payment($_GET['order_id'], $pp_response, false);
    fn_order_placement_routines('route', $_GET['order_id'], false);
		
} else {
    if (!isset($processor_data)) {
        if (!isset($payment_id)) {
            $payment_id = isset($_REQUEST['payment_id']) ? $_REQUEST['payment_id'] : null;
        }

        $processor_data = fn_get_processor_data($payment_id);
    }

    $smarty = \Tygh\Registry::get('view');
    $smarty->assign('iframe_mode',  in_array($processor_data["processor_params"]['integration_type'], ['iframe', 'iframe_v2'], true));

    $orderid = (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '-' . fn_date_format(time(), '%H_%M_%S');
    $return = fn_url("payment_notification.process?payment=payment_network&order_id=$order_id", AREA, 'current');
    $address = "{$order_info['b_address']} {$order_info['b_address_2']} {$order_info['b_city']} {$order_info['b_county']} {$order_info['b_state']} {$order_info['b_country']}";

    $fields = array(
        "merchantID" 		=> $processor_data["processor_params"]["merchant_id"],
        "amount" 			=> $order_info['total'] * 100,
        "countryCode" 		=> $processor_data["processor_params"]["countrycode"],
        "currencyCode" 		=> $processor_data["processor_params"]["currencycode"],
        "transactionUnique" => $orderid,
        "customerAddress" 	=> $address,
        "customerPostCode" 	=> $order_info['b_zipcode'],
        "customerEmail" 	=> $order_info['email'],
        "customerPhone" 	=> $order_info['phone'],
        "merchantData" 		=> "cs-cart-hosted-1",
    );

    echo fn_payment_network_process_request($fields, [
        "merchantID" 	  => $processor_data["processor_params"]["merchant_id"],
        "redirectURL" 	  => $return,
        "secret" 		  => $processor_data["processor_params"]['passphrase'],
        "integrationType" => $processor_data["processor_params"]['integration_type'],
        "responsive"      => $processor_data["processor_params"]['responsive'],
    ]);
}
exit;

