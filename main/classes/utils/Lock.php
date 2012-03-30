<?php

define('LOCKS_FOLDER', dirname(__FILE__) . "/../../temp/locks/");

/**
 * User: ortemij
 * Date: 30.03.12
 * Time: 11:50
 */
class Lock {

	private $filename;
	private $logger;

	public function __construct($name, $logger = false) {
		$this->filename = LOCKS_FOLDER . $name . ".lock";
		$this->logger = $logger;
	}

	public function lock() {
		while ($this->isLocked()) {
			$this->log("Waiting for resourse {$this->filename} 1 sec");
			sleep(1);
		}
		$fp = fopen($this->filename, 'w');
		fwrite($fp, microtime(false) . "\n");
		fclose($fp);
		$this->log("Resource {$this->filename} locked");
	}

	public function release() {
		unlink($this->filename);
		$this->log("Resource {$this->filename} released");
	}

	public function isLocked() {
		return file_exists($this->filename);
	}

	private function log($msg) {
		if ($this->logger) {
			$this->logger->info($msg);
		}
	}
}
