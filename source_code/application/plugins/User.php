<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class UserPlugin extends Yaf_Plugin_Abstract {
	
	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		//xhprof start性能检测 请勿增加到线上环境
		$debug = isset($_REQUEST["tdebug"])?$_REQUEST["tdebug"]:'';
		if ($debug) {
			xhprof_enable();
			xhprof_enable(XHPROF_FLAGS_NO_BUILTINS); //不记录内置的函数
			xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);  //同时分析CPU和Mem
		}
		//------------------//
	}
	

	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		//xhprof stop性能检测 请勿增加到线上环境
		$debug =isset($_REQUEST["tdebug"])?$_REQUEST["tdebug"]:'' ;
		if ($debug) {
			$xhprofData = xhprof_disable();
			Yaf_loader::import("Util/Xhprof/xhprof_lib.php");
			Yaf_loader::import("Util/Xhprof/xhprof_runs.php");
			$xhprofKey = sprintf("%s_%s_%s", $request->module, $request->controller, $request->action);
			$xhprofRuns = new XHProfRuns_Default();
			$runId = $xhprofRuns->save_run($xhprofData, $xhprofKey);
			echo '<a href="http://localhost.xhprof.mobgi.com/index.php?run='.$runId.'&source='.$xhprofKey.'" target="_blank">页面性能</a>';
		}
		//------------------//
        /* if ($request->module == 'Client' && $request->controller != 'Index') {
			$this->_updateCacheFile($request, $response);
		 } */
	}

	private function _updateCacheFile($request, $response) {
		$key = sprintf("%s_%s_%s", $request->module, $request->controller, $request->action);
		if (in_array($key, array_keys(Common::getConfig('cacheConfig')))) {

			$body = $response->getBody();
			$img = array();
			$pattern = "/<img(.[^<]*)src=\"?(.[^<\"]*)\"?(.[^<]*)\/?>/is";
			if(preg_match_all($pattern, $body, $p)){
				foreach($p[2] as $path){
					$imgs[] = $path;
				}
			}

			$file = sprintf("%scache/APPC_%s.php", Common::getConfig('siteConfig', 'dataPath'), $key);
			if (!file_exists($file)) {
				Util_File::savePhpData($file, $imgs);
			}
			$new_version = crc32(json_encode($imgs));
			$files = include $file;

			if (crc32(json_encode($files)) !== $new_version) {
				Util_File::savePhpData($file, $imgs);
			}
		}
	}

}
