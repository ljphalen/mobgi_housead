<?php
if (! defined ( 'BASE_PATH' )) exit ( 'Access Denied!' );

class Adx_Api_V2_BaseController extends Adx_Api_BaseController {
    public function init() {
        parent::init();
    
    }
    
    
    public function __destruct() {
        if ($this->isReportToMonitor == 1) {
            $execTime = intval((microtime(true) - $this->sTime) * 1000);
            $action = $this->getRequest()->getActionName();
            $controller = $this->getRequest()->getControllerName();
            $name = $controller .'_'. $action . '_' . $this->mAppKey . '_' . Util_ErrorCode::$mReportCodeDesc  [$this->mReportCode];
            if ($this->mReportCode == Util_ErrorCode::FITER_CONFIG) {
                $name = $controller.'_'. $action . '_' . $this->mAppKey . '_' . $this->mReportData . '_' . Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
            }
            Common::sendLogAccess(0, 'ads', $name, $this->mReportMsg, $execTime);
        }
    }
    
    
}