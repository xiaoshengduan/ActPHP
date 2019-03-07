<?php
error_reporting(E_ALL^E_NOTICE);

require_once __DIR__."/../ActPHP/ActServer.php";
use ActPHP\ActServer;

$act = new ActServer("tcp://0.0.0.0:9000");

$act->conn = function($server,$fd){
	$int_fd = intval($fd);
	echo $int_fd."开始执行--".'\n'."--";	
	$start =  microtime(1);
	echo "当前连接数量".count($server->conn_fds)."\n";
	$end = microtime(1);
	$diff = $end-$start;
	echo $int_fd."执行结束，耗时".$diff."秒";
};

$act->recv = function($server,$fd,$data){
	echo "读取到".(int)$fd."的消息--".$data."--\n";
	$server->send($fd,"hello actPHP");
	if(trim($data) == 'quit'){
		$server->close($fd);
	}
};

$act->close = function($server,$fd){
	$fd = intval($fd);
	echo "the {$fd} closed";
	echo "当前连接数量".count($server->conn_fds);
};

$act->run();
