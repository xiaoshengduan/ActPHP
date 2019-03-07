<?php
namespace ActPHP\Socket;

use ActPHP\Mode\Event;
use ActPHP\Mode\Select;


class HttpServer{

	public $mul_threads;

	public $c_fd;

	public $conn_fds = [];

	public $info;

	protected $_event;

	protected $_evet_base;

	protected $_local_address;

	public $conn;

	public $recv;

	public $close;

	public $is_use_thread = false; //是否开启多线程,默认关闭


	public $reponse_headers  = array();

	public $request_headers  = array();

	public $request_method   = 'GET';

	public $request_uri      = '';

	public $request_protocol = '';

	public $request_query_string = '';

	public $request_content_type = '';

	public $request_query_data = array();

	public $request_post_data  = array();

	public $request_files_data  = array();

	protected $_allow_methods = array(
		'GET', 
		'POST',
		'PUT', 
		'DELETE', 
		'HEAD', 
		'OPTIONS'
	);

	public function __construct($local_address)
	{

		$this->_local_address = $local_address;
		$this->_event         = new Event();

	}


	/**
	 * run() -> accept -> base read -> base write -> recv [user] 
	 */
	public function run()
	{


		//设置socket并loop
		$this->c_fd = stream_socket_server($this->_local_address,$errno,$errstr);


		if(!$this->c_fd){
			exit("socket create error {$errno} : $errstr");
		}

		$socket = socket_import_stream($this->c_fd);
		@socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);  //SO_KEEPALIVE 保持连接检测对方主机是否崩溃，避免（服务器）永远阻塞于TCP连接的输入
        @socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1); //最小化传输延迟，而不是追求最小化报文数量


        stream_set_blocking($this->c_fd, 0);

		$flags = \Event::READ;

        $add_res = $this->_event->add($this->c_fd, $flags, [$this, 'accept'],$this->c_fd);
        if(!$add_res){
        	exit("event add error");
        }

        echo "loop start ... \n";
        $this->_event->loop(); //Wait for events to become active, and run their callbacks. If the $this->fd become active ,will run this->accept
        echo "loop exit ... \n";
	}

	public function accept($fd)
	{

		$acc_fd = stream_socket_accept($fd, 0, $perrname);

        if (!$acc_fd) {
        	$fd_key = intval($fd);
        }
        $acc_fd_key = intval($acc_fd);

        $this->conn_fds[$acc_fd_key] = $acc_fd;

        
        $socket = socket_import_stream($acc_fd);
		socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);  //SO_KEEPALIVE 保持连接检测对方主机是否崩溃，避免（服务器）永远阻塞于TCP连接的输入
        socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1); //最小化传输延迟，而不是追求最小化报文数量
        stream_set_blocking($acc_fd, 0);
        stream_set_read_buffer($acc_fd, 0);
        
        $flags = \Event::READ;
        $res = $this->_event->add($acc_fd, $flags, [$this, 'read'],$acc_fd);
        if($res === false){
        	echo "添加事件失败\n";
        }

        if($this->conn){  //执行用户回调
        	call_user_func($this->conn,$this,$acc_fd);
        }
        
	}

	public function read($fd)
	{
		$time_start = microtime(1);
		$buf = fread($fd, 8192);  //读取一个块

		$socket = socket_import_stream($fd);
		@socket_set_option($socket, SOL_TCP, TCP_QUICKACK, 1); //解决40s延时问题
		echo TCP_NODELAY;
        if ($buf === '' || $buf === false) {
           $this->_event->del($fd,\Event::READ);
           fclose($fd);
           unset($this->conn_fds[(int)$fd]);
           return;
        }

        $this->getHttpRequestBuf($buf,$fd);
        // call user recv

        $time_end = microtime(1);
        if($this->recv){
        	call_user_func($this->recv,$this,$fd,$buf);
        }
        return $buf;
	}

	public function send($fd,$str = "")
	{
		$response_buf = $this->getHttpResponseBuf($str);
		set_error_handler(function(){});
        fwrite($fd, $response_buf);
        restore_error_handler();
	}

	public function close($fd)
	{	
		set_error_handler(function(){});
        fclose($fd);
        restore_error_handler();

		$acc_fd_key = (int)$fd;
		if(isset($this->conn_fds[$acc_fd_key])){
			unset($this->conn_fds[$acc_fd_key]);
		}
		$this->_event->del($fd,\Event::READ);
		$this->_event->del($fd,\Event::WRITE);
		
		if($this->close){
			call_user_func($this->close,$this,$fd);
		}
	}

	/**
	 * 获取按照当前协议解析后的数据
	 */
	public function getHttpRequestBuf($buf='',$fd)
	{
		if(!$buf){
			$thie->errorResponse();
		}
		list($headers_str,$content) = explode("\r\n\r\n", $buf,2);
		if(empty($headers_str)){
			$this->send();
			echo "error request\r\n";
			$this->send($fd,"error request1\r\n");
			return '';
		}
		$headers  = explode("\r\n", $headers_str);
		if(count($headers) === 0){
			echo "error request\r\n";
			$this->send($fd,"error request2\r\n");
			return '';
		}
		$req_top_str  = $headers[0];
		list($this->request_method,$this->request_uri,$this->request_protocol)     = explode(" ", $req_top_str);
		$this->request_method = strtoupper($this->request_method);

		if(!in_array($this->request_method, $this->_allow_methods)){
			echo "error request\n";
			$this->send($fd,"error request3\r\n");
			return '';
		}

		array_shift($headers);
		if(count($headers) > 0){
			foreach ($headers as $key => $header) {
				list($h_key,$h_value) = explode(":", $header);
				$keys = explode("-", $h_key);
				if($keys){
					$h_key = ucfirst($keys[0])."-".ucfirst($keys[1]);
				}else{
					$h_key = ucfirst($h_key);
				}
				$this->request_headers[$h_key] = ltrim($h_value,' ');
			}
		}

		//获取请求内容
		$this->request_query_string = parse_url($this->request_uri,PHP_URL_QUERY);

		if(strpos($this->request_headers["Content-Type"],"application/json")){
			$this->request_post_data = json_decode($content,true);
		}elseif(strpos($this->request_headers["Content-Type"], "application/x-www-form-urlencoded")){
			parse_str($content,$this->request_post_data);
		}elseif (strpos($this->request_headers["Content-Type"],"multipart/form-data")) {
			$this->_fillFiles($content);
		}
	}

	/**
	 * 解析文件信息添加到数组中
	 */
	private function _fillFiles(){

		$this->request_files_data = array();
	}

	/**
	 * 将要发送给客户端的数据库解析成正确的当前协议的响应数据
	 */
	public function getHttpResponseBuf($content){

		$header_str = '';
		if(!isset($this->reponse_headers['http-code'])){
			$header_str.= "HTTP/1.1 200 OK\r\n";
		}else{
			$header_str.= $this->reponse_headers['http-code'];
			unset($this->reponse_headers['http-code']);
		}

		if(!isset($this->response_headers['content-type'])){
			$header_str .= "Content-Type: text/html;charset=utf-8\r\n";
			unset($this->reponse_headers['Content-Type']);
		}
		if(count($this->response_headers)){
			foreach ($this->response_headers as $res_key => $res_value) {
				$header_str.= $res_value."\r\n";
			}
		}
		return $header_str."\r\n".$content;
	}

}