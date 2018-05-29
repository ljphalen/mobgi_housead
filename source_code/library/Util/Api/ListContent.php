<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Api_ListContent {
    
    protected $tableKey = null;
    private $rowKey = null;
    private $expireTime = 86400;
    
    public function __construct($tableKey, $rowKey = null) {
        $this->tableKey = $tableKey;
        $this->rowKey = $rowKey;
    }
    
    public function setExpireTime($expireTime) {
        $this->expireTime = $expireTime;
    }
    
    public function getExpireTime() {
        return $this->expireTime;
    }
    
    public function storeListContent($listContent) {
        $newContent = array();
        foreach($listContent as $key => $item) {
            if($this->rowKey && isset($item[$this->rowKey])) {
                $itemId = strval($item[$this->rowKey]);
            }else{
                $itemId = strval($key);
            }
            $newContent[$itemId] = json_encode($item);
        }
        $redis = $this->getCache();
        $redis->delete($this->tableKey);
        return $redis->hMset($this->tableKey, $newContent, $this->expireTime);
    }

    public function getContent($idList) {
        if(! is_array($idList)) {
            return $this->getSingleContent($idList);
        }else{
            return $this->getMutilContent($idList);
        }
    }
    
    private function getSingleContent($id) {
        $redis = $this->getCache();
        $cacheData = $redis->hGet($this->tableKey, $id);
        if($cacheData === false) {
            return array();
        }
        $item = json_decode($cacheData, true);
        return $item;
    }
    
    private function getMutilContent($idList) {
        if (count($idList) < 1) {
            return array();
        }
        $listKeys = array();
        foreach($idList as $id) {
            $listKeys[] = strval($id);
        }
        $redis = $this->getCache();
        $cacheData = $redis->hMget($this->tableKey, $listKeys);
        if($cacheData === false) {
            return array();
        }
        $contentItems = array();
        foreach($cacheData as $item) {
            $item = json_decode($item, true);
            if($this->rowKey && isset($item[$this->rowKey])) {
                $itemId = strval($item[$this->rowKey]);
                $contentItems[$itemId] = $item;
            }else{
                $contentItems[] = $item;
            }
        }
        return $contentItems;
    }
    
    public function getContentList() {
        $redis = $this->getCache();
        $cacheData = $redis->hGetAll($this->tableKey);
        if($cacheData === false) {
            return $cacheData;
        }
        $contentItems = array();
        foreach($cacheData as $item) {
            $item = json_decode($item, true);
            if($this->rowKey && isset($item[$this->rowKey])) {
                $itemId = strval($item[$this->rowKey]);
                $contentItems[$itemId] = $item;
            }else{
                $contentItems[] = $item;
            }
        }
        return $contentItems;
    }
    
    public function storeListItem($itemContent) {
        if (! $this->rowKey || ! isset($itemContent[$this->rowKey])) {
            return false;
        }
        $itemId = strval($itemContent[$this->rowKey]);
        $value = json_encode($itemContent);
        $redis = $this->getCache();
        $redis->hSet($this->tableKey, $itemId, $value);
        return true;
    }
    
    public function removeFromContent($contentId) {
        $redis = $this->getCache();
        $contentIds = $contentId;
        if (! is_array($contentIds)) {
            $contentIds = array($contentId);
        }
        foreach($contentIds as $id) {
            $id = strval($id);
            $redis->hDel($this->tableKey, $id);
        }
        return true;
    }
    
    public function itemKeyExists($contentId) {
        $redis = $this->getCache();
        return $redis->hExists($this->tableKey, $contentId);
    }
    
    public function getCache() {
        return Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
    }
    
}
