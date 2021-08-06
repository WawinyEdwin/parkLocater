<?php

class config
{
    //configurations option
    public static $config

    //setting up global configurations for classes
    public function__construct($configs)
    {
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
        if (!empty($configs) && (!isset($configs["headoffice"]) || empty($configs["headoffice"])))
        {
            $defaults["headoffice"] = $configs["shortcode"];
        }

        for each ($defaults as $key => $value){
            if(isset($configs[$key])){
                $defaults[$key] = $configs[$key];
            }else {
                $defaults[$key] = $value;
            }
        }

        self::$config = (object) $configs;
    }

    public static function getConfig()
    {
        return self::config;
    }

    /*performing a get request from thr daraja api*/
    public static function remote_get($endpoint, $credentials = null)
    {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorisation:Basic ".$credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        return curl_exec($curl);
    }

    /*performing a POST request to the daraja api*/
    public static function remote_post($endpoint, $data_array())
    {
        $token = self::token();
        $curl = curl_init();
        $data_string = json_encode($data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl,CURLPOST_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_URL,$endpoint);
        curl_setopt($curl,CURLOPT_HTTPHEADER, array(
            "Content-Type:application/json",
            "AuthorisationBearer".$token,
        )
        );
        return curl_exec($curl);
    }

    /*fetch token to authenticate requests*/
    public static function token()
    {
        $endpoint = (self::$config->env == "live")
        ?"https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials"
        :"https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $credentials = base64_encode(self::$config->key. ":". self::$config->secret);
        $response = self::remote_get($endpoint, $credentials);
        $result = json_decode($response);

        return isset($result->access_token)? $result->access_token: "";

    }

    /*get transaction status*/
    public static function status(
        $transaction,
        $command = "transaction status query",
        $remarks = "transaction status query",
        $occassion = "transaction status query",
        $callback = null
    ){
        $env = self::$config->env;
        $plaintext = self::$config->password;
        $publickey = file_get_contents(__DIR__. "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publickey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $endpoint = ($env == "live")? "https://api.safaricom.co.kelipwa/transactionstatus/v1/query"
        :"https://sandbox.safaricom.co.kelipwa/transactionstatus/v1/query";

        $curl_post_data = array(
            "Initiator" => self::$config->username,
            "SecurityCredential" => $password,
            "CommandID" => $command,
            "TransactionID" => $transaction,
            "PartyA" => self::config->shortcode,
            "IdentifierType" = > self::$config->type,
            "ResultURL" => self::$config->results_url,
            "QueueTimeoutURL" => self:$config->timeout_url,
            "Remarks" => $remarks,
            "Ocassion" => $ocassion,
        );
        $response = $self:;remote_post($endpoint, $curl_post_data);
        $result = json_decode($response, true);

        return is_null($callback)
        ?$result :\call_user_func_array($callback, array($result));
    }

    /*reverse transaction*/
    public static function reverse(
        $transaction,
        $amount,
        $reciever,
        $reciever_type = 3,
        $remarks = "transaction reversal",
        $ocassion = "transaction reversal",
        $callback = null
    ){
        $env = self::$config->env;
        $plaintext = self::$config->password;
        $publickey = file_get_contents(__DIR__."/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publickey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $endpoint = ($env == "live")? "https://api.safaricom.co.kelipwa/reversal/v1/request" 
        : "https://sandbox.safaricom.co.kelipwa/reversal/v1/request";

        $curl_post_data = array(
            "CommandID" => "transactionreversal",
            "Initiator" => self::$config->business,
            "SecurityCredential" => $password,
            "Trnsaction" => $transaction,
            "Amount" => $amount,
            "RecieverParty" => $reciever,
            "RecieverIdentifierType" => $reciever_type,
            "ResultURL" => self::$config->result_url,
            "QueueTimeoutURL" => self::$config->timeout_url,
            "Remarks" => $remarks,
            "Ocassion" => $ocassion,
        );

        $response = self::remote_post($endpoint, $curl_post_data);
        $result = json_decode($response,true);

        return is_null($callback) 
        ?$result :\call_user_func_array($callback, array($result));
    }

    /*validate transaction data*/
    public static function validate($callback = null)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (is_null($callback)){
            return array(
                "resultCode" => 0,
                "resultDesc" => "success",
            );
        } else {
            return call_user_func_array($callback, array($data))
            ?array (
                "resultCode" => 0,
                "resultDesc" => "success",
            )
            :array(
                "resultCode" => 1,
                "resultDesc" => "failed",
            );
        }
    }

    /*confirm transaction data*/
    public static function confirm($callback == null)
    {
        $data = json_decode(file_get_contents("php://input"),true);
        if (is_null($callback)){
            return array(
                "resultCode" => 0,
                "resultDesc" => "success",
            );
        } else {
            return call_user_func_array($callback, array($data))
            ?array (
                "resultCode" => 0,
                "resultDesc" => "success",
            )
            : array (
                "resultCode" => 1,
                "resultDesc" => "failed",
            );
        }
    }

    /*reconcile transaction using an instant m-pesa payment notification*/
    public static function reconcile(callable $callback == null)
    {
        $response = json_decode(file_get_contents("php://input"), true);
        if (is_null($callback)){
            return array(
                "resultCode" => 0,
                "resultDesc" => "service request successful",
            );
        } else {
            return call_user_func_array($callback, array($response))
            ?array (
                "resultCode" => 0,
                "resultDesc" => "service request successful",
            )
            : array (
                "resultCode" => 1,
                "resultDesc" => "service request failed"
            );
        }
    }
}