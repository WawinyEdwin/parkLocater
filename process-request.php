<?php

//when the user enters the phone number and press SEND , 
//at the end of the transactiomn, the user is redirected to the page confirm-payment.php and confirms the transaction 
//success or not

require('config.php');

session_start();
$base = (isset($_SERVER["HTTPS"])? "https": "http"). "://".(isset($_SERVER["SERVER_NAME"])? $_SERVER
        ["SERVER_NAME"]:'');
        $default = array(
            "env" => "sandbox",
            "type" => 4,
            "shortcode" => "174379",
            "headoffice" => "174379",
            "key" => "JfgNvJplzyILBV1ZOqnlkEszhGnqxUO",
            "secret" => "Ge7Plo7JHIp10Xmg",
            "username" => "apitest",
            "password" => "",
            "passkey" => "bfb279f9aa9bcdcf158e97dd71a467cd2e0c893059b10f78"
            "validation_url" => $base. "api/validate",
            "confirmation_url" => $base. "api/confirm",
            "callback_url" => $base. "api/reconcile",
            "timeout_url" => $base. "api/timeout",
            "results_url" => $base. "api/results"
        );

        $configuration = new config($defaults);

        if (isset($_POST['submit'])){

            $phone = $_POST['phone'];
            $amount = 1;
            $reference = "ACCOUNT";
            $description = "transaction description";
            $remark = "remark";
            $callback = null;

            $phone = (substr($phone, 0, 1) == "+")? str_replace("+", "", $phone);
            $phone = (substr($phone, 0, 1) == "0")? preg_replace("/^0/", "254", $phone) : $phone;
            $timestamp = date("YmdHis");
            $password = base64_encode($configuration->getConfig()->shortcode. $configuration->getConfig(->passkey. $timestamp));
            $endpoint = ($configuration->getConfig()->env == "live")
            ?"https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest" 
            : "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

            $curl_post_data = array(
                "BusinessShortCode" => $configuration->getConfig()->headoffice,
                "password" => $password,
                "timestamp" => $timestamp,
                "transactionType" => ($configuration->getConfig()->type == 4)?
                "CustomerPayBillOnline" : "CustomerBuyGoodsOnline",
                "amount" => $amount,
                "partyA" => $phone,
                "partyB" => $configuration->getConfig()->shortcode,
                "phonenumber" => $phone,
                "callbackURL" => $configuration->getConfig()->callback_url,
                "accountrefrence" => $refrence,
                "transactionDesc" => $description,
                "remark" => $remark,
            );

            $response = $configuration->remote_post($endpoint, $curl_post_data);
            $result = json_decode($response, true);

            if ($result['ResponseCode'] && $result['ResponseCode']  == 0){
                $_SESSION['MerchantRequestID'] = $result['MerchantRequestID'];
                $_SESSION['CheckoutRequestID'] = $result['CheckoutRequestID'];
                $_SESSION['Amount'] = $amount;
                header("location:../confirm-payment.php");
            } else if ($result['errorCode'] && $result['errorCode'] == '500.001.1001') {
                $errors = "Error! a transaction is already in progress for the current phone number";
                header("location:../index.php?error=". $errors. "");
            } else if ($result['errorCode'] && $result['errorCode'] == '400.002.02') {
                $errors = "Error! invalid request";
                header("location:../index.php?error=". errors. "");
            } else {
                $errors = "Error! unable to make the MPESA STK push request. if the problem persists please contact support!";
                header("location:../index.php?error=". $errors. "");
            }
        }
    ?>