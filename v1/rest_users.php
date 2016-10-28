<?php

class users extends restObject {

	/**
	 * user data
	 */
	public $name;
	public $email;
	public $phone;
	public $sex;
	public $bday;

	/**
	 * 
	 * @param string $method
	 * @param array $request
	 * @param string $file
	 */
	public function __construct($method, $request = null, $file = null) {
		parent::__construct($method, $request, $file);
	}



	public function def(){
		global $db_connector;
		
		if($this->method=='GET'){
			$arr=$db_connector->queryAssoc('SELECT * FROM users');
			$this->response = $this->getMyVars($arr);
			return $this->getResponse();
		}
		
	}

	
}
