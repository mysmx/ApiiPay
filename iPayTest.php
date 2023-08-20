<?php
  include_once('api_ipay.class.php');
  
  $api = new ApiiPay();
  $api->init(0000,'0000000000000000000');
  $pid = 1;
  $do = 'paymentCreate';
  
  switch ($do) {
    //create one check for 2 legal entity
    case 'paymentCreate':
       $transaction = [];
       $smchs = [1,2]; //Юридична особа, на користь якої здіюйснюється операція
       foreach ($smchs as $smch_id) {
        $key = 'transaction_'.$smch_id;
        $transaction[$key] = [
              'amount' => 5500,
              'currency' => 'UAH',
              'desc' => 'Покупка товара/услуги',
              'info' => '{"dogovor":123456}',
              'smch_id' => $smch_id
        ];
      }
      $result = $api->paymentCreate($transaction);
      var_dump($result);
    break;
    case 'checkStatus':
        $result = $api->checkStatus($pid);
    break;
    case 'Refund':
      $result = $api->Refund($pid,10);
  break;
  }
  

?>
