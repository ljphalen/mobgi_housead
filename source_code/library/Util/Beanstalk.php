<?php
/**
 * This is the PHP client for beanstalkd - a fast, general-purpose work queue.
 * Class implemented using (p)fsockopen()
 * Under Beanstalk Protocol 1.4.6
 * More information is available at https://github.com/kr/beanstalkd
 * 
 * @author  Alacner Zhang <alacner@gmail.com>
 * @version $Id$
 */
class Util_Beanstalk {
	protected $socket = null;
	protected $config = array(
		'host' => 'localhost',
		'port' => 11300,
		'timeout' => 0.25,
	);
	
	/**
	 * @param array $args host,port,timeout
	 */
	public function config($args = array()) {
		$this->config = array_merge($this->config, $args);
	}
	
	 /**
	 * Open Internet or Unix domain socket connection
	 */
	protected function connect()
	{
		if (function_exists('fsockopen')) {
			$this->socket = @fsockopen($this->config['host'], $this->config['port'], $errno, $errstr, $this->config['timeout']);
		} elseif (function_exists('pfsockopen')) {
			$this->socket = @pfsockopen($this->config['host'], $this->config['port'], $errno, $errstr, $this->config['timeout']);
		} else {
			throw new Exception('Socket function were forbidden');
		}
		
		if (!is_resource($this->socket)) {
			throw new Exception($errstr, $errno);
		}
		
		stream_set_write_buffer($this->socket, 0); // Do not buffer writes
		
		return $this->socket;
	}
	
	protected function _socket()
	{
		$this->socket || $this->socket = $this->connect();
		return $this->socket;
	}
	
	protected function _command($stream)
	{
		$socket = $this->_socket();
		@fwrite($socket, $stream);

		$status = stream_get_meta_data($socket);
		if ($status['timed_out']) {
			@fclose($socket);
			throw new Exception('Connection timed out');
		}

		$line = trim(fgets($socket));
		
		if (in_array($line, array('OUT_OF_MEMORY', 'INTERNAL_ERROR', 'BAD_FORMAT', 'UNKNOWN_COMMAND'))) {
			throw new Exception('Server has an error: ' . $line);
		}
		
		return $line;
	}
	
	protected function _fread($len)
	{
		$socket = $this->_socket();
		$ret = '';
		$bneed = $len;
		$offset = 0;
		while ($bneed > 0) {
			$data = fread($socket, $bneed);
			$n = strlen($data);
			if ($n == 0) break;
			$offset += $n;
			$bneed -= $n;
			$ret .= $data;
		}
		
		fread($socket, 2); // skip \r\n
		
		if ($offset != $len) { // Something is borked!
			throw new Exception(sprintf("Something is borked! expecting %d got %d length\n", $len, $offset));
		}
		return $ret;
	}
	
	protected function _tube($tube)
	{
		if (strlen($tube) > 200) {
			throw new Exception('tube is a name at most 200 bytes.');
		}
		return $tube;
	}
	
	// Producer Commands
	
	/**
	 * The "put" command is for any process that wants to insert a job into the queue.
	 * It comprises a command line followed by the job body
	 * It inserts a job into the client's currently used tube (see the "use" command below).
	 * 
	 * @param mixed $data is the job body -- a sequence of bytes of length <bytes> from the
	 *     previous line. This value must be less than max-job-size (default: 2**16)
	 * @param integer $priority is an integer < 2**32. Jobs with smaller priority values will be
	 *     scheduled before jobs with larger priorities. The most urgent priority is 0;
	 *     the least urgent priority is 4,294,967,295.
	 * @param integer $delay is an integer number of seconds to wait before putting the job in
	 *     the ready queue. The job will be in the "delayed" state during this time.
	 * @param integer $ttr -- time to run -- is an integer number of seconds to allow a worker
	 *     to run this job. This time is counted from the moment a worker reserves
	 *     this job. If the worker does not delete, release, or bury the job within
	 *     <ttr> seconds, the job will time out and the server will release the job.
	 *     The minimum ttr is 1. If the client sends 0, the server will silently
	 *     increase the ttr to 1.
	 *
	 * @return number is the integer id of the new job
	 */
	public function put($data, $priority = 1024, $delay = 0, $ttr = 60)
	{
		$bytes = strlen($data);
		$line = $this->_command("put $priority $delay $ttr $bytes\r\n$data\r\n");
		
		switch ($line) {
			case 'EXPECTED_CRLF': return -3;
			case 'JOB_TOO_BIG': return -2;
			case 'DRAINING': return -1;
			default:
		}
		
		if (preg_match('/^(INSERTED|BURIED) (\d+)$/', $line, $match)) {
			return $match[2];
		}
		
		return 0;
	}
	
