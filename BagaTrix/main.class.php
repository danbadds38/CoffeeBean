<?php
namespace BagaTrix;
include_once __DIR__ . '/../main.class.php';

use CoffeeBean;

class Main extends CoffeeBean\Main {
  public function __construct(){
    parent::__construct();
    /** Some Extra Code Here To Controll The Extensions **/
  }
}
