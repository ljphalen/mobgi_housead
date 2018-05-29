<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');
$config = array(
	'test' => array(
		'default' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'housead'
			)
		),
		'adx' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			)
		),
		'mobgi' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			)
		),
		'spm' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '9310',
				'key-prefix' => ''
			)
		),
		// 聚合广告位变更日志
		'intergration_position_list' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
		),
		'interative_ad_list' => array(
			1 => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'interative',
			)
		),

	),
	'product' => array(
		'default' => array(
			1 => array(
				'host' => 'redis.ad.queue1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			)
		),
		'adx' => array(
			1 => array(
				'host' => 'redis.ad.queue1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			)
		),
		'mobgi' => array(
			1 => array(
				'host' => 'redis.ad.queue1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			4 => array(
				'host' => 'redis.ad.queue4.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			5 => array(
				'host' => 'redis.ad.cache.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			)
		),
		'spm' => array(
			1 => array(
				'host' => 'redis.ad.spm.queue.ildyx.com',
				'port' => '6379',
				'key-prefix' => '',
				'password' => 'ZxEXuArl0Viw'
			)
		),
		'intergration_position_list' => array(
			1 => array(
				'host' => 'redis.ad.queue1.ildyx.com',
				'port' => '6379',
				'password' => 'ZxEXuArl0Viw'
			)
		),
		'interative_ad_list' => array(
			1 => array(
				'host' => 'redis.ad.queue1.ildyx.com',
				'port' => '6379',
				'password' => 'ZxEXuArl0Viw',
				'key-prefix' => 'interative',
			)
		),

	),
	'develop' => array(
		'default' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'housead',
				'password' => '123456'
			)
		),
		'adx' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			)
		),
		'mobgi' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			2 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			)
		),
		'spm' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '9310',
				'key-prefix' => ''
			)
		),
		'intergration_position_list' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'password' => '123456'
			)
		),
		//互动广告
		'interative_ad_list' => array(
			1 => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'password' => '123456',
				'key-prefix' => 'interative',
			)
		),
	)
);
return defined('ENV') ? $config [ENV] : $config ['product'];