	/**
	 * The "use" command is for producers. Subsequent put commands will put jobs into
	 * the tube specified by this command. If no use command has been issued, jobs
	 * will be put into the tube named "default".
	 * 
	 * @param string $tube is a name at most 200 bytes. It specifies the tube to use. If the
	 *     tube does not exist, it will be created.
	 */
	public function use_tube($tube = 'default')
	{
		$tube = $this->_tube($tube);
		$line = $this->_command("use $tube\r\n");
		
		if (preg_match('/^USING (.*)$/', $line, $match)) {
			return $match[1];
		}
		
		return false;
	}
	
	// Worker Commands
	
	/**
	 * This will return a newly-reserved job. If no job is available to be reserved,
	 * beanstalkd will wait to send a response until one becomes available. Once a
	 * job is reserved for the client, the client has limited time to run (TTR) the
	 * job before the job times out. When the job times out, the server will put the
	 * job back into the ready queue. Both the TTR and the actual time left can be
	 * found in response to the stats-job command.
	 * 
	 * @param integer timeout default: 0
	 */
	protected function _reserve($command)
	{
		$line = $this->_command($command);
		
		switch ($line) {
			case 'DEADLINE_SOON':
			case 'TIMED_OUT':
				return null;
			default:
		}
		
		if (preg_match('/^RESERVED (\d+) (\d+)$/', $line, $match)) {
			list(, $id, $bytes) = $match;
			return array('id' => $id, 'data' => $this->_fread($bytes));
		}
		return null;
	}
	
	/**
	 * @see self::_reserve
	 */
	public function reserve()
	{
		return $this->_reserve("reserve\r\n");
	}
	
	/**
	 * A timeout value of 0 will cause the server to immediately return either a
	 * response or TIMED_OUT.  A positive value of timeout will limit the amount of
	 * time the client will block on the reserve request until a job becomes
	 * available.

	 * During the TTR of a reserved job, the last second is kept by the server as a
	 * safety margin, during which the client will not be made to wait for another
	 * job. If the client issues a reserve command during the safety margin, or if
	 * the safety margin arrives while the client is waiting on a reserve command.
	 * 
	 */
	public function reserve_with_timeout($timeout = 0) {
		return $this->_reserve("reserve-with-timeout $timeout\r\n");
	}
	
	/**
	 * The delete command removes a job from the server entirely. It is normally used
	 * by the client when the job has successfully run to completion. A client can
	 * delete jobs that it has reserved, ready jobs, and jobs that are buried.
	 * 
	 * @param integer $id is the job id to delete.
	 */
	public function delete($id)
	{
		$line = $this->_command("delete $id\r\n");
		
		switch ($line) {
			case 'DELETED': return true; break;
			case 'NOT_FOUND': return false; break;
			default: return false; break;
		}
	}
	
	/**
	 * The release command puts a reserved job back into the ready queue (and marks
	 * its state as "ready") to be run by any client. It is normally used when the job
	 * fails because of a transitory error.
	 * 
	 * @param integer $id is the job id to delete.
	 * @param integer $priority is a new priority to assign to the job.
	 * @param integer $delay is an integer number of seconds to wait before putting the job in
	 *     the ready queue. The job will be in the "delayed" state during this time.
	 */
	public function release($id, $priority = 1024, $delay = 0)
	{
		$line = $this->_command("release $id $priority $delay\r\n");
		
		switch ($line) {
			case 'RELEASED': return true; break;
			case 'BURIED': return false; break;
			case 'NOT_FOUND': return false; break;
			default: return false; break;
		}
	}
	
