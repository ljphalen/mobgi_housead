<?php 
/**
 * 敏感词过滤类
 * @author fanch
 *
 */
class Util_Sensitive{
	
	/**
	 * 查找包含的敏感词
	 * @param string $strContent
	 * @return array
	 */
	public static function search($strContent){
		$res = self::_create();
		$ret = trie_filter_search($res, $strContent);
		self::_free($res);
		return $ret;
	}
	
	/**
	 * 查找包含的所有敏感词
	 * @param string $strContent
	 * @return array
	 */
	public static function searchAll($strContent){
		$res = self::_create();
		$ret = trie_filter_search_all($res, $strContent);
		self::_free($res);
		return $ret;
	}
	
	/**
	 * 创建敏感词过滤资源句柄
	 * @return resource
	 */
	private static function _create(){
		$dataPath = Common::getConfig('siteConfig', 'dataPath');
		$badtrie = $dataPath.'/badtrie.dic';
		$resTrie = trie_filter_new(); //create an empty trie tree
		$sensitives = Client_Service_Sensitive::getsBySensitives(array('status'=>1));
		if(!file_exists($badtrie)) {
			foreach ($sensitives as $k => $v) {
				trie_filter_store($resTrie, $v['title']);
			}
			trie_filter_save($resTrie, $badtrie);
		} else {
			$resTrie = trie_filter_load($badtrie);
		}
		return $resTrie;
	}
	
	/**
	 * 释放敏感词过滤资源
	 * @param resource $resTrie
	 */
	private static function _free($resTrie){
		trie_filter_free($resTrie);
	}
}