<?php

require_once dirname(__FILE__) . '/../../includes/import.php';

import("content/Item");

/**
 * User: ortemij
 * Date: 04.04.12
 * Time: 11:54
 */
class ItemsContainer extends Item {

	private $items = array();
	private $innerType;
	private $innerUser;
	private $timestamp;

	public function  __construct($items) {
		if (empty($items)) {
			throw new InvalidArgumentException("array should not be empty!");
		}
		$first = $items[0];
		$this->innerType = ItemsContainer::checkAndGetType($first);
		$this->innerUser = $first->getUser();
		$this->timestamp = $first->getTimestamp();
		foreach ($items as $item) {
			$type = ItemsContainer::checkAndGetType($item);
			if ($type != $this->innerType) {
				throw new InvalidArgumentException("all items should be the same type");
			}
			$this->items[] = $item;
		}
	}

	public function getId() {
		throw new BadFunctionCallException();
	}

	public function getType() {
		return $this->innerType . "_container";
	}

	public function getGroupId() {
		throw new BadFunctionCallException();
	}

	public function getGroup() {
		throw new BadFunctionCallException();
	}

	public function getUID() {
		return $this->innerUser->getId();
	}

	public function getUser() {
		return $this->innerUser;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getCreationTimestamp() {
		return $this->timestamp;
	}

	public function getLastCommentTimestamp() {
		throw new BadFunctionCallException();
	}

	public function getContentTitle() {
		throw new BadFunctionCallException();
	}

	public function getContentSource() {
		throw new BadFunctionCallException();
	}

	public function getContentParsed() {
		throw new BadFunctionCallException();
	}

	public function getContentValue() {
		throw new BadFunctionCallException();
	}

	public function isClosed() {
		throw new BadFunctionCallException();
	}

	public function isAvailableFor($user) {
		foreach ($this->items as $item) {
			if (!$item->isAvailableFor($user)) {
				return false;
			}
		}
		return true;
	}

	public function update() {
		throw new BadFunctionCallException();
	}

	public function getActions() {
		throw new BadFunctionCallException();
	}

	public function getComments($from = 0, $limit = 0) {
		throw new BadFunctionCallException();
	}

	public function getTags() {
		throw new BadFunctionCallException();
	}

	public function countComments() {
		throw new BadFunctionCallException();
	}

	public function countNewCommentsFor($user) {
		throw new BadFunctionCallException();
	}

	public function addComment($type, $uid, $timestamp, $contentSource, $contentParsed) {
		throw new BadFunctionCallException();
	}

	public function addTag($tag, $user) {
		foreach ($this->items as $item) {
			$item->addTag($tag, $user);
		}
	}

	public function addTags($tagIds, $user) {
		foreach ($this->items as $item) {
			$item->addTags($tagIds, $user);
		}
	}

	public function setGroupId($groupId, $update = true) {
		throw new BadFunctionCallException();
	}

	public function setTimestamp($timestamp, $update = true) {
		throw new BadFunctionCallException();
	}

	public function setLastCommentTimestamp($timestamp, $update = true) {
		throw new BadFunctionCallException();
	}

	public function viewedBy(User $user) {
		foreach ($this->items as $item) {
			$item->viewedBy($user);
		}
	}

	public function getLastViewFor($user) {
		throw new BadFunctionCallException();
	}

	public function setLastViewForLite($user, $timestamp) {
		throw new BadFunctionCallException();
	}

	public function hasNewCommentsFor($user) {
		throw new BadFunctionCallException();
	}

	public function isEvaluable() {
		throw new BadFunctionCallException();
	}

	public function act(User $user, $type, $value = 0) {
		throw new BadFunctionCallException();
	}

	public function isActedBy($user, $type) {
		throw new BadFunctionCallException();
	}

	public function remove() {
		throw new BadFunctionCallException();
	}

	public function removeTags() {
		throw new BadFunctionCallException();
	}

	public function getItems() {
		return $this->items;
	}

	private static function checkAndGetType($o) {
		if (!($o instanceof Item)) {
			throw new InvalidArgumentException("array should contain Item instances!");
		}
		return $o->getType();
	}
}
