<?php
require_once '/var/www/html/sys/HikVision/BaseModel.php';


class HikVision extends BaseModel{

	public function __construct($url, $username=false, $password=false)
	{
		$this->url = $url;
		$this->setUsername($username ?: "admin");
		$this->setPassword($password ?: "atdvrpass1");
	}

	public function getSystemStatus(){
		return $this->curl_get("ISAPI/System/status", "xml");
	}

	public function testResource(){
		return $this->curl_get("ISAPI/System/Video/inputs/channels/1/tamperDetection", "xml");
	}

}

?>