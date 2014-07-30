<?php
$url = 'https://dnsapi.cn/User.Detail';

$post_data = array (
    "login_email" => "",
    "login_password" => "",
    "format" => "json"
);
$b_data = array("wd"=>"a");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://www.baidu.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 我们在POST数据哦！
curl_setopt($ch, CURLOPT_POST, 1);
// 把post的变量加上
curl_setopt($ch, CURLOPT_POSTFIELDS, $b_data);
$output = curl_exec($ch);
var_dump($output);
curl_close($ch);
echo $output;
?>