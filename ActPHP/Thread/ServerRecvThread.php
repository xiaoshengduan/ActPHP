<?php
namespace ActPHP\Thread;

class ServerRecvThread extends \Thread{

	public $recv;

	public $server;

	public $fd;

	public $no;

	public $buf;

	public function __construct($recv,$server,$fd,$buf=''){
		$this->recv   = $recv;
		$this->fd     = $fd;
		$this->no     = intval($fd);
		$this->server = $server;
		$this->buf   = $buf;
	}	

	public function run(){
		if(!$this->recv){
			$this->recv = function(){};
		}
		if(!$this->server){
			exit("no socket server ");
		}
		if(!$this->fd){
			exit("no conn resource ");
		}
		call_user_func($this->recv,$this->server,$this->fd,$this->buf);
	}

}