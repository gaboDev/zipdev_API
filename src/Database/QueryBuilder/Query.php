<?php

namespace Models\QueryBuilder;


use Database\DatabaseConnection;
use Database\Utils;

class Query
{
	private $mainClass;
	private $mainClassReference;
	private $selectClause;
	private $whereClauses;
	private $joinClauses;
	private $values;
	
	public function __construct($mainClassReference)
	{
		$this->mainClassReference = new \ReflectionClass($mainClassReference);
		$this->mainClass = $this->mainClassReference->newInstance();
		$this->selectClause = "*";
		$this->whereClauses = array();
		$this->joinClauses = array();
		$this->values = array();
	}
	
	public function select($selectClauses) : Query {
		$selectClauses = func_get_args();
		$this->selectClause = implode(',', $selectClauses);
		return $this;
	}
	
	public function where(string $field, string $operator = "=", $value) : Query {
		$this->whereClauses[] = "$field $operator ?";
		$this->values[] = $value;
		return $this;
	}
	
	public function join($classReference): Query {
		$class = new \ReflectionClass($classReference);
		$model = $class->newInstance();
		$modelTableName = $model->getTableName();
		$modelPK = $model->getPK();
		$mainClassTableName = $this->mainClass->getTableName();
		$mainClassPK = $this->mainClass->getPK();
		$defaultFK = $mainClassTableName."_id";
		$this->joinClauses[] = "INNER JOIN $modelTableName on $modelTableName.$defaultFK = $mainClassTableName.$mainClassPK";
		return $this;
	}
	
	public function first() : ? object {
		$query = $this->buildBaseQuery();
		$query.= " LIMIT 1";
		$objects = $this->executeQuery($query);
		if (is_null($objects))
			return null;
		return $objects[0];
	}
	
	public function get() : ? array {
		$query = $this->buildBaseQuery();
		return $this->executeQuery($query);
	}
	
	
	private function buildSelectClause() : string {
		$mainClassTableName = $this->mainClass->getTableName();
		return " SELECT $this->selectClause FROM $mainClassTableName ";
	}
	
	private function buildJoinClause() : string {
		if (!$this->joinClauses)
			return "";
		return implode(' ', $this->joinClauses);
	}
	
	private function buildWhereClause() : string {
		if (!$this->whereClauses)
			return "";
		$whereClauses = implode(' AND ', $this->whereClauses);
		return " WHERE $whereClauses ";
	}
	
	private function hasJoinClause(): bool {
		return !empty($this->joinClauses);
	}
	
	private function buildBaseQuery() : string {
		$query = $this->buildSelectClause();
		$query.= $this->buildJoinClause();
		$query.= $this->buildWhereClause();
		return $query;
	}
	
	private function executeQuery(string $query) : ? array {
		$dbConnection = DatabaseConnection::getConnection();
		$statement = $dbConnection->prepare($query);
		$executeResult = $statement->execute($this->values);
		if ($executeResult){
			$records = $statement->fetchAll(\PDO::FETCH_ASSOC);
			if (!$this->hasJoinClause())
				return Utils::morph($records, $this->mainClassReference);
			return $records;
		}
		return null;
	}
	
}