<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Db_Pdo {
	static $instances = NULL;
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $config
	 * @throws PDOException
	 */
	static public function factory($config){
		if (!is_array($config)) {
			throw new PDOException('Db parameters must be in an array.');
		}
		if (!$config['username'] || !$config['password']) {
			throw new PDOException("PDO connect access username or passwd.");
		}
		$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['host'], $config['dbname']);
		$key = md5($dsn);
		$currentTime = Common::getTime();
		if (! isset(self::$instances[$key]) || self::$instances[$key]['timeout'] < $currentTime) {
		    try{
		        self::$instances[$key]['mysql'] = new Pdo($dsn, $config['username'], $config['password'], array(PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE));
		    } catch (PDOException $e) {
		        throw new PDOException($e->getMessage());
		    }
		    
		    if ($config['displayError']) {
		        self::$instances[$key]['mysql']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    }
 		    self::$instances[$key]['mysql']->exec("SET CHARACTER SET UTF8");
		}
		self::$instances[$key]['timeout'] = $currentTime + 3600;
		return self::$instances[$key]['mysql'];
	}
}
