<?php
namespace ActPHP\Thread;

class ServerLoopThread extends \Thread{

	public $event;

	protected $_count;

	public function __construct($event,$count){
		$this->event = $event;
		$this->_count = $count;
	}

	public function run(){
		echo $this->_count." loop thread starting...\n";
		$this->event->loop();
	}

}