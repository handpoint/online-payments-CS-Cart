<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Installs Payment Network payment processor.
 */
function fn_payment_network_install()
{
    /** @var \Tygh\Database\Connection $db */
    $db = \Tygh\Tygh::$app['db'];

    if (!$db->getField('SELECT type FROM ?:payment_processors WHERE processor_script = ?s', 'payment_network.php')) {
        $db->query("INSERT INTO ?:payment_processors ?e", array(
            'processor' => 'Payment Network',
            'processor_script' => 'payment_network.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template' => 'payment_network.tpl',
            'callback' => 'N',
            'type' => 'P',
            'addon' => 'payment network',
        ));
    }
}

/**
 * Disables Payment Network payment methods upon add-on uninstallation.
 */
function fn_payment_network_uninstall()
{
    /** @var \Tygh\Database\Connection $db */
    $db = \Tygh\Tygh::$app['db'];

    $processor_id = $db->getField(
        'SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s', 'payment_network.php'
    );

    if ($processor_id) {
        $db->query('DELETE FROM ?:payment_processors WHERE processor_id = ?i', $processor_id);
        $db->query('UPDATE ?:payments SET ?u WHERE processor_id = ?i',
                   array(
                       'processor_id'     => 0,
                       'processor_params' => '',
                       'status'           => 'D',
                   ),
                   $processor_id
        );
    }
}


##################################################


function fn_payment_network_process_request($orderData, $options = []) {
    switch ($options['integrationType']) {
        case PAYMENT_NETWORK_TYPE_HOSTED_V1:
            $req = array_merge($orderData, [
                'redirectURL'       => $options['redirectURL'],
                'callbackURL'       => $options['redirectURL'],
                'formResponsive'    => $options['responsive'],
                'threeDSVersion' => 2,
            ]);

            $req['signature'] = fn_payment_network_create_signature($req, $options['secret']);

            return fn_payment_network_hosted_redirect_form($req, $options);
        case PAYMENT_NETWORK_TYPE_HOSTED_V2:
            $req = array_merge($orderData, [
                'redirectURL'       => $options['redirectURL'],
                'callbackURL'       => $options['redirectURL'],
                'formResponsive'    => $options['responsive'],
                'threeDSVersion' => 2,
            ]);

            $req['signature'] = fn_payment_network_create_signature($req, $options['secret']);

            return fn_payment_network_hosted_redirect_form($req, array_merge($options, ['gatewayURL' => PAYMENT_NETWORK_ENDPOINT_HOSTED_MODAL]));
        default:
            throw new InvalidArgumentException(sprintf(
                'Not Implemented Integration: %s', $options['integrationType']
            ));
    }
}

/**
 * Send request to Gateway using HTTP Hosted API.
 *
 * The method will send a request to the Gateway using the HTTP Hosted API.
 *
 * The method returns the HTML fragment that needs including in order to
 * send the request.
 *
 * @param	array	    $parameters	request data
 * @param	array|null	$options	options (or null)
 * @return	string				    request HTML form.
 *
 * @throws	InvalidArgumentException	invalid request data
 */
function fn_payment_network_hosted_redirect_form(array $parameters, array $options = null) {
    $gatewayUrl = isset($options['gatewayURL']) && !empty($options['gatewayURL']) ? $options['gatewayURL'] : PAYMENT_NETWORK_ENDPOINT_HOSTED;
    $gatewayName = isset($options['gatewayName']) ? $options['gatewayName'] : 'Payment Network';
    $requestData = '';
    foreach ($parameters as $key => $value) {
        $requestData .= '<input type="hidden" name="' . $key . '" value="' . htmlentities($value) . '" />';
    }

    return <<<FORM
<form action="$gatewayUrl" method="post" id="payment_network_payment_form">
 <input type="submit" class="button alt" value="Pay securely via $gatewayName" />
 $requestData
</form>
<script type="text/javascript">
    window.onload = function () {
        document.getElementById('payment_network_payment_form').submit();
    };
</script>
FORM;
}

function fn_payment_network_create_signature(array $data, $key, array $fields = null) {

    $pairs = ($fields ? array_intersect_key($data, array_flip($fields)) : $data);

    ksort($pairs);

    // Create the URL encoded signature string
    $ret = http_build_query($pairs, '', '&');

    // Normalise all line endings (CRNL|NLCR|NL|CR) to just NL (%0A)
    $ret = preg_replace('/%0D%0A|%0A%0D|%0A|%0D/i', '%0A', $ret);

    // Hash the signature string and the key together
    $ret = hash("SHA512", $ret . $key);

    return $ret;
}