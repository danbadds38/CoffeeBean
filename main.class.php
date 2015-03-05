<?php
namespace CoffeeBean;

class Main {
	private $WORKING_DIR = __DIR__;
	private $CONFIG_NAME = 'config.ini';
	private $MAIN_CLASS  = 'main.class.php';
	public $config;

	public function __construct(){
		$this->getConfig();
		$this->debug($this->config);
		/**
		 *  Include Necessary Application Classes
		 */
		 //spl_autoload_register('manualLoadClasses'); #Should Be Deprecated In Favour Of Dynamic Inclusion.
		 spl_autoload_register('autoLoadClasses');
	}
	
	private function getConfig(){
		$this->config = parse_ini_file($this->WORKING_DIR.'/'.$this->CONFIG_NAME, TRUE);
		return $this->config;
	}
	
	public function debug($arg,$message = null) {
        $bt =  debug_backtrace();
        $trace_location = "Calling file: ". $bt[0]['file'] . ' line  '. $bt[0]['line'] . ' : ';

		if($this->config['CoffeeBean']['debug'] == true) {
			echo '<pre>'; print_r($arg); echo '</pre>';
		}

		$message = ($message != null) ? $message : $trace_location;
		return error_log($message . json_encode($arg) . "\n",3,$this->WORKING_DIR.'/debug.log');
	}
	
	/**
	 *  @deprecated: Used to load classes used by CoffeeBean
	 */
	public function manualLoadClasses(){
		/**
		 *  PayPal API/SDK Integration
		 */
		include_once $this->WORKING_DIR.'/PayPal/'.$this->MAIN_CLASS;
		
		/**
		 *  Module For Helping With Requests: POST | GET | REQUEST | FILE | RAW
		 */
		include_once $this->WORKING_DIR.'/RequestHelper/'.$this->MAIN_CLASS;
		
		/**
		 *  Module For Misc. Helpful Tools.
		 *  @Browser : Sub-Module For [BagaTrix] Used To Determine End-User Browser Information
		 */
		include_once $this->WORKING_DIR.'/BagaTrix/'.$this->MAIN_CLASS;
		include_once $this->WORKING_DIR.'/BagaTrix/Browser/'.$this->MAIN_CLASS;
	}
	/**
	 *  @autoloader : used to {auto}load classes used by CoffeBeen
	 */
	public function autoLoadClasses() {
		foreach($this->config['EnabledModules'] as $key => $value) {
			($key != true) ? continue : include_once $this->WORKING_DIR.'/'.$key.'/'.$this->MAIN_CLASS;
		}
	}
}
