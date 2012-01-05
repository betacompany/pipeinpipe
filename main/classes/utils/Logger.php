<?php

require_once dirname(__FILE__) . '/../../includes/config-local.php';

/**
 * @author ortemij
 */
class Logger {

	const QUERY_LOG = '../../logs/query.log';
	const CUSTOM_LOG = '../../logs/custom.log';
	const CUPMS_LOG = '../../logs/cupms.log';

	const LAYER_EXCEPTION = 'EXCEPTION';
	const LAYER_INFO = 'INFO';
	const LAYER_WARN = 'WARN';
	const LAYER_ERROR = 'ERROR';
	const LAYER_SQL = 'SQL';
	const LAYER_PIZDETS = 'PIZDETS';

	private $fp;
	private $filename;

	public function __construct($filename = self::CUSTOM_LOG) {
		$this->filename = dirname(__FILE__) . '/' . $filename;
		@chmod($this->filename, 0777);
		if (file_exists($filename)) {
			$size = filesize($filename);
			if ($size > LOG_FILE_MAX_SIZE) {
				rename($filename, $filename . '-' . time());
			}
		}
	}
	
	private function writeln($line = '') {
		$this->fp = fopen($this->filename, 'a+');
		fwrite(
			$this->fp,
			$line . "\n"
		);
		fflush($this->fp);
		fclose($this->fp);
	}

	private function header($layer) {
		$tm = microtime(true);
		global $auth;
		$userid = (isset($auth) && $auth->isAuth()) ? 'user:' . $auth->uid() : 'unknown';

		$this->writeln(
			sprintf(
				"[%s] %s:%s %s %s?%s",
				$layer,
				date("Y/m/d H:i:s", floor($tm)),
				preg_replace("/ /", "0", sprintf("%3d", ($tm * 1000) % 1000)),
				$userid,
				$_SERVER['SCRIPT_NAME'],
				$_SERVER['QUERY_STRING']
			)
		);
	}

	private function log($layer, $message) {
		$this->header($layer);
		$this->writeln($message);
		$this->writeln();
	}

	/**
	 * [INFO]
	 * @param string $message 
	 */
	public function info($message) {
		$this->log(self::LAYER_INFO, $message);
	}

	/**
	 * [QUERY]
	 * @param string $query
	 */
	public function sql($query) {
		$this->log(self::LAYER_SQL, $query);
	}

	/**
	 * [WARN]
	 * @param string $message 
	 */
	public function warn($message) {
		$this->log(self::LAYER_WARN, $message);
	}

	/**
	 * [ERROR]
	 * @param string $message
	 */
	public function error($message) {
		$this->log(self::LAYER_ERROR, $message);
	}

	/**
	 * [PIZDETS]
	 * @param string $message
	 */
	public function pizdets($message) {
		$this->log(self::LAYER_PIZDETS, $message);
	}

	/**
	 * Logs catched exception
	 * @param Exception $e
	 * @param string $layer [optional] EXCEPTION default
	 * @param string $message [optional] empty default
	 */
	public function exception(Exception $e, $layer = self::LAYER_EXCEPTION, $message = '') {
		$this->log(
			$layer,
			empty ($message) ? $e->__toString() : $message . "\nCaused by " . $e->__toString()
		);
	}
}
?>
