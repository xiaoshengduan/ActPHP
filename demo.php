<?php


// 1 构建stream
$context_arr = [];
$context = stream_context_create($context_arr);  //为资源创建上下文
$stream = stream_socket_server("tcp://0.0.0.0:9999",$error_no, $error_msg,STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);  //创建socket服务

$socket = socket_import_stream($stream);
@socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);  //设置持久连接
@socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);      //禁用Nagle延迟发送算法
stream_set_blocking($stream,0);  //设置流为非阻塞模式
stream_set_read_buffer($stream, 0);  //设置读取操作无缓冲

$event_base = new \EventBase();
$fd_key = (int)$stream;
$event = new \Event($event_base, $stream, \Event::READ | \Event::PERSIST, $func, $fd); //设置\Event::PERSIST与不设置\Event::PERSIST有什么区别？
if (!$event || !$event->add()) {
    return false;
}

$this->allEvents[$fd_key][$flag] = $event;




$event_base = new \EventBase();

$event = new \Event($event_base,);