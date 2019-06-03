

1、修改server/config.php中的配置信息

2、cli运行php ws.php

3、修改client/index.html中的js代码var wsServer = 'ws://127.0.0.1:8888'，修改其中的host和端口号，端口号为config.php中配置的ws_port
4、使用web服务器或者浏览器直接访问index.html

5、修改client/push.php里的host和端口号，端口号为config.php中配置的tcp_port，push.php中的代码可以在php-fpm环境中运行，实现在日常项目中发推送消息给websoket服务器



<a href="http://im.sh-jinger.com/">在线预览</a>

<a href="http://im.sh-jinger.com/push.php">发推送</a>
