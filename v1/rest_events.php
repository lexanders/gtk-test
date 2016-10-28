<?php

class events extends restObject {

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

		if($this->method=='POST'){
			//check fields to be filled
			if(!$this->object_id){
				$this->setError('Не выбрано событие.');
				return $this->getResponse(400);	
			}
		
			//email validation
			$_POST['user_email']=strtolower(trim($_POST['user_email']));
			if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
					$this->setError('Неверный формат email.');
					return $this->getResponse(400);	
			}
			
			//check if user already registered
			$arr=$db_connector->arrayPrepare($_POST,array('user_name','user_email','user_phone','user_sex','user_bday'));
			$user_db=$db_connector->getRow('SELECT * FROM users WHERE user_email="'.$_POST['user_email'].'" LIMIT 1');
			if(!$user_db) {
				//create new user
				if(!preg_match('/\d\d\.\d\d\.\d\d\d\d/u',$arr['user_bday'])) $arr['user_bday']='';
				if($arr['user_bday']) $arr['user_bday']=date("Y-m-d", strtotime($arr['user_bday']));
				$user_id=$db_connector->insert('users',$arr,true,'',true);
				if(!$user_id){
						$this->setError('Ошибка БД при добавлении пользователя');
						return $this->getResponse(500);
				}
				$user_db=$arr;	
				$user_db['user_id']=$user_id;	
			} 
				
			//check event registration 	
			$event_regsid=$db_connector->getOne('SELECT event_regs_id FROM event_regs WHERE event_regs_eventid='.(int)$this->object_id.' AND event_regs_userid='.(int)$user_db['user_id'].' LIMIT 1');
			if($event_regsid && $event_regsid>0){
					$this->setError('Пользователь уже зарегистрировался на это событие.');
					return $this->getResponse(400);		
				} else {
					$eventregs_id=$db_connector->insert('event_regs',array('event_regs_eventid'=>(int)$this->object_id,'event_regs_userid'=>(int)$user_db['user_id']),true,'',true);
					if($eventregs_id && $eventregs_id>0) {
						$this->response = $this->getMyVars(array('id'=>$eventregs_id));
						return $this->getResponse();
					} else {
						$this->setError('Ошибка при регистрации пользователя на событие');
						return $this->getResponse(500);
					}
				}
				
			} //end-POST
			
			if($this->method=='GET'){
				$i=0;
				$arr=array(
					array('id'=>++$i,'event_name'=>'«Мир»: Бьорн Торске'),
					array('id'=>++$i,'event_name'=>'«Big Stand-Up»'),
					array('id'=>++$i,'event_name'=>'Симфонический оркестр кинематографии'),
					array('id'=>++$i,'event_name'=>'Хор Московской консерватории'),
					array('id'=>++$i,'event_name'=>'Антон Лаврентьев'),
				);
				$this->response = $this->getMyVars($arr);
				return $this->getResponse();
			}//end-GET
	
	}
}
