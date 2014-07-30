<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|----------------------------------------
| Dnspod
|----------------------------------------  
*/

$config['dnspod_account'] = array (
    "login_email" => "",
    "login_password" => "",
    "format" => "json"
);
$config['dnspod_url'] = array (
		"domain_list" => "https://dnsapi.cn/Domain.List",//获取域名列表
		"domain_create" => "https://dnsapi.cn/Domain.Create",//添加新域名
		"domain_remove" => "https://dnsapi.cn/Domain.Remove",//删除域名
		"record_create" => "https://dnsapi.cn/Record.Create",//添加记录
		"record_modify" => "https://dnsapi.cn/Record.Modify",//修改记录
		"record_list" => "https://dnsapi.cn/Record.List"//记录列表
// 		"" => "",//
// 		"" => "",//
// 		"" => "",//
// 		"" => "",//
// 		"" => "",//
// 		"" => "",//
// 		"" => "",//
// 		"" => ""//
);

/* End of file config.php */
/* Location: ./application/config/config.php */