	/**
	 * The bury command puts a job into the "buried" state. Buried jobs are put into a
	 * FIFO linked list and will not be touched by the server again until a client
	 * kicks them with the "kick" command.
	 * 
	 * @param integer $id is the job id to delete.
	 * @param integer $priority is a new priority to assign to the job.
	 */
	public function bury($id, $priority = 1024)
	{
		$line = $this->_command("bury $id $priority\r\n");
		
		switch ($line) {
			case 'BURIED': return true; break;
			case 'NOT_FOUND': return false; break;
			default: return false; break;
		}
	}
	
	/**
	 * The "touch" command allows a worker to request more time to work on a job.
	 * This is useful for jobs that potentially take a long time, but you still want
	 * the benefits of a TTR pulling a job away from an unresponsive worker.  A worker
	 * may periodically tell the server that it's still alive and processing a job
	 * (e.g. it may do this on DEADLINE_SOON).
	 * 
	 * @param integer $id is the ID of a job reserved by the current connection.
	 */
	public function touch($id)
	{
		$line = $this->_command("touch $id\r\n");
		
		switch ($line) {
			case 'TOUCHED': return true; break;
			case 'NOT_FOUND': return false; break;
			default: return false; break;
		}
	}
	
	/**
	 * The "watch" command adds the named tube to the watch list for the current
	 * connection. A reserve command will take a job from any of the tubes in the
	 * watch list. For each new connection, the watch list initially consists of one
	 * tube, named "default".
	 * 
	 * @param string $tube is a name at most 200 bytes. It specifies a tube to add to the watch
	 *     list. If the tube doesn't exist, it will be created.
	 * @return number is the integer number of tubes currently in the watch list.
	 */
	public function watch($tube)
	{
		$tube = $this->_tube($tube);
		$line = $this->_command("watch $tube\r\n");
		
		if (preg_match('/^WATCHING (\d+)$/', $line, $match)) {
			return $match[1];
		}
		return 0;
	}
	
	/**
	 * The "ignore" command is for consumers. It removes the named tube from the
	 * watch list for the current connection.
	 * 
	 * @param string $tube is a name at most 200 bytes. It specifies a tube to add to the watch
	 *     list. If the tube doesn't exist, it will be created.
	 * @return number is the integer number of tubes currently in the watch list.
	 */
	public function ignore($tube)
	{
		$tube = $this->_tube($tube);
		$line = $this->_command("ignore $tube\r\n");
		switch ($line) {
			case 'NOT_IGNORED': return false; break;
		}
		if (preg_match('/^WATCHING (\d+)$/', $line, $match)) {
			return $match[1];
		}
		return false;
	}
	
	
	// Other Commands
	
	/**
	 * The peek commands let the client inspect a job in the system. There are four
	 * variations. All but the first operate only on the currently used tube.
	 */
	protected function _peek($command)
	{
		$line = $this->_command($command);
		
		switch ($line) {
			case 'NOT_FOUND':
				return null;
			default:
		}
		
		if (preg_match('/^FOUND (\d+) (\d+)$/', $line, $match)) {
			list(, $id, $bytes) = $match;
			return array('id' => $id, 'data' => $this->_fread($bytes));
		}
		return null;
	}
	
	/**
	 * @return job
	 */
	public function peek($id) {
		return $this->_peek("peek $id\r\n");
	}
	
	/**
	 * @return the next ready job.
	 */
	public function peek_ready() {
		return $this->_peek("peek-ready\r\n");
	}
	
	/**
	 * @return the delayed job with the shortest delay left.
	 */
	public function peek_delayed() {
		return $this->_peek("peek-delayed\r\n");
	}
	
