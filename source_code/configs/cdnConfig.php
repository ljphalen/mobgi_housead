<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

$cdnConfig['publish_xml'] = '<?xml%%20version="1.0"%%20encoding="UTF-8"%%20?>'.
		'<ccsc>'.
			'<cust_id>'.
				'%s'.
			'</cust_id>'.
			'<passwd>'.
				'%s'.
			'</passwd>'.
			'<publish_report>'.
				'%s'.
			'</publish_report>'.
			'<item_id%%20value="%s">'.
				'<source_path>'.
					'%s'.
				'</source_path>'.
				'<publish_path>'.
					'%s'.
				'</publish_path>'.
				'<md5>'.
					'%s'.
				'</md5>'.
				'<fsize>'.
				'</fsize>'.
			'</item_id>'.
		'</ccsc>';
//CDN发布的xml格式  要先传入 cust_id,checkcode,report,item_id,sourch_path,publish_path,checkfile;
$cdnConfig['delete_xml'] = '<?xml%20version="1.0"%20encoding="UTF-8"%20?>'.
		'<ccsc>'.
			'<cust_id>'.
				'%s'.
			'</cust_id>'.
			'<passwd>'.
				'%s'.
			'</passwd>'.
			'<item_id%20value="%s">'.
				'<source_path>'.
					'%s'.
				'</source_path>'.
				'<publish_path>'.
					'%s'.
				'</publish_path>'.
			'</item_id>'.
		'</ccsc>';
//CDN删除的xml格式  要先传入 cust_id,passwd,item_id,sourch_path,publish_path;删除情况下，source_path和publish_path是一样的

$cdnConfig["cdn_username"] = "idreamsky_ad";
$cdnConfig["cdn_pwd"] = "fhNyS3C5PRCu";
$cdnConfig["cdn_url"] = "http://fd.chinanetcenter.com:8080/HttpUpdate/publishService.do";
$cdnConfig["cdn_path"] = "https://dl2.gxpan.cn/ad";
//是否发不到cdn
$cdnConfig['IS_SEND_CDN'] = false;
return $cdnConfig;