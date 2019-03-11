<?php
namespace ActPHP\Lib;

class DB{

	public function __construct(){

		$host      = $config['host'];
		$dbname    = $config['dbname'];
		$username  = $config['username'];
		$password  = $config['password'];

		try{

			$this->dbh = new PDO("mysql:host={$host};dbname={$dbname}",$username,$password);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
			$this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
			$this->dbh->exec("set names utf8");

		}catch(PDOException $err){
			echo $err->getMessage();
		}

	}

	
	public function insert($table = '',$insert_data = array()){

		$insert_sql = "INSERT INTO ".$table." ( ";
		$fields = array_keys($insert_data);
		$values = array_values($insert_data);
		$insert_sql = $insert_sql . implode(', ', $fields)." ) VALUES (".implode(',', array_fill(0, count($insert_data), '?'))." )";
		$stmt = $this->dbh->prepare($insert_sql);
		$stmt->execute($values);
		return $this->dbh->lastInsertId();

	}

	public function delete($table = '',$where = array()){

		$delete_sql = "delete  from " .$table." where ";

		$pre_values = array();
		$arr = $this->join_where($delete_sql,$pre_values,$where);
		$delete_sql = $arr[0];
		$pre_values = $arr[1];
		$stmt = $this->dbh->prepare($delete_sql);
		$stmt->execute($pre_values);
		return $stmt->rowCount();
	}
	

	public function update($table = "",$where = array(),$new_data = array()){

		$update_sql = "UPDATE ".$table." SET ";
		$pre_values = array();
		foreach ($new_data as $n_key => $n_value) {
			$n_key = $this->reborn_field($n_key);
			$update_sql = $update_sql . $n_key. " = ? ,";
			$pre_values[] = $n_value;
		}
		$update_sql = rtrim(',');
		$arr = $this->join_where($update_sql,$pre_values,$where);
		$update_sql = $arr[0];
		$pre_values = $arr[1];
		$stmt = $this->dbh->prepare($update_sql);
		$stmt->execute($pre_values);
		return $stmt->rowCount();
	}

	public function find($table = "",$where = array()){
		
	}

	public function batch_insert(){

	}

	public function batch_update(){

	}

	public function start_trans(){
		$this->dbh->beginTransaction();
	}

	public function rollback(){
		$this->dbh->rollBack();
	}

	public function commit(){
		$this->dbh->rollBack();
	}

	private function reborn_field($field = ''){
		if(strpos($field, '.') === false){
			return '`'.$field.'`';
		}else{
			$field_arr =  array_walk(function($k,$v){return '`'.$v.'`'},explode('.', $field));
			return implode('.', $field_arr);
		}
	}

	private function join_where($sql = "",$pre_values = array(),$where = array()){

		foreach ($where as $w_key => $w_value) {
			$w_key = $this->reborn_field($w_key);

			if(is_string($w_value) || is_numeric($w_value)){
				$sql = $sql . $w_key . " = ? ";
				$pre_values[] = $w_value;
			}
			if(is_array($w_value)){
				$w_type = $w_value[0];
				$w_data = $w_value[1];
				$w_connect = $w_value[2]?$w_value[2]:" AND ";
				if(in_array(strtolower($w_type), array('>','<','!=','<>','like','>=','<='))){
					if(is_string($w_data) || is_numeric($w_data)){
						$sql = $sql . $w_connect . $w_key . $w_type." ? ";
						$pre_values[] = $w_data;
					}
				}
				if(strtolower($w_type) == 'in'){
					if(is_array($w_data)){
						$sql = $sql . $w_connect .$w_key . " IN (".implode(',', array_fill(0, count($w_data), '?')).") ";
						$pre_values = array_merge($pre_values,$in_datas);
					}
				}
				if(strtolower($w_type) == 'between'){
					if(is_array($w_data) && count($w_data) == 2){
						$sql = $sql . $w_connect . $w_key . " BETWEEN ( ? , ?) ";
						$pre_values = array_merge($pre_values,$in_datas);
					}
				}
				if(strtolower($w_type) == 'exp'){
					if(is_array($w_data)){
						$sql = $sql . $w_connect .$w_key . $w_data;
					}
				}
			}
		}
		return array($sql,$pre_values);
	}

}


	