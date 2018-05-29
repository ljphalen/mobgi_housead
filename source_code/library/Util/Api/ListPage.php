<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Api_ListPage extends Util_Api_ListContent {

    const PAGE_TOTAL = 'total';
    const PAGE_SIZE = 'pageSize';
    
    private $pageInfoKey = 'page_info';
    private $limit = 0;//等于0直接应用外部算好的翻页数据
    
    public function Util_Api_ListPage($tableKey, $limit = 10) {
        parent::__construct($tableKey);
        $this->limit = $limit;
    }
    
    public function storeListContent($listContent) {
        $newPageList = array();
        if($this->limit > 0) {
            $pageList = array_chunk($listContent, $this->limit, false);
            $page = 0;
            foreach($pageList as $item) {
                $newPageList[++$page] = $item;
            }
        }else{
            $newPageList = $listContent;
        }
        $total = count($listContent);
        $pageSize =count($newPageList);
        $info = array(self::PAGE_TOTAL => $total, self::PAGE_SIZE => $pageSize);
        $newPageList[$this->pageInfoKey] = $info;
        parent::storeListContent($newPageList);
    }
    
    public function getPageInfo() {
        $redis = $this->getCache();
        $info = $redis->hGet($this->tableKey, $this->pageInfoKey);
        if($info === false) {
            return $info;
        }
        return json_decode($info, true);
    }
    
}
