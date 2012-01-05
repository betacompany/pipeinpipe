<?php

/**
 * @author Artyom Grigoriev
 * should be thrown when passed id is incorrect for such object type
 * ex. there is no such row in DB
 */
class InvalidIdException extends InvalidArgumentException {

}

/**
 * @author Artyom Grigoriev
 */
class InvalidDataException extends Exception {
	
}

?>
