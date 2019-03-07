<?php

error_reporting(E_ALL^E_NOTICE);

require_once __DIR__."/../ActPHP/ActServer.php";
use ActPHP\ActClient;
		
for ($i=0; $i <100000 ; $i++) { 
	$fd = stream_socket_client("tcp://120.79.133.221:80",$errno,$errstr,10);
	if(!$fd){
		exit("socket create error {$errno} : $errstr");
	}
	$str = "Host: yiguan.dolit.cn
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:62.0) Gecko/20100101 Firefox/62.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2
Accept-Encoding: gzip, deflate
Cookie: Hm_lvt_3554c848b256e6f1af1636047be45f7d=1535439235
Connection: keep-alive
Upgrade-Insecure-Requests: 1
Pragma: no-cache
Cache-Control: no-cache

asas
";
	$len = fwrite($fd, $str);
}
		
