<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
class Util_Lcs {	
	private $mSourceStr;
	private $mTargetStr;
	private $c = array();
	
	
	/*返回串一和串二的最长公共子序列
	 */
	function getLCS($sourceStr, $targetStr, $sourceStrLen = 0, $targetStrLen = 0) {
		$this->mSourceStr = $sourceStr;
		$this->mTargetStr = $targetStr;
		if ($sourceStrLen == 0) $sourceStrLen = strlen($sourceStr);
		if ($targetStrLen == 0) $targetStrLen = strlen($targetStr);
		$this->initC($sourceStrLen, $targetStrLen);
		return $this->printLCS($this->c, $sourceStrLen - 1, $targetStrLen - 1);
	}
	
	/*返回两个串的相似度
	 */
	function getSimilar($sourceStr, $targetStr) {
		$sourceStrLen = strlen($sourceStr);
		$targetStrLen = strlen($targetStr);
		$lcsLen = strlen($this->getLCS($sourceStr, $targetStr, $sourceStrLen, $targetStrLen));
		return $lcsLen * 2 / ($sourceStrLen + $targetStrLen);
	}
	
	/**
	 * 
	 * @param unknown_type $sourceStrLen
	 * @param unknown_type $targetStrLen
	 */
	function initC($sourceStrLen, $targetStrLen) {
		for ($i = 0; $i < $sourceStrLen; $i++) $this->c[$i][0] = 0;
		for ($j = 0; $j < $targetStrLen; $j++) $this->c[0][$j] = 0;
		for ($i = 1; $i < $sourceStrLen; $i++) {
			for ($j = 1; $j < $targetStrLen; $j++) {
				if ($this->mSourceStr[$i] == $this->mTargetStr[$j]) {
					$this->c[$i][$j] = $this->c[$i - 1][$j - 1] + 1;
				} else if ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
					$this->c[$i][$j] = $this->c[$i - 1][$j];
				} else {
					$this->c[$i][$j] = $this->c[$i][$j - 1];
				}
			}
		}
	}
	
	function printLCS($c, $i, $j) {
		if ($i == 0 || $j == 0) {
			if ($this->mSourceStr[$i] == $this->mTargetStr[$j]) {
				return $this->mTargetStr[$j];
			}
			else return "";
		}
		if ($this->mSourceStr[$i] == $this->mTargetStr[$j]) {
			return $this->printLCS($this->c, $i - 1, $j - 1).$this->mTargetStr[$j];
		} else if ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
			return $this->printLCS($this->c, $i - 1, $j);
		} else {
			return $this->printLCS($this->c, $i, $j - 1);
		}
	}
}
