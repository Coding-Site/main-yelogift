<?php


namespace App\Traits;

trait BinanceTrait
{
    private $KEY= '5cc4e4b6a1dfb1b42634bd453489896769fd94987f2a88a89fc2f91fecfe81b0';
    private $SECRET = 'devlzbreyistxksank32unms6jc2ugshtvrdqaubatifgznfoiovbo0gmnbd676b';

    // private $BASE_URL = 'https://api.binance.com/'; // production
    private $BASE_URL = 'https://testnet.binance.vision/'; // testnet

    function signature($query_string, $secret) {
        return hash_hmac('sha256', $query_string, $secret);
    }

    public function sendRequest($method, $path) {
        global $KEY;
        global $BASE_URL;

        $url = "${BASE_URL}${path}";

        echo "requested URL: ". PHP_EOL;
        echo $url. PHP_EOL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY:'.$KEY));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method == "POST" ? true : false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execResult = curl_exec($ch);
        $response = curl_getinfo($ch);

        // if you wish to print the response headers
        // echo print_r($response);
        return response;

        curl_close ($ch);
        return json_decode($execResult, true);
    }

    public function signedRequest($method, $path, $parameters = []) {
        global $SECRET;

        $parameters['timestamp'] = round(microtime(true) * 1000);
        $query = $this->buildQuery($parameters);
        $signature = $this->signature($query, $SECRET);
        return $this->sendRequest($method, "${path}?${query}&signature=${signature}");
    }

    public function buildQuery(array $params)
    {
        $query_array = array();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $query_array = array_merge($query_array, array_map(function ($v) use ($key) {
                    return urlencode($key) . '=' . urlencode($v);
                }, $value));
            } else {
                $query_array[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        return implode('&', $query_array);
    }

    public function binance_make_order()
    {
        // Generate nonce string
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';
        for ($i = 1; $i <= 32; $i++) {
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
            "orderTags" => array(
                "ifProfitSharing" => false
            ),
            "merchantTradeNo" => 11,
            "orderAmount" => 1,
            "currency" => "USDT",
            "description"=> "Payment for Order " . 13,
            "goods" => array([
                "goodsType" => "02",
                "goodsCategory" => "6000",
                "referenceGoodsId" => 13,
                "goodsName" => "test",
                "goodsDetail" => "test test"
            ])
        );
        // return ($request);
        // return [
        //     'encode' => json_encode($request),
        //     'decode' => json_decode(json_encode($request))
        // ];
        // exit;
        $json_request = json_encode($request);
        $payload = $timestamp . "\n" . $nonce . "\n" . $json_request . "\n";
        $binance_pay_key = "5cc4e4b6a1dfb1b42634bd453489896769fd94987f2a88a89fc2f91fecfe81b0";
        $binance_pay_secret = "devlzbreyistxksank32unms6jc2ugshtvrdqaubatifgznfoiovbo0gmnbd676b";
        $signature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_secret));
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
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }

    /*
    // get orderbook
    $response = sendRequest('GET', 'api/v3/depth?symbol=BNBUSDT&limit=5');
    echo json_encode($response);

    // get account information, make sure API key and secret are set
    $response = signedRequest('GET', 'api/v3/account');
    echo json_encode($response);

    // place order, make sure API key and secret are set, recommend to test on testnet.
    $response = signedRequest('POST', 'api/v3/order', [
    'symbol' => 'BNBUSDT',
    'side' => 'BUY',
    'type' => 'LIMIT',
    'timeInForce' => 'GTC',
    'quantity' => 1,
    'price' => 15,
    // 'newClientOrderId' => 'my_order', // optional
    'newOrderRespType' => 'FULL' //optional
    ]);
    echo json_encode($response);
    */
}
