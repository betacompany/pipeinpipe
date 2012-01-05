<?php

require_once dirname(__FILE__) . '/../db/ActionDBClient.php';

require_once dirname(__FILE__) . '/../../includes/log.php';

/**
 * @author ortemij
 */
class Action {
    
	const TARGET_ITEM = 'item';
	const TARGET_COMMENT = 'comment';
	
	const AGREE = 'agree';
	const ROMAN = 'roman';
	const EVALUATION = 'evaluation';

	const ROMAN_TIME = 86400;

	public static function forumTypes() {
		return array(self::AGREE, self::ROMAN);
	}
	
	protected $id;
	protected $targetType;
	protected $targetId;
	protected $target;
	protected $targetLoaded = false;
	protected $type;
	protected $uid;
	protected $timestamp;
	protected $value;

	public function  __construct($id, $data = null) {
		if ($data == null) {
			assertPositive('ID of content action should be positive! ', $id);
			$iterator = ActionDBClient::getById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no content action with id=' . $id);
			}
		}

		$this->id = $data['id'];
		$this->targetType = $data['target_type'];
		$this->targetId = $data['target_id'];
		$this->type = $data['type'];
		$this->uid = $data['uid'];
		$this->timestamp = $data['timestamp'];
		$this->value = $data['value'];
	}

	public function getId() {
		return $this->id;
	}

	public function getTargetType() {
		return $this->targetType;
	}

	public function getTargetId() {
		return $this->targetId;
	}

	public function getType() {
		return $this->type;
	}

	public function getUID() {
		return $this->uid;
	}

	public function getAuthorId() {
		return $this->uid;
	}

	public function getAuthor() {
		return User::getById($this->uid);
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getValue() {
		return $this->value;
	}

	public static function create($type, $targetType, $targetId, $uid, $timestamp, $value) {
		$id = ActionDBClient::insert($type, $targetType, $targetId, $uid, $timestamp, $value);
		try {
			return new Action($id);
		} catch (Exception $e) {
			return null;
		}
	}

	public static function getActionsFor($targetType, $targetId) {
		$iterator = ActionDBClient::getByTarget($targetType, $targetId);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			try {
				$result[] = new Action(-1, $data);
			} catch (Exception $e) {
				global $LOG;
				$LOG->exception($e);
			}

			$iterator->next();
		}

		return $result;
	}

	public static function isActive($type, $target) {
		if (!($target instanceof Item) && !($target instanceof Comment)) {
			return false;
		}

		switch ($type) {
		case self::AGREE: return true;
		case self::EVALUATION: return true;
		case self::ROMAN:
			if (time() - $target->getTimestamp() < self::ROMAN_TIME) return true;
			return false;
		default: return false;
		}
	}

	public static function getActionName($type) {
		switch ($type) {
		case self::AGREE: return 'Согласиться';
		case self::EVALUATION: return 'Оценить';
		case self::ROMAN: return 'Зароманить';
		default: return 'Действие';
		}
	}

	public static function countActive($type, $uid = 0) {
		return ActionDBClient::countActive($type, $uid);
	}

	public static function countPassive($uid = 0) {

	}

}
?>
