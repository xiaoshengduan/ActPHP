<?php
namespace ActPHP;

include_once "include.php";

class ActServer{


	private $_server;

	private $_pid_file = __CLASS__;

	private $_commands = array();

	public $is_demonize = true;
	
	public $conn;
	
	public $recv;

	public $close;

	
	public function __construct($local_socket,$socket_type='tcp'){

		$this->_pid_file = __DIR__.'/act_pid.log';
		$this->_commands = array('start','stop','reload','info');

		if(strtolower($socket_type) == 'tcp'){
			$this->_server = new \ActPHP\Socket\TcpServer($local_socket);
		}elseif (strtolower($socket_type) == 'udp') {
			$this->_server = new \ActPHP\Socket\UdpServer($local_socket);
		}elseif (strtolower($socket_type) == 'http') {
			$this->_server = new \ActPHP\Socket\HttpServer($local_socket);
		}elseif (strtolower($socket_type) == 'websocket') {
			$this->_server = new \ActPHP\Socket\WebSocketServer($local_socket);
		}

		
	}

	/*public function openThread(){
		$this->_server->is_use_thread = true;
	}*/


	/**
	 * 运行该server
	 */
	public function run(){

		$this->_checkSys();
		$this->_parseArgv();
				
	}

	private function _parseArgv(){
		 global $argv;

		 $server_name = $argv[0];

		 if(!isset($argv[1]) || !in_array($argv[1], $this->_commands)){ //没有命令，默认为启动
		 	 $this->start();
		 }else{
			 $m_1 = trim($argv[1]);

			 $m_2 = isset($argv[2])?trim($argv[2]):false;

			 if($m_1 == 'start'){
			 	if($m_2 !== false && $m_2 == '-test'){
			 		$this->is_demonize = false;
			 	}
			 	$this->start();
			 }
			 if($m_1 == 'stop'){
			 	$this->stop();
			 }
		 }

	}

	/**
	 * 启动服务
	 */
	public function start(){
		if($this->is_demonize){  
			$pid = $this->_demonize();
			if($pid === 0 ){
				$this->_message(" It is already running ");
			}else{ //子进程创建完毕,运行服务端代码
				$this->_server->conn  = $this->conn;
				$this->_server->recv  = $this->recv;
				$this->_server->close = $this->close;
				$this->_server->run();
			}
		}else{  // 不创建子进程，直接在父进程运行服务端代码
				$this->_server->conn  = $this->conn;
				$this->_server->recv  = $this->recv;
				$this->_server->close = $this->close;
				$this->_server->run();
		}
	}

	/**
	 * 关闭服务
	 */
	public function stop(){


		$conn_fds = $this->_server->conn_fds;
		foreach ($conn_fds as $key => $fd) {
			$this->_server->close($fd); //关闭所有连接
		}

		if($this->_getPid() > 0){  //如果进程存在
			if (file_exists($this->_pid_file)) { //杀死进程
	            $pid = (int)file_get_contents($this->_pid_file);
	            posix_kill($pid, 9);
	            unlink($this->_pid_file);
	            echo 'Stoped' . PHP_EOL;
	        }else{
	        	echo 'Stoped' . PHP_EOL;
	        }
		}else{
			 echo "Not Running" . PHP_EOL;
		}
		
	}

	private function _checkSys(){

		if(php_sapi_name() != 'cli'){
			die("only could run in cli mode");
		}
		if(!extension_loaded('pcntl')){
			die("pcntl extension is required");
		}

	}

	/**
	 * 创建守护进程
	 */
	private function _demonize(){
		

		if($this->_getPid() > 0){  //进程已存在
			return 0;
		}

		$pid = pcntl_fork();
		if($pid == -1){
			die("create demonize failed");
		}elseif($pid){
			file_put_contents($this->_pid_file, $pid);
			exit();
		}

		$sid = posix_setsid();
		if($sid < 0){
			exit();
		}

		$pid = pcntl_fork();
		if($pid == -1){
			die("create demonize failed");
		}elseif($pid){
			file_put_contents($this->_pid_file, $pid);
			exit();
		}
		
		return 1;
	}

	/**
	 * 获取当前运行的进程
	 */
	private function _getPid(){

		if(!file_exists($this->_pid_file)){
			return 0;
		}

		$pid = intval(trim(file_get_contents($this->_pid_file)));
		if(posix_kill($pid, 0) && $pid !=0){  //检测进程是否已存在，如果存在返回该进程的pid
			return $pid;
		}else{
			unlink($this->_pid_file);
			return 0;
		}

	}

	private function _message($message) {
        printf("%s  %d %d  %s" . PHP_EOL, date("Y-m-d H:i:s"), posix_getpid(), posix_getppid(), $message);
    }

}
