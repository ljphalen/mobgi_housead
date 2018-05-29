<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Marketing_TargetingsController extends Admin_MarketingController {
    const  CACHE_EXPRIE = 604800;
    public $resourceName = 'targetings';
    private $targetingCond = [
        'age' => [
            ['max_len', [0, 250], '', null, true],
        ],
        'gender' => [
            ['max_len', [0, 250], '', null, true],
        ],
    ];


    //创建定向（targetings/add）
    public function addAction() {
        $param = $this->getParams([
            'targeting_name' => [
                ['max_len', [0, 120], '', null, true],
            ],
            'description' => [
                ['max_len', [0, 250], '', null, true],
            ],
            'targeting' => [
                ['isset', null, '必须是对象', $this->targetingCond, true],
            ],
        ]);

        if ($param['targeting']) {
            $targeting = [];
            foreach ($param['targeting'] as $key => $val) {
                if (!empty($val)) {
                    $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                }
            }
            $param['targeting'] = json_encode($targeting);
        }

        $result = $this->send($param, substr(__FUNCTION__, 0, -6));
        $ret = json_decode($result, TRUE);
        $this->output($ret['code'], $ret['message'], $ret['data']);
    }

    //更新定向（targetings/update）
    public function updateAction() {
        $param = $this->getParams([
            'targeting_id' => [
                ['int']
            ],
            'targeting_name' => [

                ['max_len', [0, 120], '', null, true],

            ],
            'description' => [
                ['max_len', [0, 250], '', null, true],
            ],
            'targeting' => [
                ['isset', null, '必须是对象', $this->targetingCond],

            ],
        ]);


        if ($param['targeting']) {
            $targeting = [];
            foreach ($param['targeting'] as $key => $val) {
                if (!empty($val)) {
                    $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                }
            }
            $param['targeting'] = json_encode($targeting);
        }

        $result = $this->send($param, substr(__FUNCTION__, 0, -6));
        $ret = json_decode($result, TRUE);
        $this->output($ret['code'], $ret['message'], $ret['data']);
    }

    //获取定向（targetings/get）
    public function getAction() {
        //        $subCond = [
        //            function ($vals) {
        //                foreach ($vals as $val) {
        //                    $this->getParams([
        //                        'field' => [['=', 'targeting_name', 'field!=targeting_name']],
        //                        'operator' => [['in', ['EQUALS', 'CONTAINS'], 'operator 不在定义范围 [\'EQUALS\', \'CONTAINS\'] ']],
        //                        'values' => [['count_range', [1, 120]]],
        //                    ], (array)$val);
        //                }
        //                return [true, $vals];
        //            }
        //        ];
        $param = $this->getParams([
            'targeting_id' => [
                ['int', null, '', null, true],

            ],
            'filtering' => [
                ['trim', null, '', null, true],
            ],
            'page' => [
                ['default', 1],
                ['between', [1, 99999]],
            ],
            'page_size' => [
                ['default', 10],
                ['between', [1, 100]],
            ],
        ]);

        if ($param['filtering']) {
            $filter = [
                'field' => "targeting_name",
                "operator" => "CONTAINS",
                "values" => [$param['filtering']]
            ];

            $param['filtering'] = json_encode([$filter]);
        }

        $result = $this->send($param, substr(__FUNCTION__, 0, -6));
        $ret = json_decode($result, TRUE);
        if (isset($ret['data']['list'])) {
            foreach ($ret['data']['list'] as $key => $item) {
                $ret['data']['list'][$key]['created_time'] = date('Y-m-d H:i:s', $item['created_time']);
                $ret['data']['list'][$key]['last_modified_time'] = date('Y-m-d H:i:s', $item['last_modified_time']);
                if (isset($item['targeting']['gender'])) {
                    $ret['data']['list'][$key]['targeting']['gender'] = $item['targeting']['gender'][0];
                }
                if (isset($item['targeting']['app_install_status'])) {
                    $ret['data']['list'][$key]['targeting']['app_install_status'] = $item['targeting']['app_install_status'][0];
                }
                if (isset($item['targeting']['customized_audience'])) {
                    $audience = [];
                    $excludedAudience = [];
                    foreach ($item['targeting']['customized_audience'] as $val) {
                        if ($val > 0) {
                            $audience[] = $val;
                        } else {
                            $excludedAudience[] = abs($val);
                        }
                    }
                    if (count($audience) and !isset($item['targeting']['custom_audience'])) {
                        $ret['data']['list'][$key]['targeting']['custom_audience'] = $audience;
                    }
                    if (count($excludedAudience) and !isset($item['targeting']['excluded_custom_audience'])) {
                        $ret['data']['list'][$key]['targeting']['excluded_custom_audience'] = $excludedAudience;
                    }
                }

            }
        }


        $this->output($ret['code'], $ret['message'], $ret['data']);
    }

    //删除定向（targetings/delete）
    public function deleteAction() {
        $param = $this->getParams([
            'targeting_id' => [
                ['int', null, '', null, true],
            ],
        ]);
        $result = $this->send($param, substr(__FUNCTION__, 0, -6));
        $this->output(0, 'ok', $result);
    }

    //获取定向标签（targetings/getTags）
    public function getTagsAction() {
        $subCond = [
            'type' => [['notin', ['LOCATION', 'DISTRICT'], 'in [\'LOCATION\', \'DISTRICT\'],region_id is need']],
        ];
        $param = $this->getParams([
            'type' => [
                'trim',
                ['in', ['REGION', 'LOCATION', 'BUSINESS_INTEREST', 'APP_CATEGORY', 'DISTRICT'], '标签类型不符合0'],
            ],
            'region_id' => [

                ['int', null, '', null, true],
                ['=', null, '', $subCond, true],
            ]
        ]);
        $result = $this->send($param, 'get', 'targeting_tags');
        $ret = json_decode($result, TRUE);
        $this->output($ret['code'], $ret['message'], $ret['data']);
    }

    //获取定向标签（targetings/getTags）
    public function getTreeAction() {
        $subCond = [
            'type' => [['notin', ['LOCATION', 'DISTRICT'], 'in [\'LOCATION\', \'DISTRICT\'],region_id is need']],
        ];
        $param = $this->getParams([
            'type' => [
                'trim',
                ['in', ['REGION', 'LOCATION', 'BUSINESS_INTEREST', 'APP_CATEGORY', 'DISTRICT'], '标签类型不符合0'],
            ],
            'region_id' => [
                ['int', null, '', null, true],
                ['=', null, '', $subCond, true],
            ]
        ]);
        $result = $this->send($param, 'get', 'targeting_tags');
        $ret = json_decode($result, TRUE);
        $result = [];
        if ($ret['data']['list']) {
            foreach ($ret['data']['list'] as $item) {
                if (isset($param['type']) and $param['type'] == 'REGION') {
                    if ($item['id'] == 1156) {
                        $item['parent_id'] = 0;
                    }
                    if ($item['id'] == 156) {
                        $item['parent_id'] = 1156;
                    }
                    if ($item['parent_id'] == 0 and $item['id'] > 10000) {
                        $item['parent_id'] = 1156;
                    }
                }

                $result[] = [
                    'id' => $item['id'],
                    'pid' => $item['parent_id'],
                    'label' => $item['name']

                ];
            }
            if (isset($param['type']) and $param['type'] == 'REGION') {
                $map = [];
                foreach ($result as $item) {
                    $map[$item['pid']][$item[id]] = 1;
                }

                foreach ($result as $item) {
                    if (($item['id'] > 100000 and $item['id'] < 999999 and !isset($map[$item['id']]) and $item['id'] % 10 == 0) or in_array($item['id'], [
                            110000,
                            120000,
                            310000,
                            500000
                        ])
                    ) {
                        $ret = $this->getLocation($item['id']);
                        if ($ret) {
                            $result = array_merge($result, $ret);
                        }
                    }

                }
            }
        }
        $this->output($ret['code'], $ret['message'], $result);
    }


    public function getLocation($id) {
        $redis = $this->getCache();
        $key = 'location_' . $id;
        $result = $redis->get($key);
        if ($result === false) {
            $param = [
                'type' => 'LOCATION',
                'region_id' => $id
            ];
            $result = $this->send($param, 'get', 'targeting_tags');
            $ret = json_decode($result, TRUE);
            $result = [];
            if ($ret['data']['list']) {
                foreach ($ret['data']['list'] as $item) {
                    $result[] = [
                        'id' => $item['id'],
                        'pid' => $item['parent_id'] ?: $id,
                        'label' => $item['name']
                    ];
                }
            }
            $redis->set($key, $result, self::CACHE_EXPRIE + rand(0, 604800));
        }
        return $result;
    }

    private function getCache() {
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'AD_USER_CACHE_REDIS_SERVER0');
        return $cache;
    }
}