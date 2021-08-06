<?php

/*when a user click book space , we will initiate a query to check the status of the previous transaction*/

session_start();

require_once('config.php');

$url_succcess = ""// we place the link to return to if the payment was succesful
$url_cancel = "" //we place the link to return to if the transaction was cancelled.
$url_error = ""//we place the link to return to if an error had occured during the transaction.

$endpoint = ($configuration->config->env == "live")
?"https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query" : "https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query";

$timestamp = data("YmdHis");
$password = base64_encode($configuration->config->shortcode. $configuration->config->$passkey. $timestamp);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url2);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("authorisation basic".$password));
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_response = curl_exec($curl);
$access_token - json_decode($curl_response);

$curl_post_data = array(
    'BusinessShortCode' => $configuration->config->headoffice,,
    'password' => $lipa_na_mpesa_password,
    'timestamp' => $timestamp,
    'CheckoutRequestID' => $_SESSION['CheckoutRequestID']
);

$response = $configuration->process_getRequest($endpoint, $access_token);

if ($res['ResultCode'] == 0) {
    //if the transaction was a success
    header('location:'$url_succcess. '');
    exit();
} else if ($res['ResultCode'] == 1032) {
    //if the transaction was cancelled
    header('location:'$url_cancel. '');
    exit();
} else {
    //flag any other response as an error
    header('location:'$url_error. '');
    exit();
}

?>