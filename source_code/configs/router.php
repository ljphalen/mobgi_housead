<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');
/**
 * 正则路由设置
 * 'key1' => new Yaf_Route_Regex('#test1.html#',
 * array('module' => 'api','controller' => 'test', 'action' => 'test'),
 * //映射
 * array(1=>'key1')
 * ),
 * 'key2' => new Yaf_Route_Regex('#test2.html#',
 * array('module' => 'api','controller' => 'test', 'action' => 'test2'),
 * //映射
 * array(1=>'key2')
 * )
 */
return [
	// 广告主模块
	'advertiser0' => new Yaf_Route_Regex ('#/v1/advertiser/read#', array(
		'module' => 'api',
		'controller' => "Sona_V1_Advertiser",
		'action' => 'read'
	), array(
		1 => 'key0'
	)),
	'advertiser1' => new Yaf_Route_Regex ('#/v1/advertiser/token#', array(
		'module' => 'api',
		'controller' => "Sona_V1_Advertiser",
		'action' => 'token'
	), array(
		1 => 'key0'
	)),
	// 投放单元模块
	'key1' => new Yaf_Route_Regex ('#/v1/unit/create#', array(
		'module' => 'api',
		'controller' => "Sona_V1_Unit",
		'action' => 'create'
	), array(
		1 => 'key1'
	)),
	'key2' => new Yaf_Route_Regex ('#/v1/unit/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Unit',
		'action' => 'read'
	), array(
		1 => 'key2'
	)),
	'key3' => new Yaf_Route_Regex ('#/v1/unit/update#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Unit',
		'action' => 'update'
	), array(
		1 => 'key3'
	)),
	'key4' => new Yaf_Route_Regex ('#/v1/unit/select#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Unit',
		'action' => 'select'
	), array(
		1 => 'key4'
	)),
	'key5' => new Yaf_Route_Regex ('#/v1/unit/delete#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Unit',
		'action' => 'delete'
	), array(
		1 => 'key5'
	)),
	// 广告模块
	'key6' => new Yaf_Route_Regex ('#/v1/ad/create#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Ad',
		'action' => 'create'
	), array(
		1 => 'key6'
	)),
	'key7' => new Yaf_Route_Regex ('#/v1/ad/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Ad',
		'action' => 'read'
	), array(
		1 => 'key7'
	)),
	'key8' => new Yaf_Route_Regex ('#/v1/ad/update#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Ad',
		'action' => 'update'
	), array(
		1 => 'key8'
	)),
	'key9' => new Yaf_Route_Regex ('#/v1/ad/select#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Ad',
		'action' => 'select'
	), array(
		1 => 'key9'
	)),
	'key10' => new Yaf_Route_Regex ('#/v1/ad/delete#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Ad',
		'action' => 'delete'
	), array(
		1 => 'key10'
	)),
	// 创意模块
	'key11' => new Yaf_Route_Regex ('#/v1/originality/create#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Originality',
		'action' => 'create'
	), array(
		1 => 'key11'
	)),
	'key12' => new Yaf_Route_Regex ('#/v1/originality/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Originality',
		'action' => 'read'
	), array(
		1 => 'key12'
	)),
	'key13' => new Yaf_Route_Regex ('#/v1/originality/update#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Originality',
		'action' => 'update'
	), array(
		1 => 'key13'
	)),
	'key14' => new Yaf_Route_Regex ('#/v1/originality/select#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Originality',
		'action' => 'select'
	), array(
		1 => 'key14'
	)),
	'key15' => new Yaf_Route_Regex ('#/v1/originality/delete#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Originality',
		'action' => 'delete'
	), array(
		1 => 'key15'
	)),
	// 定向模块
	'key16' => new Yaf_Route_Regex ('#/v1/direct/create#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Direct',
		'action' => 'create'
	), array(
		1 => 'key16'
	)),
	'key17' => new Yaf_Route_Regex ('#/v1/direct/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Direct',
		'action' => 'read'
	), array(
		1 => 'key17'
	)),
	'key18' => new Yaf_Route_Regex ('#/v1/direct/update#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Direct',
		'action' => 'update'
	), array(
		1 => 'key18'
	)),
	'key19' => new Yaf_Route_Regex ('#/v1/direct/select#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Direct',
		'action' => 'select'
	), array(
		1 => 'key19'
	)),
	'key20' => new Yaf_Route_Regex ('#/v1/direct/delete#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Direct',
		'action' => 'delete'
	), array(
		1 => 'key20'
	)),
	// 图片模块
	'key21' => new Yaf_Route_Regex ('#/v1/image/create#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Image',
		'action' => 'create'
	), array(
		1 => 'key21'
	)),
	'key22' => new Yaf_Route_Regex ('#/v1/image/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Image',
		'action' => 'read'
	), array(
		1 => 'key22'
	)),
	// 流媒体模块
	'key26' => new Yaf_Route_Regex ('#/v1/video/create#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Video',
		'action' => 'create'
	), array(
		1 => 'key26'
	)),
	'key27' => new Yaf_Route_Regex ('#/v1/video/read#', array(
		'module' => 'api',
		'controller' => 'Sona_V1_Video',
		'action' => 'read'
	), array(
		1 => 'key27'
	)),
	'report_stat' => new Yaf_Route_Regex ('#report_stat#', array(
		'module' => 'Stat',
		'controller' => 'Data',
		'action' => 'collect'
	), array()),
	'report_api' => new Yaf_Route_Regex ('#report_api#', array(
		'module' => 'Stat',
		'controller' => 'report',
		'action' => 'api'
	), array(
		1 => 'report_api'
	)),
	// adx模块
	'key28' => new Yaf_Route_Regex ('#/adx/v1/get[A a]{1}d[l L]{1}ist#', array(
		'module' => 'api',
		'controller' => 'Adx_V1_Ad',
		'action' => 'getAdList'
	), array(
		1 => 'key28'
	)),
	'key29' => new Yaf_Route_Regex ('#/adx/v1/get[H h]{1}ousead[D d]{1}sp#', array(
		'module' => 'api',
		'controller' => 'Adx_V1_Housead',
		'action' => 'getAdInfo'
	), array(
		1 => 'key29'
	)),
	// stat模块
	'key30' => new Yaf_Route_Regex ('#/adx/v1/report#', array(
		'module' => 'Stat',
		'controller' => 'Adx_V1_Stat',
		'action' => 'collect'
	),[]),
	'key31' => new Yaf_Route_Regex ('#/adx/v1/server#', array(
		'module' => 'Stat',
		'controller' => 'Adx_V1_Test',
		'action' => 'dsp'
	),[]),
	'key33' => new Yaf_Route_Regex ('#stat#', array(
		'module' => 'Stat',
		'controller' => 'Mobgi_Stat',
		'action' => 'stat'
	), array(
		1 => 'key33'
	)),
	'key34' => new Yaf_Route_Regex ('#Stat/Data/collect#', [
		'module' => 'Stat',
		'controller' => 'Housead_Stat',
		'action' => 'collect'
	],[]),
	'key35' => new Yaf_Route_Regex ('#/adx/v1/get[Tt]{1}oken#', array(
		'module' => 'api',
		'controller' => 'Adx_V1_Ad',
		'action' => 'getToken'
	), array(
		1 => 'key35'
	)),
	// adx模块,新交互协议
	'key36' => new Yaf_Route_Regex ('#/adx/v2/[Dd]{1}sp#', array(
		'module' => 'api',
		'controller' => 'Adx_V2_Dsp',
		'action' => 'config'
	), array(
		1 => 'key36'
	)),
	'key37' => new Yaf_Route_Regex ('#/adx/v2/[Ii]{1}ntergration#', array(
		'module' => 'api',
		'controller' => 'Adx_V2_Intergration',
		'action' => 'config'
	), array(
		1 => 'key37'
	)),
	// 老聚合接口
	'key38' => new Yaf_Route_Regex ('#/[v V]{1}ideo[a A]{1}ds/get[p P]{1}ic[a A]{1}d[l L]{1}ist#', array(
		'module' => 'api',
		'controller' => 'Mobgi_VideoAds',
		'action' => 'getAdList'
	), array(
		1 => 'key38'
	)),
	'key39' => new Yaf_Route_Regex ('#/[v V]{1}ideo[a A]{1}ds/[l L]{1}ists#', array(
		'module' => 'api',
		'controller' => 'Mobgi_VideoAds',
		'action' => 'lists'
	), array(
		1 => 'key39'
	)),
	'key40' => new Yaf_Route_Regex ('#/[a A]{1}ggr/[c C]{1}onfig#', array(
		'module' => 'api',
		'controller' => 'Mobgi_Aggr',
		'action' => 'config'
	), array(
		1 => 'key40'
	)),
	'key41' => new Yaf_Route_Regex ('#/[a A]{1}ggr/[r R]{1}eport#', array(
		'module' => 'api',
		'controller' => 'Mobgi_Aggr',
		'action' => 'report'
	), array(
		1 => 'key41'
	)),
	'key42' => new Yaf_Route_Regex ('#/adx/v2/[Tt]{1}rial#', array(
		'module' => 'api',
		'controller' => 'Adx_V2_Trial',
		'action' => 'config'
	), array(
		1 => 'key42'
	)),
	#临时页面支持
	#神庙落地页
	'key43' => new Yaf_Route_Regex ('#/smcoupon#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'index'
	), array(
		1 => 'key43'
	)),
	#好时光
	'key44' => new Yaf_Route_Regex ('#/gtcoupon1#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail1'
	), array(
		1 => 'key44'
	)),
	'key45' => new Yaf_Route_Regex ('#/gtcoupon2#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail2'
	), array(
		1 => 'key45'
	)),
	'key46' => new Yaf_Route_Regex ('#/gtcoupon3#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail3'
	), array(
		1 => 'key46'
	)),
	'key47' => new Yaf_Route_Regex ('#/gtcoupon4#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail4'
	), array(
		1 => 'key47'
	)),
	'key48' => new Yaf_Route_Regex ('#/gtcoupon5#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail5'
	), array(
		1 => 'key48'
	)),
	'key49' => new Yaf_Route_Regex ('#/jdiojwoandahiwguq123#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'jdiojwoandahiwguq123'
	), array(
		1 => 'key49'
	)),
	'key50' => new Yaf_Route_Regex ('#/gtcoupon6#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail6'
	), array(
		1 => 'key50'
	)),
	'key51' => new Yaf_Route_Regex ('#/gtcoupon7#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail7'
	), array(
		1 => 'key51'
	)),
	'key52' => new Yaf_Route_Regex ('#/gtcoupon8#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail8'
	), array(
		1 => 'key52'
	)),
	'key53' => new Yaf_Route_Regex ('#/gtcoupon9#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail9'
	), array(
		1 => 'key53'
	)),
	'key54' => new Yaf_Route_Regex ('#/gtcoupon10#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail10'
	), array(
		1 => 'key54'
	)),
	'key55' => new Yaf_Route_Regex ('#/gtcoupon11#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail11'
	), array(
		1 => 'key55'
	)),
	'key56' => new Yaf_Route_Regex ('#/gtcoupon12#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail12'
	), array(
		1 => 'key56'
	)),

	'key57' => new Yaf_Route_Regex ('#/gtcoupon13#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail13'
	), array(
		1 => 'key57'
	)),
	'key58' => new Yaf_Route_Regex ('#/gtcoupon14#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail14'
	), array(
		1 => 'key58'
	)),
	'key59' => new Yaf_Route_Regex ('#/gtcoupon15#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail15'
	), array(
		1 => 'key59'
	)),
	'key60' => new Yaf_Route_Regex ('#/gtcoupon16#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail16'
	), array(
		1 => 'key60'
	)),
	'key61' => new Yaf_Route_Regex ('#/gtcoupon17#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail17'
	), array(
		1 => 'key61'
	)),
	'key62' => new Yaf_Route_Regex ('#/gtcoupon18#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail18'
	), array(
		1 => 'key62'
	)),
	#神庙落地页
	'key63' => new Yaf_Route_Regex ('#/dtcoupon#', array(
		'module' => 'coupon',
		'controller' => 'Index',
		'action' => 'couponDetail19'
	), array(
		1 => 'key63'
	)),
	#互动广告的活动页面
	'iaad_activity' => new Yaf_Route_Regex ('#/iaad/activity#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'activity'], []),
	'iaad_verifyGoods' => new Yaf_Route_Regex ('#/iaad/verifyGoods#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'verifyGoods'], []),
	'iaad_getUser' => new Yaf_Route_Regex ('#/iaad/getUser#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'getUser'], []),
	'iaad_goods' => new Yaf_Route_Regex ('#/iaad/goods#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'goods'], []),
	'iaad_draw' => new Yaf_Route_Regex ('#/iaad/draw#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'draw'], []),
	'iaad_prize' => new Yaf_Route_Regex ('#/iaad/prize#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'prize'], []),
	'iaad_postVerifyGoods' => new Yaf_Route_Regex ('#/iaad/postVerifyGoods#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'postVerifyGoods'], []),
	'iaad_qrCode' => new Yaf_Route_Regex ('#/iaad/qrCode#',
		['module' => 'coupon',
		'controller' => 'Iaad',
		'action' => 'getQrCode'], []),
	'iaad_Uuid' => new Yaf_Route_Regex ('#/iaad/getUuid#',
		['module' => 'coupon',
			'controller' => 'Iaad',
			'action' => 'getUuid'], []),


];
