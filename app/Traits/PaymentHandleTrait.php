<?php
namespace App\Traits;

trait PaymentHandle
{
    public function initiateBinancePay(){
        // Generate nonce string
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';
        for($i=1; $i <= 32; $i++)
        {
            $pos = mt_rand(0, strlen($chars) - 1);
            $char = $chars[$pos];
            $nonce .= $char;
        }
        $ch = curl_init();
        $timestamp = round(microtime(true) * 1000);
        // Request body
         $request = array(
           "env" => array(
                 "terminalType" => "APP"
              ),
           "merchantTradeNo" => mt_rand(982538,9825382937292),
           "orderAmount" => 25.17,
           "currency" => "BUSD",
           "goods" => array([
                    "goodsType" => "02",
                    "goodsCategory" => "6000",
                    "referenceGoodsId" => "7876763A3B",
                    "goodsName" => "Ice Cream",
                    "goodsDetail" => "Greentea ice cream cone"
                 ])
        );

        $json_request = json_encode($request);
        $payload = $timestamp."\n".$nonce."\n".$json_request."\n";
        $binance_pay_key = "vm6kbwcfguzyquwix9lusw4wmtdwclk3bgxullympuanhbdgopamz5ytp5w84bak";
        $binance_pay_secret = "txllxem1hbbiumbmgz14vwagukqgbgoyeicrgz25f6xikkzhxhh0wb104phluk7z";
        $signature = strtoupper(hash_hmac('SHA512',$payload,$binance_pay_secret));
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "BinancePay-Timestamp: $timestamp";
        $headers[] = "BinancePay-Nonce: $nonce";
        $headers[] = "BinancePay-Certificate-SN: $binance_pay_key";
        $headers[] = "BinancePay-Signature: $signature";

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, "https://bpay.binanceapi.com/binancepay/openapi/v2/order");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

        $result = curl_exec($ch);
        if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); }
        curl_close ($ch);

        var_dump($result);

        //Redirect user to the payment page

    }
}
