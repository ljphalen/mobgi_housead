<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 观察者模式之被观察者
 * Common_Service_Subject
 * @author fanch
 *
 */
class Common_Service_Subject implements SplSubject {
	
	private $_observers; 
	private $_data;

	/**
	 * 构造函数
	 * 
	 * @param string $mainfile  
	 * @param srting $sitefile
	 * @return 
	 */
	public function __construct($data) {
		$this->_observers = new SplObjectStorage();
		$this->_data = $data;
	}
	
	/**
	 * 注册观察者
	 * @see SplSubject::attach()
	 */
	public function attach(SplObserver $observer) {
		$this->_observers->attach($observer);
	}
	
	/**
	 * 删除观察者
	 * @see SplSubject::detach()
	 */
	public function detach(SplObserver $observer) {
		$this->_observers->detach($observer);
	}
	
	/**
	 * 通知接口
	 * @see SplSubject::notify()
	 */
	public function notify()
	{
		foreach ($this->_observers as $observer) {
			$observer->update($this);
		}
	}
	
	/**
	 * 获取传递的数据
	 */
	public function getData(){
		return $this->_data;
	}
	
	/**
	 * 获取注册过来的所有观察者
	 * @return SplObjectStorage
	 */
	public function getObserver(){
		return $this->_observers;
	}
}
