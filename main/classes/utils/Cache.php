<?php

/**
 * @author ortemij
 */
class Cache {

	private $folderName;

	public function __construct($name) {
		$this->folderName = dirname(__FILE__) . '/../../temp/cache/' . $name;
		if (!file_exists($this->folderName))
			mkdir($this->folderName);
	}

	public function get($key) {
		return file_get_contents($this->folderName . '/' . md5($key));
	}

	public function getByHash($hash) {
		return file_get_contents($this->folderName . '/' . $hash);
	}

	public function removeByHash($hash) {
		return unlink($this->folderName . '/' . $hash);
	}

	public function put($key, $value) {
		$key = md5($key);
		file_put_contents($this->folderName . '/' . $key, $value);
		return $key;
	}
}
?>
