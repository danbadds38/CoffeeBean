<?php
namespace CoffeeBean;

class Main {
	private $WORKING_DIR = __DIR__;
	private $CONFIG_NAME = 'config.ini';
	
	public function __construct(){
		$this->getConfig();
		$this->debug($this->config);
	}
	
	private function getConfig(){
		$this->config = parse_ini_file($this->WORKING_DIR.'/'.$this->CONFIG_NAME, TRUE);
		return $this->config;
	}
	
	public function debug($arg,$message = null) {
		if($this->config['CoffeeBean']['debug'] == true) {
			echo '<pre>'; print_r($arg); echo '</pre>';
		}
		$message = ($message != null) ? $message : 'Debug Dump: ';
		return error_log($message . json_encode($arg),3,$this->WORKING_DIR.'/debug.log');
	}
}
