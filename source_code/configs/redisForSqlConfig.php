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
				)
			)
		),
		'abTest' => array(
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
				)
			)
		),
		'flowInfo' => array(
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
				)
			)
		),
		'houseadInfo' => array(
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
				)
			)
		),
		'adsRelInfo' => array(
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
				)
			)
		),
		'flowInfo2' => array(
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
				)
			)
		),
		'spm' => array(
			'write' => array(
				'host' => '127.0.0.1',
				'port' => '8002',
				'key-prefix' => 'spm'
			),
			'read' => array(
				'1' => array(
					'host' => '127.0.0.1',
					'port' => '8002',
					'key-prefix' => 'spm'
				)
			)
		)
	),
	'product' => array(
		'default' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'abTest' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql2.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql2.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'flowInfo' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql3.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql3.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'houseadInfo' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql4.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql4.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'adsRelInfo' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql5.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql5.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'flowInfo2' => array(
			'write' => array(
				'host' => 'redis.ad.api.sql6.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'housead',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.api.sql6.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'housead',
					'password' => 'ZxEXuArl0Viw'
				)
			)
		),
		'spm' => array(
			'write' => array(
				'host' => 'redis.ad.spm.cache.ildyx.com',
				'port' => '6379',
				'key-prefix' => 'spm',
				'password' => 'ZxEXuArl0Viw'
			),
			'read' => array(
				'1' => array(
					'host' => 'redis.ad.spm.cache.ildyx.com',
					'port' => '6379',
					'key-prefix' => 'spm',
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
		'abTest' => array(
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
		'flowInfo' => array(
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
		'houseadInfo' => array(
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
		'adsRelInfo' => array(
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
		'flowInfo2' => array(
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
		'spm' => array(
			'write' => array(
				'host' => '192.168.141.216',
				'port' => '8002',
				'key-prefix' => 'spm',
				'password' => '123456'
			),
			'read' => array(
				'1' => array(
					'host' => '192.168.141.216',
					'port' => '8002',
					'key-prefix' => 'spm',
					'password' => '123456'
				)
			)
		)
	)
);
return defined('ENV') ? $config [ENV] : $config ['product'];