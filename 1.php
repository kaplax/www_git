<?php  
header( 'Content-type:text/html;charset=utf8' );
mysql_query("set names utf8");
$servername = "56f0ace535bc8.sh.cdb.myqcloud.com";
$username = "root";
$pwd = 'kk851228!@';
$db = 'oms';
$port = '4921';
// 创建连接
$conn = mysqli_connect($servername, $username,$pwd,$db,$port);

// 检测连接
if (!$conn) {
    echo 'Could not connect: ' . iconv('gbk', 'utf-8', mysqli_connect_error());
}
else{
    echo 'success';
}
?> 