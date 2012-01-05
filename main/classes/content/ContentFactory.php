<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentFactory
 *
 * @author ortemij
 */
class ContentFactory {

	const COMMENT = 'comment';
	const ITEM = 'item';
	const ACTION = 'action';
	const GROUP = 'group';

	public static function toContentSource($type, $content) {
		switch ($type) {
		case self::COMMENT:
			return htmlspecialchars($content);
		default:
			return $content;
		}
	}

	public static function toContentParsed($type, $content) {
		switch ($type) {
		case self::COMMENT:
			return ;
		default:
			return $content;
		}
	}

	public static function items() {
		return Item::getAll(true);
	}

	public static function comments() {
		return Comment::getAll(true);
	}
}
?>
