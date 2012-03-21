<?php

require_once dirname(__FILE__).'/../classes/cupms/Player.php';
require_once dirname(__FILE__).'/../classes/cupms/Cup.php';
require_once dirname(__FILE__).'/../classes/cupms/Competition.php';
require_once dirname(__FILE__).'/../classes/cupms/League.php';
require_once dirname(__FILE__).'/../classes/cupms/RatingTable.php';

require_once dirname(__FILE__).'/../classes/exceptions/cupms_exception_set.php';
require_once dirname(__FILE__).'/../classes/exceptions/useful_exception_set.php';

/**
 * Checks if $variable == $value
 * Throws InvalidArgumentException
 * @param string $errorPrefix
 * @param mixed $variable
 * @param mixed $value 
 */
function assertValue($errorPrefix, $variable, $value) {
	if ($variable != $value) {
		throw new InvalidArgumentException($errorPrefix . $value);
	}
}

/**
 * Checks if $variable is positive
 * Throws InvalidArgumentException
 * @param string $errorPrefix
 * @param mixed $variable
 */
function assertPositive($errorPrefix, $variable) {
	if ($variable <= 0) {
		throw new InvalidArgumentException($errorPrefix . $variable);
	}
}

/**
 * Throws InvalidArgumentException
 * @param string $errorPrefix
 * @param mixed $variable
 */
function assertNotNegative($errorPrefix, $variable) {
	if ($variable < 0) {
		throw new InvalidArgumentException($errorPrefix . $variable);
	}
}

/**
 * Checks if $variable is true
 * @param string $errorText
 * @param mixed $variable
 */
function assertTrue($errorText, $variable) {
	if (!$variable) {
		throw new InvalidArgumentException($errorText);
	}
}

/**
 * Checks if there is such pipeman in DB
 * @param int $id
 */
function assertPipeman($id) {
    // TODO escape symbols in $id
    assertTrue('There is no pipeman with id='.$id, Player::existsById($id));
}

function assertCup($id) {
    // TODO escape symbol in $id
    assertTrue('There is no cup with id='.$id, Cup::existsById($id));
}

function assertLeague($id) {
    // TODO escape symbol in $id
    assertTrue('There is no league with id='.$id, League::existsById($id));
}

function assertCompetition($id) {
    // TODO escape symbol in $id
    assertTrue('There is no competition with id='.$id, Competition::existsById($id));
}

function assertIsset($var, $varName = '') {
    assertTrue('Variable '.$varName.' is not set', isset($var));
}

function assertDate($date) {
	$li = explode('-', $date, 3);
	if (count($li) != 3)
		throw new InvalidDateException();

	$year = intval($li[0]);
	if ($year < 2008)
		throw new InvalidDateException();

	$month = intval($li[1]);
	if ($month > 12 || $month < 1)
		throw new InvalidDateException();

	$date = intval($li[2]);
	if ($date < 1 || $date > 31)
		throw new InvalidDateException();

	if ($date > 28) {
		if ($month == 2) {
			if ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0))
				$daysInMonth = 29;
			else
				$daysInMonth = 28;
		} else {
			$daysInMonth = ($month % 2 == 1 && $month <= 7) || ($month % 2 == 0 && $month > 7) ? 31 : 30;
		}
		if ($date > $daysInMonth)
			throw new InvalidDateException();
	}
}

function assertNotEmpty($str) {
	assertTrue('This variable must not be empty', !empty($str));
}

function assertParam($param) {
	assertTrue('Query parameter \''.$param.'\' is not set', isset($_REQUEST[$param]));
}

function assertCupMult($mult) {
	assertTrue('Cup multiplier must be greater than 0 and less than 8', $mult > 0 && $mult < 8);
}

?>
