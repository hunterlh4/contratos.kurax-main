<?php

class BaseModel
{
	private $username;
	private $password;

	protected $url;

	protected function getUsername(){
		return $this->username;
	}

	protected function setUsername($username){
		$this->username = $username;
	}

	protected function getPassword(){
		return $this->password;
	}

	protected function setPassword($password){
		$this->password = $password;
	}

	protected function curl_get($uri, $return="", $opts = [], $headers=[]){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://'.$this->url.'/'.$uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		foreach ($opts as $key => $opt) curl_setopt($ch, $key, $opt);

		$headers[] = 'Content-Type: application/xml; charset="UTF-8"';
		$headers[] = 'Authorization: Basic '.base64_encode($this->getUsername().":".$this->getPassword());
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if($return === "xml") return simplexml_load_string(curl_exec($ch));
		if($return === "json") return json_encode(curl_exec($ch));
		return curl_exec($ch);
	}
}

?> 	 