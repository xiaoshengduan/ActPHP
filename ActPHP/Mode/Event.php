<?php
namespace ActPHP\Mode;

class Event{

	protected  $_eventbase;
	protected  $_events_arr = [];
	protected  $_signal_events_arr = [];

	public function __construct(){
		$this->_eventbase = new \EventBase();
	}

	public function add($fd, $flag, $func, $args){

		if($flag === \Event::READ){
			$flag = \Event::READ | \Event::PERSIST;
		}elseif($flag === \Event::WRITE){
			$flag = \Event::WRITE | \Event::PERSIST;
		}else{
			$flag = \Event::READ | \Event::PERSIST;
		}

		$event = new \Event($this->_eventbase,$fd,$flag,$func,$fd);
		if(!$event){
			return false;
		}
		$add_res = $event->add();
		if(!$add_res){
			return false;
		}
		
		$fd_key = (int)$fd;
		$flag   = (int)$flag;
		
		$this->_events_arr[$fd][$flag] = $event;
		return true;
	}

	public function addSignal($fd,$flag,$func,$args = []){

		$fd_key = (int)$fd;
        $signal_event = \Event::signal($this->_eventbase, $fd, $func,$args);
        if(!$signal_event){
        	return false;
        }
        $signal_add_res = $signal_event->addSignal();
        if(!$signal_add_res){
        	return false;
        }
        $this->_signal_events_arr[$fd_key] = $event;
        return true;
	}

	public function del($fd, $flag){
		if($flag === \Event::READ){
			$flag = \Event::READ | \Event::PERSIST;
		}elseif($flag === \Event::WRITE){
			$flag = \Event::WRITE | \Event::PERSIST;
		}else{
			$flag = \Event::READ | \Event::PERSIST;
		}
		
		$fd   = intval($fd);
		$flag = intval($flag);
		if(isset($this->_events_arr[$fd][$flag])){
			$res = $this->_events_arr[$fd][$flag]->del();  // delete pending
			
		    unset($this->_events_arr[$fd][$flag]);
		}
		
		if(count($this->_events_arr[$fd]) == 0){
			unset($this->_events_arr[$fd]);
		}
	}

	public function delSignal(){

		$fd   = intval($fd);
		$flag = intval($flag);
		if(isset($this->_signal_events_arr[$fd][$flag])){
			$this->_signal_events_arr[$fd][$flag]->delSignal();  // delete pending
		    unset($this->_signal_events_arr[$fd][$flag]);
		}
		
		if(count($this->_signal_events_arr[$fd]) == 0){
			unset($this->_signal_events_arr[$fd]);
		}
	}

	public function loop(){
		$this->_eventbase->loop();
	}

}