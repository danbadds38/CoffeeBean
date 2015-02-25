<?php
/**
 * Author: Daniel P. Baddeley JR , <danbadds38@gmail.com>
 *
 * @info: Alter to use config values for Unique_id && Table to Pull From
 */
@session_start();
use PayPal\Main as PayPal;
use CoffeeBean\RequestHelper as RequestHelper;
include(__DIR__.'/../../PayPal/main.class.php');
include(__DIR__ . '/../../RequestHelper/main.class.php');
$PayPal = new PayPal();
$api = new RequestHelper();

$data = $api->fetchRequest('RAW');

$date = date('Y-m-d');
if(is_array($data['data'])){
    $contact = $api->query("SELECT unique_id , cc_number, cc_type , cc_expire_month , cc_expire_year , cc_cvv2 , cc_first_name , cc_last_name , cc_active , account_type, last_payment FROM tank_main.contacts WHERE unique_id='{$_SESSION['USER']['unique_id']}'");
    if($contact['data']['account_type'] == 'FREE') {
        $ccArgs = array(
            'number' => $data['data']['cc_number'],
            'type' => $data['data']['cc_type'],
            'expire_month' => $data['data']['cc_expire_month'],
            'expire_year' => $data['data']['cc_expire_year'],
            'cvv2' => $data['data']['cc_cvv2'],
            'first_name' => $data['data']['cc_first_name'],
            'last_name' => $data['data']['cc_last_name'],
            'total' => $PayPal->config['PayPal']['cc_total']
        );
        $payment = $PayPal->creditCardPayment($ccArgs);
        $account_type = ($payment['state'] === 'approved') ? 'PRO' : 'FREE';
        $cc_active = ($payment['state'] === 'approved') ? 1 : 0;
        $update_account = $api->query("UPDATE tank_main.contacts SET account_type='$account_type' , last_payment='$date', cc_active='$cc_active' WHERE unique_id='{$_SESSION['USER']['unique_id']}'");
        $insert_billing_record =$api->query("INSERT INTO tank_main.pp_billing (transaction_id, state , payment_id , unique_id , payment , account_type ) VALUES (
                    '{$payment['id']}',
                    '{$payment['state']}',
                    '{$payment['transactions'][0]['related_resources'][0]['sale']['id']}',
                    '{$_SESSION['USER']['unique_id']}',
                    '{$PayPal->config['PayPal']['cc_total']}',
                    '{$account_type}'
        )");
        if($cc_active == true) {
            $update_account = $api->query("UPDATE tank_main.contacts SET cc_number='{$data['data']['cc_number']}',
                cc_type='{$data['data']['cc_type']}',
                cc_expire_month='{$data['data']['cc_expire_month']}',
                cc_expire_year='{$data['data']['cc_expire_year']}',
                cc_cvv2='{$data['data']['cc_cvv2']}',
                cc_first_name='{$data['data']['cc_first_name']}',
                cc_last_name='{$data['data']['cc_last_name']}'
                WHERE unique_id='{$_SESSION['USER']['unique_id']}'");
        }
    } else {
        $update_account = $api->query("UPDATE tank_main.contacts SET cc_number='{$data['data']['cc_number']}',
                cc_type='{$data['data']['cc_type']}',
                cc_expire_month='{$data['data']['cc_expire_month']}',
                cc_expire_year='{$data['data']['cc_expire_year']}',
                cc_cvv2='{$data['data']['cc_cvv2']}',
                cc_first_name='{$data['data']['cc_first_name']}',
                cc_last_name='{$data['data']['cc_last_name']}'
                WHERE unique_id='{$_SESSION['USER']['unique_id']}'");
    }
}
$PayPal->debug($payment);
$PayPal->debug($update_account);