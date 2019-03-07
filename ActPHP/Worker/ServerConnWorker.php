<?php
namespace ActPHP\Worker;

class ServerConnWorker extends \Worker{

	public $conn;

	public $server;

	public $fd;

	public $no;

	public function __construct($conn,$server,$fd){
		$this->conn   = $conn;
		$this->fd     = $fd;
		$this->no     = intval($fd);
		$this->server = $server;
	}

	public function run(){
		if(!$this->conn){
			$this->conn = function(){};
		}
		if(!$this->server){
			exit("no socket server ");
		}
		if(!$this->fd){
			exit("no conn resource ");
		}
		sleep(2);
		call_user_func($this->conn,$this->server,$this->fd);
		// echo "线程{$this->no}执行了\n";
	}

}