<?php
namespace Paypal;
include_once '../main.class.php';

use CoffeeBean;
class PayPal extends CoffeeBean\Main {
	public function __construct(){
		echo 'This Worked'; 
	}
}