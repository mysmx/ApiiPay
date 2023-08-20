<?php 
//https://checkout.ipay.ua/doc#General
class ApiiPay
{
    private $mch_id, $url, $salt, $sign;

    public function arrayToXml($array, $rootElement, $xml = null) { 
        $_xml = $xml; 
        if ($_xml === null) { 
         $_xml = new SimpleXMLElement ('<?xml version="1.0" encoding="utf-8" standalone="yes"?>'.$rootElement); 
        } 
        foreach ($array as $k => $v) { 
         if (preg_match("/transaction_/i", $k)) $k = 'transaction';
         if (is_array($v)) {  
          $this->arrayToXml ($v, $k, $_xml->addChild($k)); 
         } 
         else { 
          $_xml->addChild($k, $v); 
         } 
        } 
        return $_xml->asXML(); 
    }

    function init($mch_id, $sign_key){
        $this->url = 'https://api.ipay.ua';
        $this->salt = sha1(microtime(true));
        $this->mch_id = $mch_id;
        $this->sign = hash_hmac('sha512', $this->salt, $sign_key);
    }


    private function getData($xml){
        $data = ['error' => '','data' => ''];
        $ch = curl_init($this->url);
        $headers = array('Content-Type:application/x-www-form-urlencoded');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['data' => $xml]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info["http_code"] == 200){
            if (preg_match("/</i", $result)) {
                $data['data'] = simplexml_load_string($result);
            } else {
                $data['error'] = $result;
                if($data['error'] == '') $data['error'] = 'Data not found!';
            }
        } else {
            $data['error'] = $info["http_code"];
        }
        curl_close($ch);
        return $data;
    }

    public function paymentCreate($transaction){
        $data = [
            'auth' => ['mch_id' => $this->mch_id, 'salt' => $this->salt, 'sign' => $this->sign],
            'urls' => ['good' => 'http://www.example.com/ok/', 'bad' => 'http://www.example.com/fail/', 'auto_redirect_good' => 1, 'auto_redirect_bad' => 1],
            'transactions' =>  $transaction,
            'trademark' => '{"ru":"apiexpert.net","ua":"apiexpert.net","en":"apiexpert.net"}',
            'lifetime' => 24,
            'lang' => 'ua'
        ];
        $xml = $this->arrayToXml($data,'<payment/>');
        return $this->getData($xml);
    }

      
    public function checkStatus($pid){
        /*
        status
        1 - платіж створений
        4 - платіж неуспішний
        5 - платіж успішний
        */
        $data = [
            'auth' => ['mch_id' => $this->mch_id, 'salt' => $this->salt, 'sign' => $this->sign],
            'action' => 'status',
            'pid' => $pid
        ];
        $xml = $this->arrayToXml($data,'<payment/>');
        $result = $this->getData($xml);
        if($result['data']) {
            return $result['data'];
        } else {
            return false;
        }
    }


    public function Refund($pid,$amount){
 
        $data = [
            'auth' => ['mch_id' => $this->mch_id, 'salt' => $this->salt, 'sign' => $this->sign],
            'action' => 'refund',
            'amount' => $amount*100,
            'info' => '{"refund_id": "'.$pid.'"}',
            'pid' => $pid
        ];

        $xml = $this->arrayToXml($data,'<payment/>');
        $result = $this->getData($xml);
        
        if(isset($result)){
            return true;
        } else {
            return ['error'=>'Ошибка возврата суммы в ipay.'];
        }

    }


    

}
?>