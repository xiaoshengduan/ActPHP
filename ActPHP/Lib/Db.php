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

	//delete from user where name = 'cc' and id =3 and addtime in (1,2) and addtime between 1 and 2 and into like '%xiaomin';
	
	public function delete($table = '',$where = array()){

		$delete_sql = "delete  from " .$table." where ";

		$pre_values = array();
		foreach ($where as $w_key => $w_value) {
			$w_key = $this->reborn_field($w_key);

			if(is_string($w_value) || is_numeric($w_value)){
				$delete_sql = $delete_sql . $w_key . " = ? ";
				$pre_values[] = $w_value;
			}
			if(is_array($w_value)){
				$w_type = $w_value[0];
				$w_data = $w_value[1];
				$w_connect = $w_value[2]?$w_value[2]:" AND ";
				if(strtolower($w_type) == 'in'){
					if(is_array($w_data)){
						$delete_sql = $delete_sql . $w_connect .$w_key . " IN (".implode(',', array_fill(0, count($w_data), '?')).") ";
						$pre_values = array_merge($pre_values,$in_datas);
					}
				}
				if(strtolower($w_type) == 'like'){
					if(is_string($w_data)){
						$delete_sql = $delete_sql .. $w_connect . $w_key . " LIKE ? ";
						$pre_values[] = $w_data;
					}
				}
				if(strtolower($w_type) == 'between'){
					if(is_array($w_data) && count($w_data) == 2){
						$delete_sql = $delete_sql .. $w_connect . $w_key . " BETWEEN ( ? , ?) ";
						$pre_values = array_merge($pre_values,$in_datas);
					}
				}
			}
		}

	}

	private function reborn_field($field = ''){
		if(strpos($field, '.') === false){
			return '`'.$field.'`';
		}else{
			$field_arr =  array_walk(function($k,$v){return '`'.$v.'`'});explode('.', $field);
			return implode('.', $field_arr);
		}

	}

	public function update($table = "",$where = array(),$new_data = array()){

	}

	public function find(){

	}

	public function batch_insert(){

	}

	public function batch_update(){

	}

	public function get_where(){

	}

	public function start_trans(){

	}

	public function rollback_trans(){

	}

	public function commit_trans(){

	}

}


	