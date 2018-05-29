<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 观察者模式之观察者
 * Common_Service_Observer
 * @author fanch
 *
 */
class Common_Service_Observer implements SplObserver {

	/**
	 * 观察者执行的方法体
	 * @see SplObserver::update()
	 */
	public function update(SplSubject $subject){
		$this->run($subject->getData());
	}
}
