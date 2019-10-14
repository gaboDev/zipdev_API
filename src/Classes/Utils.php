<?php

namespace Classes;

class Utils
{
	
	static function isValidIdentifier($identifier) : bool {
		if (!is_null($identifier) && is_numeric($identifier)){
			$identifier = (int) $identifier;
			return $identifier > 0;
		}
		return false;
	}
	
	static function validateCommaSeparatedItems(string $commaSeparatedItems, string $callbackName): ? array {
		$items = explode(',', $commaSeparatedItems);
		$validItems = array_filter($items, array(Utils::class, $callbackName));
		return count($validItems) == count($items) ? $validItems : null;
	}
	
	static function isEmail($email) : bool {
		return strlen($email) == 0 ? false : filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	static function isValidPhone($phone) : bool {
		return preg_match('/^[0-9]{10}$/', $phone) === 1;
	}
	
	static function filterData(array $relatedData, string $phoneIndex, string $emailIndex) : array {
		return [
			'emails' => self::filter($relatedData, $emailIndex),
			'phones' => array_values( self::filter($relatedData, $phoneIndex) )
		];
	}
	
	private static function filter(array $rows, string $index) : array {
		$filteredRows = array_map(function ($row) use ($index){
			return $row[$index];
		}, $rows);
		
		return array_unique($filteredRows);
	}
	
	
	/**
	 * @param string $commaSeparatedItems
	 * @param string $validationCallbackName
	 * @param string $labelForException
	 * @return array
	 * @throws \Exception
	 */
	static function processCommaSeparatedItems(string $commaSeparatedItems, string $validationCallbackName, string $labelForException) : array {
		$validItems = Utils::validateCommaSeparatedItems($commaSeparatedItems, $validationCallbackName);
		if (is_null($validItems))
			throw new \Exception("Some of the $labelForException provided are not valid.");
		return $validItems;
	}
}