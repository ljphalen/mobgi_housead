<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Api_ListControll extends Util_Api_ListContent {
    
    const KEY = 'key';
    
    public function __construct($tableKey) {
        parent::__construct($tableKey, self::KEY);
    }
    
	public function getValidList() {
	    $controllValue = $this->getContentList();
        $deleteKeys = array();
        $result = array();
	    foreach ($controllValue as $key => $params) {
	        if($params['expire'] <= time()) {
	            $deleteKeys[$key] = $params;
	        }else{
	            $result[$key] = $params['args'];
	        }
	    }
	    if($deleteKeys) {
            $this->removeFromContent(array_keys($deleteKeys));
	    }
	    return $result;
	}

	public function storeListItem($args) {
	    $key = $this->getArgsKey($args);
	    $itemContent = array(
	        self::KEY => $key,
	        'args' => $args,
	        'expire' => time() + $this->getExpireTime(),
	    );
	    parent::storeListItem($itemContent);
	}
	
	public function itemKeyExists($args) {
	    $key = $this->getArgsKey($args);
	    return parent::itemKeyExists($key);
	}
	
	private function getArgsKey($args) {
	    $key = '_' . implode('_', $args);
	    return $key;
	}
	
}
