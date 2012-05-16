<?php
/**
 * User: ortemij
 * Date: 04.04.12
 * Time: 12:14
 */

define("IMPORT_FILE_DIR", dirname(__FILE__) . "/");
define("CLASSES_DIR", IMPORT_FILE_DIR . "../classes/");

/**
 * Requires once a class with the given name
 * For example:
 *          content/Item
 *          db/ActionDBClient
 * @param $className
 */
function import($className) {
	/** @noinspection PhpIncludeInspection */
	require_once CLASSES_DIR . $className . '.php';
}
