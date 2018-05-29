<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');
$config = array(
	'test' => array(
		'default' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'housead'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'housead'
				),
				'2' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'housead'
				)
			)
		),
		'adx_default' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				),
				'2' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				)
			)
		),
		// 保存ip信息
		'ip_info_1' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				)
			)
		),
		'ip_info_2' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				)
			)
		),
		'charge_info' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				)
			)
		),
		'ab_info' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378',
				'key-prefix' => 'adx'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378',
					'key-prefix' => 'adx'
				)
			)
		),
		'spm' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '8002',
				'key-prefix' => ''
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '8002',
					'key-prefix' => ''
				)
			)
		),
		// 付费用户,活跃用户
		'AD_USER_CACHE_REDIS_SERVER0' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378	'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER1' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER2' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '6378'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '6378'
				)
			)
		)
	),
	'product' => array(
		'default' => array(
			'write' => array(
				'host' => 'redis.ad.api.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'adx_default' => array(
			'write' => array(
				'host' => 'redis.ad.api.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'adx',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		// 保存ip信息
		'ip_info_1' => array(
			'write' => array(
				'host' => 'redis.ad.api.ipinfo1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.ipinfo1.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'adx',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'ip_info_2' => array(
			'write' => array(
				'host' => 'redis.ad.api.ipinfo2.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.ipinfo2.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'adx',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),

		'charge_info' => array(
			'write' => array(
				'host' => 'redis.ad.api.chareinfo1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.chareinfo1.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'adx',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'ab_info' => array(
			'write' => array(
				'host' => 'redis.ad.api.abtestinfo1.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'adx',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.abtestinfo1.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'adx',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),

		'spm' => array(
			'write' => array(
				'host' => 'redis.ad.spm.cache.ildyx.com',
				'port' => '6379',
				'key-prefix' => '',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.spm.cache.ildyx.com',
					'port' => '6379',
					'key-prefix' => '',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		// 付费用户,活跃用户
		'AD_USER_CACHE_REDIS_SERVER0' => array(
			'write' => array(
				'host' => 'redis.ad.user1.ildyx.com',
				'port' => '6379',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.user1.ildyx.com',
					'port' => '6379',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER1' => array(
			'write' => array(
				'host' => 'redis.ad.user2.ildyx.com',
				'port' => '6379',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.user2.ildyx.com',
					'port' => '6379',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER2' => array(
			'write' => array(
				'host' => 'redis.ad.user3.ildyx.com',
				'port' => '6379',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.user3.ildyx.com',
					'port' => '6379',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		)
	),
	'develop' => array(
		'default' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'housead',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'housead',
					'password' => '123456'
				)
			)
		),
		'adx_default' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'adx',
					'password' => '123456'
				)
			)
		),
		// 保存ip信息
		'ip_info_1' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'adx',
					'password' => '123456'
				)
			)
		),
		'ip_info_2' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'adx',
					'password' => '123456'
				)
			)
		),

		'charge_info' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'adx',
					'password' => '123456'
				)
			)
		),
		'ab_info' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'key-prefix' => 'adx',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'key-prefix' => 'adx',
					'password' => '123456'
				)
			)
		),
		'spm' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '8002',
				'key-prefix' => ''
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '8002',
					'key-prefix' => ''
				)
			)
		),
		// 付费用户,活跃用户
		'AD_USER_CACHE_REDIS_SERVER0' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'password' => '123456'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER1' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'password' => '123456'
				)
			)
		),
		'AD_USER_CACHE_REDIS_SERVER2' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '6378',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '6378',
					'password' => '123456'
				)
			)
		)
	)
);
return defined('ENV') ? $config [ENV] : $config ['product'];
