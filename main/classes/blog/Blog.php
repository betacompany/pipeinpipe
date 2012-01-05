<?php

require_once dirname(__FILE__) . '/../content/Group.php';
require_once dirname(__FILE__) . '/../content/Item.php';

require_once dirname(__FILE__) . '/../content/Connection.php';

/**
 * 
 * @author Artyom Grigoriev
 */
class Blog extends Group {

	private $holders = array();
	private $holdersLoaded = false;

	public function getHolders() {
		if ($this->holdersLoaded) return $this->holder;

		$this->holders = Connection::getHoldersFor($this);
		$this->holdersLoaded = true;
		return $this->holders;
	}

	public function getOwnerDescription() {
		$description = '';
		$holders = $this->getHolders();
		foreach ($holders as $i => $holder) {
			$description .= Connection::holderTitle($holder);
			if ($i < count($holders) - 1) {
				$description .= ', ';
			}
		}

		return $description;
	}

	public static function valueOf(Group $other) {
		return new Blog(-1, $other);
	}
}
?>
