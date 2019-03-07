<?php
namespace ActPHP\Socket;

class TcpClient{

	public $read_buf;

	public $fd;

	protected $_local_address;

	public $conn;

	public $recv;

	public $close;

	public function __construct($local_address)
	{

		$this->_local_address = $local_address;
	}

	public function conn(){

		$this->fd = stream_socket_client($this->_local_address,$errno,$errstr,10);
		if(!$this->fd){
			exit("socket create error {$errno} : $errstr");
		}
		if($this->conn){
			call_user_func($this->conn,$this,$this->fd);
		}
		$this->read_buf = fread($this->fd, 4096);
		if($this->read_buf !== "" && $this->read_buf !== false){
			if($this->recv){
				call_user_func($this->recv,$this,$this->fd,$this->read_buf);
			}
		}
	}

	public function close(){
		if($this->_client){
			if($this->close){
				call_user_func($this->close,$this,$this->read_buf);
				set_error_handler(function(){});
				fclose($this->_client);
				restore_error_handler();
			}
		}
	}

	public function send($fd,$str = "")
	{
		set_error_handler(function(){});
        $len = fwrite($fd, $str);
        restore_error_handler();
	}

}