<?php
error_reporting(E_ALL^E_NOTICE);
require_once __DIR__."/../ActPHP/ActClient.php";
use ActPHP\ActClient;

class My extends Thread{
	public $i;
	public function __construct($i){
		$this->i=$i;
	}
	public function run(){
	/*	$client = new ActClient("tcp://127.0.0.1:9000",'tcp');
		$client->conn();*/
		echo $this->i;
		$c = file_get_contents("http://127.0.0.1");
		$c = file_get_contents("http://127.0.0.1/");
		echo $this->i."--".strlen($c)."--\n";
	}
}
$time = microtime(1);
$my1 = new My(1);
$my2 = new My(2);
$my3 = new My(3);
$my4 = new My(4);
$my5 = new My(5);
$my6 = new My(6);
$my1->start();
$my1->join();

$my2->start();
$my2->join();

$my3->start();
$my3->join();

$my4->start();
$my4->join();

$my5->start();
$my5->join();

$my6->start();
$my6->join();

while (true) {
	$is_run1 = $my1->isRunning();
	$is_run2 = $my2->isRunning();
	$is_run3 = $my3->isRunning();
	$is_run4 = $my4->isRunning();
	$is_run5 = $my5->isRunning();
	$is_run6 = $my5->isRunning();
	if($is_run1 === false && $is_run2 ===false && $is_run3 === false && $is_run4 === false && $is_run5===false && $is_run6 === false ){
		echo "===结束===";
		$diff = microtime(1)-$time;
		echo "==".$diff."==\n";
		break;
	}
}