	/**
	 * @return the next job in the list of buried jobs.
	 */
	public function peek_buried() {
		return $this->_peek("peek-buried\r\n");
	}
	
	/**
	 * The kick command applies only to the currently used tube. It moves jobs into
	 * the ready queue. If there are any buried jobs, it will only kick buried jobs.
	 * Otherwise it will kick delayed jobs. 
	 * 
	 * @param integer $bound is an integer upper bound on the number of jobs to kick. The server
	 *     will kick no more than <bound> jobs.
	 */
	public function kick($bound)
	{
		$line = $this->_command("kick $bound\r\n");
		
		if (preg_match('/^KICKED (\d+)$/', $line, $match)) {
			return $match[1];
		}
		return 0;
	}
	
	/**
	 * The stats-job command gives statistical information about the specified job if
	 * it exists.
	 */
	public function stats_job($id)
	{
		$line = $this->_command("stats-job $id\r\n");
		
		switch ($line) {
			case 'NOT_FOUND': return false; break;
		}
		
		if (preg_match('/^OK (\d+)$/', $line, $match)) {
			list(, $bytes) = $match;
			return $this->_fread($bytes);
		}
		return false;
	}
	
	/**
	 * The stats-tube command gives statistical information about the specified tube
	 * if it exists.
	 */
	public function stats_tube($tube)
	{
		$tube = $this->_tube($tube);
		$line = $this->_command("stats-tube $tube\r\n");
		
		switch ($line) {
			case 'NOT_FOUND': return false; break;
		}
		
		if (preg_match('/^OK (\d+)$/', $line, $match)) {
			list(, $bytes) = $match;
			return $this->_fread($bytes);
		}
		return false;
	}
	
	/**
	 * The stats command gives statistical information about the system as a whole.
	 */
	public function stats()
	{
		$line = $this->_command("stats\r\n");
		
		if (preg_match('/^OK (\d+)$/', $line, $match)) {
			list(, $bytes) = $match;
			return $this->_fread($bytes);
		}
		return false;
	}
	
	/**
	 * The list-tubes command returns a list of all existing tubes. 
	 */
	public function list_tubes()
	{
		$line = $this->_command("list-tubes\r\n");
		
		if (preg_match('/^OK (\d+)$/', $line, $match)) {
			list(, $bytes) = $match;
			return $this->_fread($bytes);
		}
		return false;
	}
	
	/**
	 * The list-tube-used command returns the tube currently being used by the
	 * client.
	 */
	public function list_tube_used()
	{
		$line = $this->_command("list-tube-used\r\n");
		
		if (preg_match('/^USING (.*)$/', $line, $match)) {
			return $match[1];
		}
		
		return false;
	}
	
	/**
	 * The list-tubes-watched command returns a list tubes currently being watched by
	 * the client. 
	 */
	public function list_tubes_watched()
	{
		$line = $this->_command("list-tubes-watched\r\n");
		
		if (preg_match('/^OK (\d+)$/', $line, $match)) {
			list(, $bytes) = $match;
			return $this->_fread($bytes);
		}
		return false;
	}
	
	/**
	 * The quit command simply closes the connection.
	 */
	public function quit()
	{
		$line = $this->_command("quit\r\n");
		
		switch ($line) {
			case 'NOT_IGNORED': return false; break;
		}
		
		if (preg_match('/^WATCHING (\d+)$/', $line, $match)) {
			return $match[1];
		}
		return false;
	}
	
	/**
	 * The pause-tube command can delay any new job being reserved for a given time. 
	 */
	public function pause_tube($tube, $delay = 0)
	{
		$tube = $this->_tube($tube);
		$line = $this->_command("pause-tube $tube $delay\r\n");
		
		switch ($line) {
			case 'PAUSED': return true; break;
			case 'NOT_FOUND': return false; break;
			default:
		}
		return false;
	}
	
	public function __destruct() {
		if (is_resource($this->socket)) fclose($this->socket);
	}
}