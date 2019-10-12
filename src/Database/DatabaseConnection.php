<?php

namespace Database;


use PDO;

class DatabaseConnection
{
	private static $DBConnection;
	
	protected $user;
	protected $pass;
	protected $dbName;
	protected $host;
	protected $connection;
	
	
	private function __construct()
	{
		$this->user = getenv('DB_USER');
		$this->pass = getenv('DB_PASS');
		$this->db = getenv('DB_NAME');
		$this->host = getenv('DB_HOST');
		$this->connection = new \PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->pass, $this->PDOInstanceOptions());
	}
	
	private function PDOInstanceOptions(){
		return [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
		];
	}
	
	public function getConnectionObject() : PDO {
		return $this->connection;
	}
	
	static function getConnection() : PDO {
		if (is_null(self::$DBConnection)){
			self::$DBConnection = new DatabaseConnection();
		}
		return self::$DBConnection->getConnectionObject();
	}
	
	
	static function beginTransaction(){
		$DBConnection = self::getConnection();
		$DBConnection->beginTransaction();
	}
	
	static function commit(){
		$DBConnection = self::getConnection();
		$DBConnection->commit();
	}
	
	static function rollBack(){
		$DBConnection = self::getConnection();
		$DBConnection->rollBack();
	}
	
	
}