<?php
namespace Paypal;
include_once '../main.class.php';

use CoffeeBean;
//use OAuth\OAuth2\Service\Paypal;

class Main extends CoffeeBean\Main {
	public function __construct(){
        parent::__construct();
        $this->ACCESS_TOKEN = $this->getAccessToken($this->config['PayPal']['app_id'], $this->config['PayPal']['app_secret']);
        $this->debug($this->ACCESS_TOKEN);
	}

    public function getAccessToken($client_id , $secret){
        $this->debug($client_id);
        $this->debug($secret);
        $curl = shell_exec('curl -v https://api.sandbox.paypal.com/v1/oauth2/token \
              -H "Accept: application/json" \
              -H "Accept-Language: en_US" \
              -u "'.$client_id.':'.$secret.'" \
              -d "grant_type=client_credentials"');
        $json = json_decode($curl,true);
        return $json['access_token'];
    }

    public function creditCardPayment($args){
        $url = $this->config['PayPal'][$this->config['PayPal']['status'].'_payment_url'];
        $curl = shell_exec('
        curl -v '.$url.' \
            -H "Content-Type:application/json" \
            -H "Authorization: Bearer '.$this->ACCESS_TOKEN.'" \
            -d \'{
              "intent": "sale",
              "payer": {
                        "payment_method": "credit_card",
                "funding_instruments": [
                  {
                      "credit_card": {
                      "number": "'.$args['number'].'",
                      "type": "'.$args['type'].'",
                      "expire_month": '.(int)$args['expire_month'].',
                      "expire_year": '.(int)$args['expire_year'].',
                      "cvv2": '.(int)$args['cvv2'].',
                      "first_name": "'.$args['first_name'].'",
                      "last_name": "'.$args['last_name'].'"
                    }
                  }
                ]
              },
              "transactions": [
                {
                    "amount": {
                    "total": "'.$args['total'].'",
                    "currency": "USD"
                  },
                  "description": "1 Month Payment: TankersNow.com."
                }
              ]
            }\'
        ');
        return json_decode($curl, TRUE);
    }
}
use PayPal;
class Test extends PayPal\Main{
    public function __construct(){
        parent::__construct();
        $ccArgs = array(
          'number' => $this->config['PayPal']['cc_number'],
            'type' => $this->config['PayPal']['cc_type'],
            'expire_month' => $this->config['PayPal']['cc_expire_month'],
            'expire_year' => $this->config['PayPal']['cc_expire_year'],
            'cvv2' => $this->config['PayPal']['cc_cvv2'],
            'first_name' => $this->config['PayPal']['cc_first_name'],
            'last_name' => $this->config['PayPal']['cc_last_name'],
            'total' => $this->config['PayPal']['cc_total']
        );
        $this->debug($this->creditCardPayment($ccArgs));
    }
}