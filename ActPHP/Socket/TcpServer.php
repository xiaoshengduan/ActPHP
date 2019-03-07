<?php
namespace ActPHP\Socket;

use ActPHP\Mode\Event;
use ActPHP\Mode\Select;


class TcpServer{

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
        	echo "socket {$fd} accept error ";
        }
        echo "accept ...\n";
        $acc_fd_key = intval($acc_fd);

        $this->conn_fds[$acc_fd_key] = $acc_fd;

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

		$buf = fread($fd, 8192);  //读取一个块

        if ($buf === '' || $buf === false) {
           $this->_event->del($fd,\Event::READ);
           fclose($fd);
           unset($this->conn_fds[(int)$fd]);
           return;
        }

        // 如果指定了协议，按照协议规则解析读取到的数据

        // 如果buf中收到关闭指令，执行关闭close($fd);

        // call user recv
        if($this->recv){
        	call_user_func($this->recv,$this,$fd,$buf);
        }
        return $buf;
	}

	public function send($fd,$str = "")
	{
		set_error_handler(function(){});
        fwrite($fd, $str);
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

		
		echo "close  {$acc_fd_key}  end ...\n";		
	}


}