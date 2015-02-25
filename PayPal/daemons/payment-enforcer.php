<?php
/**
 * Author: Daniel P. Baddeley JR , <danbadds38@gmail.com>
 *
 * @info: Alter to use config values for Unique_id && Table to Pull From
 */
use PayPal\Main as PayPal;
use CoffeeBean\RequestHelper as RequestHelper;
include(__DIR__.'/../../PayPal/main.class.php');
include(__DIR__ . '/../../RequestHelper/main.class.php');
$PayPal = new PayPal();
$api = new RequestHelper();

$date = date('Y-m-d');
$contactList = $api->query("SELECT unique_id , email, cc_number, cc_type , cc_expire_month , cc_expire_year , cc_cvv2 , cc_first_name , cc_last_name , cc_active , account_type, last_payment FROM tank_main.contacts WHERE cc_active=1 OR account_type='PRO'");
for($i = 0; $i < $contactList['count']; $i ++ ){
    if($contactList['count'] === 1) {
        $contactList['data'][0] = $contactList['data'];
    }

    if(strtotime($contactList['data'][$i]['last_payment']) < strtotime('1 month ago')){
        /** Check if CC is still active - if not lower account type */
        if($contactList['data'][$i]['cc_active'] != '1') {
            $update_account = $api->query("UPDATE tank_main.contacts SET account_type='FREE' , last_payment='$date' WHERE unique_id='{$contactList['data'][$i]['unique_id']}'");
            continue;
        }

        echo 'Contact: '.$contactList['data'][$i]['unique_id'].'- Needs To Pay'."<br />";
        $ccArgs = array(
            'number' => $contactList['data'][$i]['cc_number'],
            'type' => $contactList['data'][$i]['cc_type'],
            'expire_month' => $contactList['data'][$i]['cc_expire_month'],
            'expire_year' => $contactList['data'][$i]['cc_expire_year'],
            'cvv2' => $contactList['data'][$i]['cc_cvv2'],
            'first_name' => $contactList['data'][$i]['cc_first_name'],
            'last_name' => $contactList['data'][$i]['cc_last_name'],
            'total' => $PayPal->config['PayPal']['cc_total']
        );
        $payment = $PayPal->creditCardPayment($ccArgs);
        $account_type = ($payment['state'] === 'approved') ? 'PRO' : 'FREE';
        $account_active = ($payment['state'] === 'approved') ? '1' : '0';
        $update_account = $api->query("UPDATE tank_main.contacts SET cc_active='$account_active', account_type='$account_type' , last_payment='$date' WHERE unique_id='{$contactList['data'][$i]['unique_id']}'");
        $insert_billing_record =$api->query("INSERT INTO tank_main.pp_billing (transaction_id, state , payment_id , unique_id , payment , account_type ) VALUES (
                    '{$payment['id']}',
                    '{$payment['state']}',
                    '{$payment['transactions'][0]['related_resources'][0]['sale']['id']}',
                    '{$contactList['data'][$i]['unique_id']}',
                    '{$PayPal->config['PayPal']['cc_total']}',
                    '{$account_type}'
        )");
        if($payment['state'] === 'approved'){
            mail($contactList['data'][$i]['email'],'Payment Confirmation: TankersNow.com','Your Account Has Been Successfully Billed For 1/month Recurring Subscription. '."\n"."Your receipt# is: ".$payment['transactions'][0]['related_resources'][0]['sale']['id']);
        } else {
            mail($contactList['data'][$i]['email'],'Payment Denied: TankersNow.com','There was an issue when processing your monthly payment, please sign back into <a href="https://tankersnow.com" target="_blank">https://tankersnow.com</a> to update your payment methods' );
        }
//        $PayPal->debug($payment);
//        $PayPal->debug($update_account);
//        $PayPal->debug($insert_billing_record);
    }
}