<?php

require_once dirname(__FILE__) . '/../../includes/common.php';

class ResponseCache {
	private $key;
	private $request;
	private $dirname;
	private $filename;
	private $started = false;
	private $loadingEnabled = true;
	private $storingEnabled = true;

	/**
	 * Starts to bufferize output
	 * @return boolean
	 */
	public function start() {
		if (!$this->isStoringEnabled()) return false;
		if ($this->started) return false;
		ob_start();
		$this->started = true;
		return true;
	}

	/**
	 * Stores bufferized output and flush it
	 * @return boolean
	 */
	public function store() {
		if (!$this->isStoringEnabled()) return false;
		$contents = ob_get_contents();
		file_put_contents($this->filename, $contents);
		ob_flush();
		return true;
	}

	public function remove($verbose = false) {
		file_remove($this->dirname, $verbose);
	}

	public function storeData($data) {
		file_put_contents($this->filename, $data);
	}

	/**
	 * Returns cached data if exists
	 * Returns false otherwise
	 * @return string
	 */
	public function get() {
		if (!$this->isLoadingEnabled()) return false;
		if (!file_exists($this->filename)) return false;
		return file_get_contents($this->filename);
	}

	/**
	 * Proofs in $politics hash exists TTL for cached data
	 * and if it is up-to-date or if such politics is not specified
	 * echos cached data and then exits process
	 * @param array $politics
	 */
	public function echoByPolitics($politics) {
		if (!$this->isLoadingEnabled()) return false;
		if (
			$this->get() && (
				!isset($politics[$this->key]) ||
				$this->getAge() <= $politics[$this->key]
			)
		) {
			$this->echoAndExit();
		}
	}

	/**
	 * Echos cached data and exits process
	 */
	public function echoAndExit() {
		if (!$this->isLoadingEnabled()) return false;
		if (!file_exists($this->filename)) return false;
		echo $this->get();
		exit(0);
	}

	/**
	 * Returns the age of cached data in milliseconds
	 * @return int
	 */
	public function getAge() {
		if (!file_exists($this->filename)) return INF;
		return time() - filemtime($this->filename);
	}

	/**
	 * Proofs if cache data storing enabled
	 * @return boolean
	 */
	public function isStoringEnabled() {
		return $this->storingEnabled;
	}

	/**
	 * Proofs if data loading from cache enabled
	 * @return boolean
	 */
	public function isLoadingEnabled() {
		return $this->loadingEnabled;
	}

	/**
	 * Enables data storing in cache
	 */
	public function enableStoring() {
		$this->storingEnabled = true;
	}

	/**
	 * Disables data storing in cache
	 */
	public function disableStoring() {
		$this->storingEnabled = false;
	}

	/**
	 * Enables data loading from cache
	 */
	public function enableLoading() {
		$this->loadingEnabled = true;
	}

	/**
	 * Disables data loading from cache
	 */
	public function disableLoading() {
		$this->loadingEnabled = false;
	}

	/**
	 * Returns instance of ResponseCache class
	 * @param string $key
	 * @return ResponseCache
	 */
	public static function getInstance($key) {
		return new ResponseCache($key);
	}

	public function  __construct($key, $request = null) {
		$request = ($request == null) ? array_diff_key(
						$_REQUEST,
						array_merge(
							array(
								'handler' => '',
								'method' => '',
								'cache_load' => '',
								'cache_store' => '',
								't' => '',
								'_' => ''
							),
							$_COOKIE,
							$_SESSION
						)
					) : $request;

		$this->key = $key;

		$this->request = $request;

		ksort($this->request);
		if (isset($this->request['date'])) {
			$this->dirname = dirname(__FILE__) . '/../../temp/cache/' . $key . '/' . $this->request['date'];
			$this->request = array_diff_key($this->request, array('date' => ''));
			$this->filename = $this->dirname . '/' . md5(implode('_', $this->request));
			if (!file_exists($this->dirname)) mkdir($this->dirname, 0777, true);
		} else {
			$this->dirname = dirname(__FILE__) . '/../../temp/cache/' . $key;
			$this->filename = $this->dirname . '/' . md5(implode('_', $this->request));
			if (!file_exists($this->dirname)) mkdir($this->dirname, 0777, true);
		}
		
		$this->loadingEnabled = !isset($this->request['cache_load']) || $this->request['cache_load'] == 'enabled';
		$this->storingEnabled = !isset($this->request['cache_store']) || $this->request['cache_store'] == 'enabled';
	}
}

?>