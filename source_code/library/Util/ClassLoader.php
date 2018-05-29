<?php

class Util_ClassLoader {
    /**
     * 递归获取目录下的文件
     * 
     * @param $dir
     * @param string $filter            
     * @param array $result            
     * @param bool $deep            
     * @return array
     */
    public static function tree($dir, $filter = '', &$result = array()) {
        $files = new DirectoryIterator($dir);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ($filename[0] === '.') {
                continue;
            }
            if ($file->isDir()) {
                self::tree($dir . DIRECTORY_SEPARATOR . $filename, $filter, $result);
            } else {
                if(!empty($filter) && !preg_match($filter,$filename)){
                    continue;
                }
                $result[] = $dir . DIRECTORY_SEPARATOR . $filename;
            }
        }
        return $result;
    }
    
    public static function loadClassesFromDir($dir) {
        $instances = array();
        $files = self::tree($dir, "/.php$/");
        $libraryPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        foreach ($files as $file) {
            $className = str_replace($libraryPath, "", $file);
            $className = substr($className, 0, strpos($className, ".php"));
            $timerList[] = str_replace(DIRECTORY_SEPARATOR, "_", $className);
        }
        foreach ($timerList as $className) {
            if (! class_exists($className)) {
                continue;
            }
            $instances[$className] = new $className();
        }
        return $instances;        
    }
}
