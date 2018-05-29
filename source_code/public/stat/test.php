<?php

$rq = array(
    1 => array(
        'host' => 'redis.ad.queue1.ildyx.com',
        'port' => '6379',
        'key-prefix' => 'adx',
        'password' => 'ZxEXuArl0Viw'
    ),

                            2 => array (
                                    'host' => 'redis.ad.queue2.ildyx.com',
                                    'port' => '6379',
                                    'key-prefix' => 'adx',
                                    'password' => 'ZxEXuArl0Viw'
                            ) ,
    //                        3 => array (
    //                                'host' => 'redis.ad.queue3.ildyx.com',
    //                                'port' => '6379',
    //                                'key-prefix' => 'adx',
    //                                'password' => 'ZxEXuArl0Viw'
    //                        ) ,
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
);

//$rq=array("red","green","blue","yellow","brown");
$random_keys=array_rand($rq);
var_dump($rq[$random_keys]);
exit;





$ipv6 = "2001:4860:a005::68";
$ipv4 = "255.255.255.255";

function ip2bin($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) return base_convert(ip2long($ip), 10, 2);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) return false;
    if (($ip_n = inet_pton($ip)) === false) return false;
    $bits = 15; // 16 x 8 bit = 128bit (ipv6)
    while ($bits >= 0) {
        $bin = sprintf("%08b", (ord($ip_n[$bits])));
        $ipbin = $bin . $ipbin;
        $bits--;
    }
    return $ipbin;
}

if (filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
    echo "@4";
} else {
    echo "@6";
}
        // return base_convert(ip2long($ip),10,2);

echo ip2bin($ipv4) . "\n";
echo base_convert(ip2bin($ipv6), 2, 10);

// echo strtotime("2016-09-22");
// echo strtotime("2016-09-23");
exit;




// ALTER TABLE `original_data` PARTITION by RANGE(created_time)(
// PARTITION p20160921 VALUES less than (1474473600),
// PARTITION p20160921 VALUES less than (1474473600),
// PARTITION p2016 VALUES less than MAXVALUE
// );
// alter table original_data add partition(partition p20160922 values less than 1474560000);
// alter table `original_data` add partition(partition p20160922 values less than MAXVALUE);