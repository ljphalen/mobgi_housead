<?php
include 'common.php';

$result= Util_PHPMailer_SendMail::postEmail('369775049@qq.com','标题', '内容'); var_dump($result);die;
