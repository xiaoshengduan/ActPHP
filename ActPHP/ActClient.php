<?php
namespace ActPHP;

include_once "include.php";

class ActClient{


	private $_client;

	public $conn;
	
	public $recv;

	public $close;
	
	public function __construct($local_socket,$socket_type='tcp'){

		if(strtolower($socket_type) == 'tcp'){
			$this->_client = new \ActPHP\Socket\TcpClient($local_socket);
		}elseif (strtolower($socket_type) == 'udp') {
			$this->_client = new \ActPHP\Socket\UdpClient($local_socket);
		}elseif (strtolower($socket_type) == 'http') {
			$this->_client = new \ActPHP\Socket\HttpClient($local_socket);
		}elseif (strtolower($socket_type) == 'websocket') {
			$this->_client = new \ActPHP\Socket\WebSocketClient($local_socket);
		}
	}

	/**
	 * connect remote address
	 */
	public function conn(){

		$this->_client->conn  = $this->conn;
		$this->_client->recv  = $this->recv;
		$this->_client->close = $this->close;

		$this->_client->conn();
	}

	public function send($fd,$str = ""){
		$this->_client->send($fd,$str);
	}

	public function close($fd){
		$this->_client->close($fd);
	}
	

}
