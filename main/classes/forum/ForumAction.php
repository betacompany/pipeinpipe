<?php

require_once dirname(__FILE__) . '/../../includes/common.php';

require_once dirname(__FILE__) . '/../user/User.php';

require_once dirname(__FILE__) . '/../content/Action.php';

/**
 *
 * @author Artyom Grigoriev
 */
class ForumAction extends Action {

	public static function types() {
		return array(parent::AGREE, parent::ROMAN);
	}

	public function  __construct($id) {
		$id = intval($id);
		parent::__construct($id);
	}

	public function getMessageId() {
		return $this->targetId;
	}

	public function getAuthorId() {
		return $this->uid;
	}

	public function getAuthor() {
		try {
			return User::getById($this->uid);
		} catch (Exception $e) {
			// TODO use error log file
			return null;
		}
	}

	public static function create($type, $messageId, $uid, $timestamp, $value = -1) {
		if ($value == -1) {
			switch ($type) {
			case parent::AGREE:
				$value = 1;
				break;
			case parent::ROMAN:
				$value = mt_rand(20, 40);
				break;
			}
		}

		parent::create($type, $type, $messageId, $uid, $timestamp, $value);
	}

}
?>
