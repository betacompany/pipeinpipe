<?php

require_once dirname(__FILE__) . '/useful_exception_set.php';

/**
 * @author Artyom Grigoriev
 * should be thrown when the status of being changed object
 * is not suitable for these changes
 */
class InvalidStatusException extends Exception {
    
}

/**
 * @author Artyom Grigoriev
 * should be thrown when used instance of Cup is null
 */
class NullCupException extends Exception {

}

/**
 * @author Innokenty
 * should be thrown when trying to stop a competition before setting it's date
 */
class NullDateException extends Exception {

}

/**
 * @author Innokenty
 * should be thrown when trying to stop a competition with incorrect date value
 */
class InvalidDateException extends Exception {

}

/**
 * @author Innokenty
 * should be thrown when CupFactory retutns a cup of the type, 
 * that is not supposed to be returned in this case
 */
class InvalidCupTypeException extends Exception {

}

?>