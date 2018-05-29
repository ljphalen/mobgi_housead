<?php
/**
 * SnowFlake ID Generator
 * Based on Twitter Snowflake to generate unique ID across multiple
 * datacenters and databases without having duplicates.
 *
 *
 * SnowFlake Layout
 *
 * 1 sign bit -- 0 is positive, 1 is negative
 * 41 bits -- milliseconds since epoch
 * 5 bits -- dataCenter ID
 * 5 bits -- machine ID
 * 12 bits -- sequence number
 *
 * Total 64 bit integer/string
 */
/**
 * SnowFlake ID Generator
 * Based on Twitter Snowflake to generate unique ID across multiple
 * datacenters and databases without having duplicates.
 *
 *
 * SnowFlake Layout
 *
 * 1 sign bit -- 0 is positive, 1 is negative
 * 41 bits -- milliseconds since epoch
 * 5 bits -- dataCenter ID
 * 5 bits -- machine ID
 * 12 bits -- sequence number
 *
 * Total 64 bit integer/string
 */

class IdWorker
{
	/**
	 * Offset from Unix Epoch
	 * Unix Epoch : January 1 1970 00:00:00 GMT
	 * Epoch Offset : January 1 2000 00:00:00 GMT
	 */
	const EPOCH_OFFSET = 1483200000000;
	const SIGN_BITS = 1;
	const TIMESTAMP_BITS = 41;
	const DATACENTER_BITS = 5;
	const MACHINE_ID_BITS = 5;
	const SEQUENCE_BITS = 12;

	protected $datacenter_id;
	protected $machine_id;
	protected $lastTimestamp = null;

	protected $sequence = 1;
	protected $signLeftShift = self::TIMESTAMP_BITS + self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
	protected $timestampLeftShift = self::DATACENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
	protected $dataCenterLeftShift = self::MACHINE_ID_BITS + self::SEQUENCE_BITS;
	protected $machineLeftShift = self::SEQUENCE_BITS;
	protected $maxSequenceId = -1 ^ (-1 << self::SEQUENCE_BITS);
	protected $maxMachineId = -1 ^ (-1 << self::MACHINE_ID_BITS);
	protected $maxDataCenterId = -1 ^ (-1 << self::DATACENTER_BITS);

	/**
	 * Constructor to set required paremeters
	 *
	 * @param mixed $dataCenter_id Unique ID for datacenter (if multiple locations are used)
	 * @param mixed $machine_id Unique ID for machine (if multiple machines are used)
	 * @throws \Exception
	 */
	public function __construct($dataCenter_id, $machine_id)
	{
		if ($dataCenter_id > $this->maxDataCenterId) {
			throw new Exception('dataCenter id should between 0 and ' . $this->maxDataCenterId);
		}
		if ($machine_id > $this->maxMachineId) {
			throw new Exception('machine id should between 0 and ' . $this->maxMachineId);
		}
		$this->datacenter_id = $dataCenter_id;
		$this->machine_id = $machine_id;
	}

	/**
	 * Generate an unique ID based on SnowFlake
	 * @return string
	 * @throws \Exception
	 */
	public function generateID()
	{
		$sign = 0; // default 0
		$timestamp = $this->getUnixTimestamp();
		if ($timestamp < $this->lastTimestamp) {
			throw new Exception('"Clock moved backwards!');
		}
		if ($timestamp == $this->lastTimestamp) { //与上次时间戳相等，需要生成序列号
			$sequence = ++$this->sequence;
			if ($sequence == $this->maxSequenceId) { //如果序列号超限，则需要重新获取时间
				$timestamp = $this->getNextTimestamp();
				$this->sequence = 0;
				$sequence = ++$this->sequence;
			}
		} else {
			$this->sequence = 0;
			$sequence = ++$this->sequence;
		}
		$this->lastTimestamp = $timestamp;
		$time = (int)($timestamp - self::EPOCH_OFFSET);
		$id = ($sign << $this->signLeftShift) | ($time << $this->timestampLeftShift) | ($this->datacenter_id << $this->dataCenterLeftShift) | ($this->machine_id << $this->machineLeftShift) | $sequence;
		return (string)$id;
	}

	private function getNextTimestamp() {
		$timestamp = $this->getUnixTimestamp();
		while ($timestamp <= $this->lastTimestamp) {
			$timestamp = $this->getUnixTimestamp();
		}

		return $timestamp;
	}

	/**
	 * Get UNIX timestamp in microseconds
	 *
	 * @return int  Timestamp in microseconds
	 */
	private function getUnixTimestamp()
	{
		return floor(microtime(true) * 1000);
	}
}