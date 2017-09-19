<?php
/**
 * 非Yii函数公共静态类库 
 * @since 2011-8-22
 */
class Common {
	/**
	 * 生成对应的type
	 */
	public static function getType()
	{
		$ret_array		=	array( 'ret'=>-1 , 'msg'=>null , 'data'=>null , 'ocurr'=>'M_Common_getType' );
		$loop			=	0;
		do{
			$loop++;
			$code		=	Common::getCode( array( 'num'=>15 ) );
			$resData	=	AppUrlContentModel::model()->find( 'ftype=:ftype', array( ':ftype'=>$code ) );
			if( empty( $resData ) ){
				$ret_array['ret']	=	0;
				$ret_array['data']	=	$code;
				break;
			}
			$ret_array['msg']		=	'获取失败，请稍候再试！';
		}while( $loop<3 );	//生成两次type,
		if( 0!=$ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_M_Common_getType.txt', 'txt'=>'outPut:'.var_export( $ret_array, true ) ) );
		}
		return $ret_array;
	}
	/**
	 * 清空对应的api缓存信息
	 */
	public static function clearApiCache( $params )
	{
		$ret_array		=	array( 'ret'=>-1 ,'msg'=>null,'data'=>null );
		do{
			try {
				$key	=	isset( $params['key'] ) ? $params['key'] : '';
				$link	=	isset( $params['link'] ) ? $params['link'] : '';
				if( empty( $key ) || empty( $link ) ){
					$ret_array['msg']	=	'调用参数缺失';
					break;
				}
				$Ctime	=	time();
				$sendArr	=	array( 'Ctime'=>$Ctime ,'key'=>$key , 'link' => $link );
				foreach( $sendArr as $k =>$val ){
					$tempArr[]	=	$val;
				}
				sort($tempArr, SORT_STRING);
				$tempArr	=	implode( $tempArr );
				$tempToken	=	md5( $tempArr . md5( $Ctime . APP_API_COMMON_KEY ) );
				$url 		= 	APIURL . '/Api/ClearCache.html?link='.$link .'&key='.$key .'&Ctime='.$Ctime .'&Token='.$tempToken;//请求地址
				$resData	=	Common::getCurl( array('url'=>$url) );
				if( 0!=$resData['ret'] ){
					$ret_array	=	$resData;
					break;
				}
				$ret_array		=	CfgAR::deJson( $resData['data'] );
			} catch (Exception $e) {
			}
		}while(0);
// 		Common::toTxt( array('file'=>'Log_error.txt', 'txt'=>'input:'. $url .'|'. $tempArr  ) );
		return $ret_array;
	}
	/**
	 * 微信支付接口
	 * 需要对应的证书
	 */
	public static function payCash( $params )
	{
		$ret_array		=	array( 'ret'=>-1 ,'msg'=>null,'data'=>null );
		do{
			try {
				if( !is_array($params) || !isset($params['url']) || !isset( $params['appid'] ) || empty( $params['appid'] ) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				$appid	=	$params['appid'];
				$url 	= 	trim($params['url']);//请求地址
				$vars	=	$params['vars'];
				if( !Common::matchUrl($url) ){
					$ret_array['msg']	= 	'请求地址不正确';
					break;
				}
				$ch		=	curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				//加载证书接口
				$cret1	=	'/data/wwwroot/cret/'.$appid.'/apiclient_cert.pem';
				$cret2	=	'/data/wwwroot/cret/'.$appid.'/apiclient_key.pem';
				$cret3	=	'/data/wwwroot/cret/'.$appid.'/rootca.pem';
				if( !file_exists($cret1) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'证书不存在，请先下载证书';
					break;
				}
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 只信任CA颁布的证书
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
				curl_setopt($ch, CURLOPT_SSLCERT, $cret1 );
				curl_setopt($ch, CURLOPT_SSLKEY, $cret2 );
				curl_setopt($ch, CURLOPT_CAINFO, $cret3 ); // CA根证书（用来验证的网站证书是否是CA颁布）
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data	=	curl_exec($ch);
				curl_close($ch);
				$data	=	str_replace("\r\n","",$data);
				if( 0 == strlen($data) ){
					$ret_array['ret']	=	-2;
					$ret_array['msg']	= 	'请求超时或没用响应';
					break;
				}
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$data;
				Common::toTxt( array( 'file'=>'error.txt' ,'txt'=> var_export( $data , true ) ) );
				
			} catch (Exception $e) {
			}
		}while(0);
		return $ret_array;
	}
	/**
	 * 图片压缩功能的实现
	 * @param string     源图绝对完整地址{带文件名及后缀名}
	 * @param string     目标图绝对完整地址{带文件名及后缀名}
	 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
	 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
	 * @param int        是否裁切{宽,高必须非0}
	 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
	 */
	public static function picCompress( $src_img, $dst_img ,$width=360 ,$height=null ,$cut = 0, $proportion = 0 )
	{
		$ret_array		=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null );
		do{
			try {
				if( !@fopen( $src_img, 'r' ) )
				{
					$ret_array['ret']	=	1;
					break;
				}
				if( empty( $dst_img ) ){
					$ret_array['ret']	=	2;
					break;
				}
				$ot = pathinfo($src_img, PATHINFO_EXTENSION);
				$otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
				$srcinfo = getimagesize($src_img);
				$src_w = $srcinfo[0];
				$src_h = $srcinfo[1];
				$type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
				$createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
	
				$dst_h = $height;
				$dst_w = $width;
				$x = $y = 0;
	
				/**
				 * 缩略图不超过源图尺寸（前提是宽或高只有一个）
				 */
				if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
				{
					$proportion = 1;
				}
				if($width> $src_w)
				{
					$dst_w = $width = $src_w;
				}
				if($height> $src_h)
				{
					$dst_h = $height = $src_h;
				}
	
				if(!$width && !$height && !$proportion)
				{
					$ret_array['ret']	=	3;
					break;
				}
				if(!$proportion)
				{
					if($cut == 0)
					{
						if($dst_w && $dst_h)
						{
							if($dst_w/$src_w> $dst_h/$src_h)
							{
								$dst_w = $src_w * ($dst_h / $src_h);
								$x = 0 - ($dst_w - $width) / 2;
							}
							else
							{
								$dst_h = $src_h * ($dst_w / $src_w);
								$y = 0 - ($dst_h - $height) / 2;
							}
						}
						else if($dst_w xor $dst_h)
						{
							if($dst_w && !$dst_h)  //有宽无高
							{
								$propor = $dst_w / $src_w;
								$height = $dst_h  = $src_h * $propor;
							}
							else if(!$dst_w && $dst_h)  //有高无宽
							{
								$propor = $dst_h / $src_h;
								$width  = $dst_w = $src_w * $propor;
							}
						}
					}
					else
					{
						if(!$dst_h)  //裁剪时无高
						{
							$height = $dst_h = $dst_w;
						}
						if(!$dst_w)  //裁剪时无宽
						{
							$width = $dst_w = $dst_h;
						}
						$propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
						$dst_w = (int)round($src_w * $propor);
						$dst_h = (int)round($src_h * $propor);
						$x = ($width - $dst_w) / 2;
						$y = ($height - $dst_h) / 2;
					}
				}
				else
				{
					$proportion = min($proportion, 1);
					$height = $dst_h = $src_h * $proportion;
					$width  = $dst_w = $src_w * $proportion;
				}
	
				$src = $createfun($src_img);
				$dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
				$white = imagecolorallocate($dst, 255, 255, 255);
				imagefill($dst, 0, 0, $white);
				if(function_exists('imagecopyresampled'))
				{
					imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
				}
				else
				{
					imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
				}
				$otfunc($dst, $dst_img);
				imagedestroy($dst);
				imagedestroy($src);
				$ret_array['ret']	=	0;
				break;
			} catch (Exception $e) {
				$ret_array['ret']	=	13;
				break;
			}
		}while(0);
		return $ret_array;
	}
	/**
	 * 传递xml字串，解析为数组传出
	 */
	public static function getXmlArray( $xmlstring )
	{
		$dom 	=	new DOMDocument();
		$dom->loadXML($xmlstring);
		$node	=	$dom->documentElement;
		$array = false;
		if ($node->hasAttributes()) {
			foreach ($node->attributes as $attr) {
				$array[$attr->nodeName] = $attr->nodeValue;
			}
		}
	
		if ($node->hasChildNodes()) {
			if ($node->childNodes->length == 1) {
				$array[$node->firstChild->nodeName] = getArray($node->firstChild);
			} else {
				foreach ($node->childNodes as $childNode) {
					if ($childNode->nodeType != XML_TEXT_NODE) {
						$array[$childNode->nodeName][] = getArray($childNode);
					}
				}
			}
		} else {
			return $node->nodeValue;
		}
		return false;
	}
	/**
	 * 生成随机码
	 */
	public static function getCode( $params )
	{
		$data			=	'';
		do{
			$num		=	isset( $params['num'] ) ? (int)$params['num'] : 10;	//生成随机码的尾数
			$pattern	=	'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
			for($i=0;$i<$num;$i++)
			{
				$data .= 	$pattern{mt_rand(0,35)};    //生成php随机数
			}
		}while(0);
		return $data;
	}
	/**
	 * 根据传递的key,获取对应的字符ABC
	 */
	public static function getABC( $key )
	{
		$data			=	'';
		do{
			$pattern	=	'ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
			$data		=	substr( $pattern , $key , 1);
		}while(0);
		return $data;
	}
	/**
	* 数字转换为大写
	 * 0-100
	 */
	 public static function exchange($number)
	 {
	 	$ret_array	=	array( 'ret'=>-1 , 'data'=>null );
	 	do{
	 		$result	=	false;
	 		$arr1	=	array( "零","一","二","三","四","五","六","七","八","九","十");
	 		$arr2	=	array( "",'十' , '百'  );
	 		if( empty( $number ) ){
	 			break;
	 		}
	 		if(strlen($number)==1){
	 			$ret_array['ret']	=	0;
	 			$ret_array['data']	=	$arr1[$number];
	 			break;
			}
	 		if( strlen( $number ) == 2 ){
	 			if( 10==$number ){
	 				$ret_array['ret']	=	0;
	 				$ret_array['data']	=	$arr1[$number];
	 				break;
	 			}
	 			$ret_array['data']	.=	$arr1[ substr( $number , 1, 1 ) ] . '十';
	 			if( 0!=substr( $number , -1 ) ){
	 				$ret_array['data']	.=	$arr1[ substr( $number , -1 ) ] ;
	 			}
	 		}
	 		if( strlen( $number ) == 3 ){
	 			$ret_array['ret']	=	0;
	 			$ret_array['data']	=	$arr1[ substr( $number ,0 ,1 ) ]. '百';
	 			if( 0!=substr( $number , -2 )  ){
	 				$ret_array['data']	.=	$arr1[ substr( $number , 1, 1 ) ] . '十';
	 				if( 0!=substr( $number , -1 ) ){
	 					$ret_array['data']	.=	$arr1[ substr( $number , -1 ) ] ;
	 				}
	 			}
	 			break;
	 		}
	 	}while (0);
	 	return $ret_array;
	}
	/**
	 * 生成url
	 */
	public static function createUrl( $params )
	{
		if( !is_array( $params ) ){
			return "?ax=";
		}
		
		$temp	=	0;
		$arr	=	'?';
		
		foreach ( $params as $key=>$val ){
			$temp++;
			if( $temp==1 ){
				$arr	.=	$key.'='.$val;
			}else{
				$arr	.=	'&' .$key .'='.$val;
			}
		}
		return $arr;
	}
	/**
	 * JS弹出对话框并关闭窗口
	 * Enter description here ...
	 * @param $msg （提示信息内容）
	 * @param $url （跳转的URL地址）
	 */
	public static function jsalertCloseurl($msg){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "<script>alert('".$msg."');window.opener=null;window.open('','_self');window.close();</script>";
		exit;
	}
	/**
	 * JS弹出对话框并跳转
	 * Enter description here ...
	 * @param $msg （提示信息内容）
	 * @param $url （跳转的URL地址）
	 */
	public static function jsalerturl($msg,$url=''){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if(empty($url)){
			echo "<script>alert('".$msg."');history.back();</script>";
		}else{
			echo "<script>alert('".$msg."');window.location='".$url."'</script>";
		}
		exit;
	}
	/**
	 * JS弹出对话框并跳转后刷新页面
	 * Enter description here ...
	 * @param $msg （提示信息内容）
	 * @param $url （跳转的URL地址）
	 * @param $backnum (返回页数 默认-1)
	 */
	public static function jsalerturlRefresh($msg,$url='',$backnum=-1){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if(empty($url)){
			echo "<script>alert('".$msg."');history.go('".$backnum."');location.reload();</script>";
		}else{
			echo "<script>alert('".$msg."');window.location='".$url."'</script>";
		}
		exit;
	}
	/**
	 * js弹出框
	 * Enter description here ...
	 * @param $msg （提示内容信息）
	 */
	public static function jsalert($msg){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "<script>alert('".$msg."');</script>";
		exit;
	}
	/**
	 * 匹配手机号码
	 * @author cilun
	 * @param $phone 手机号码
	 */
	public static function matchPhone ($phone = '') {
		if (!empty($phone)) {
			if (preg_match("/^(0|86|086|\+86|12520){0,1}(13[0-9]|14[0-9]|15[0-9]|18[0-9])[0-9]{8}$/" , $phone)) {    
			    return true;
			} else {   
			    return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 匹配参数是否为数字
	 * @author cilun
	 * @param $para 数字参数
	 */
	public static function matchNum($para = '') {
		if (!empty($para)) {
			if (preg_match("/^[0-9]+$/" , $para)) {   
			    return true;
			} else {   
			    return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 匹配是否为url
	 * @author cilun
	 * @param $para url参数
	 */
	public static function matchUrl($para = '') {
		if (!empty($para)) {
			if (preg_match("/^http[s]?:\/\/[A-Za-z0-9-]+\.[A-Za-z0-9-]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/" , $para)) {
			    return true;
			} else {   
			    return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * 匹配Email
	 * @author kk
	 * @param $para email参数
	 * @return boolean $ret
	 */
	public static function matchEmail($para = '') {
		$ret = false;
		if (preg_match('/^[a-z0-9]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i' , $para)) {
			$ret = true;
		}
		return $ret;
	}
	/**
	 * 匹配YYYY-MM-DD HH:II:SS时间
	 * @author kk
	 * @param $para datetime参数
	 * @return boolean $ret
	 */
	public static function matchDateTime($para = '') {
		$ret = false;
		if ( preg_match('/^2[0-9]{3}\-[0-1][0-9]\-[0-3][0-9]\s{1}[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/', $para) ) {
			$ret = true;
		}
		return $ret;
	}
	/**
	 * 软件最新版本信息
	 * @author kk
	 * @param $para 数字参数
	 */
	public static function getSoftVersion() {
		return VER;
	}
	
	/**
	 * 递归创建目录并赋权限
	 * @author kk
	 * @param $dir 目录
	 */
	public static function makeDir($dir) {
		if (! is_dir ( $dir )) {
			if (! self::makeDir ( dirname ( $dir ) )) {
				
				return false;
			}
			if (! mkdir ( $dir, 0777 )) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 调试信息放入txt
	 * @author kk
	 * @param $txt 内容
	 */
	public static function debugTxt($txt) {
		$myfile = 'D:\\bugs.txt';
		$file_pointer = fopen($myfile,"a");
		fwrite($file_pointer,$txt);
		fclose($file_pointer);
	}
	
	/**
	 * logs 日志 信息写入txt文件
	 * @author cilun
	 * @param $arr 参数为数组
	 * @param file 日志文件
	 * @param txt 内容
	 * @param isarr 是否打印数组
	 */
	public static function toTxt($arr = '') {
		$file	=	isset($arr['file']) ? trim($arr['file']) : LOGS_ROOT .'Log_Default.txt';
		if( !preg_match('/\/logs\//', $file) ){//日志文件不是全路径
			$file	=	 LOGS_ROOT . $file;
		}
		$isarr = isset($arr['isarr']) ? true : false;
		if (is_file($file)) {
			if (is_writable($file)) {
				$bname = basename($file);
				$file = substr($file, 0, strlen($file) - strlen($bname));
				$time = date("Y-m-d H:i:s");
				$pre = "DATE:" . $time . "|";
				if ($isarr) {
					$txt = var_export($arr['txt'],true);
					$txt = str_replace("\n", "", $txt);
					$txt = "\r\n" . $pre . $txt;
				} else {
					$txt = !empty($arr['txt']) ? $arr['txt'] : '';
					$txt = str_replace("\n", "", $txt);
					$txt = "\n" . $pre . $txt;
				}
				$fp = fopen($file . $bname,"a+");
				fwrite($fp,$txt);
				fclose($fp);
			}
		} else {
			$bname = basename($file);
			$file = substr($file, 0, strlen($file) - strlen($bname));
			self::makeDir($file);
			$time = date("Y-m-d H:i:s");
			$pre = "DATE:" . $time . "|";
			if ($isarr) {
				$txt = var_export($arr['txt'],true);
				$txt = str_replace("\n", "", $txt);
				$txt = "\r\n" . $pre . $txt;
			} else {
				$txt = !empty($arr['txt']) ? $arr['txt'] : '';
				$txt = str_replace("\n", "", $txt);
				$txt = "\n" . $pre . $txt;
			}
			$fp = fopen($file . $bname,"a+");
			fwrite($fp,$txt);
			fclose($fp);
		}
	}
	
	/**
	 * 下载文件
	 * @author kk
	 * @param $dir 目录文件
	 */
	public static function downLoad($params = '') {
		$file = $params['file'];
		
		$fsize = filesize($file);
	    // 截取文件扩展名
		$file_name = basename($params ['file']);
		$file_dir = isset ( $params ['file'] ) ? str_replace($file_name, '', $file) : '';
		$file = @fopen ( $file, "rb" );
		
	    $ext = explode(".", $params['file']);
	    $ext = strtolower(end($ext));
		switch($ext) {
	        case 'jar': $mime = "application/java-archive"; break;
	        case 'zip': $mime = "application/zip"; break;
	        case 'jpeg': $mime = "image/jpeg"; break;
	        case 'jpg': $mime = "image/jpg"; break;
	        case 'jad': $mime = "text/vnd.sun.j2me.app-descriptor"; break;
	        case "gif": $mime = "image/gif"; break;
	        case "png": $mime = "image/png"; break;
	        case "pdf": $mime = "application/pdf"; break;
	        case "txt": $mime = "text/plain"; break;
	        case "doc": $mime = "application/msword"; break;
	        case "ppt": $mime = "application/vnd.ms-powerpoint"; break;
	        case "wbmp": $mime = "image/vnd.wap.wbmp"; break;
	        case "wmlc": $mime = "application/vnd.wap.wmlc"; break;
	        case "mp4s": $mime = "application/mp4"; break;
	        case "ogg": $mime = "application/ogg"; break;
	        case "pls": $mime = "application/pls+xml"; break;
	        case "asf": $mime = "application/vnd.ms-asf"; break;
	        case "swf": $mime = "application/x-shockwave-flash"; break;
	        case "mp4": $mime = "video/mp4"; break;
	        case "m4a": $mime = "audio/mp4"; break;
	        case "m4p": $mime = "audio/mp4"; break;
	        case "mp4a": $mime = "audio/mp4"; break;
	        case "mp3": $mime = "audio/mpeg"; break;
	        case "m3a": $mime = "audio/mpeg"; break;
	        case "m2a": $mime = "audio/mpeg"; break;
	        case "mp2a": $mime = "audio/mpeg"; break;
	        case "mp2": $mime = "audio/mpeg"; break;
	        case "mpga": $mime = "audio/mpeg"; break;
	        case "wav": $mime = "audio/wav"; break;
	        case "m3u": $mime = "audio/x-mpegurl"; break;
	        case "bmp": $mime = "image/bmp"; break;
	        case "ico": $mime = "image/x-icon"; break;
	        case "3gp": $mime = "video/3gpp"; break;
	        case "3g2": $mime = "video/3gpp2"; break;
	        case "mp4v": $mime = "video/mp4"; break;
	        case "mpg4": $mime = "video/mp4"; break;
	        case "m2v": $mime = "video/mpeg"; break;
	        case "m1v": $mime = "video/mpeg"; break;
	        case "mpe": $mime = "video/mpeg"; break;
	        case "mpeg": $mime = "video/mpeg"; break;
	        case "mpg": $mime = "video/mpeg"; break;
	        case "mov": $mime = "video/quicktime"; break;
	        case "qt": $mime = "video/quicktime"; break;
	        case "avi": $mime = "video/x-msvideo"; break;
	        case "midi": $mime = "audio/midi"; break;
	        case "mid": $mime = "audio/mid"; break;
	        case "amr": $mime = "audio/amr"; break;
	        case "apk": $mime = "application/vnd.android.package-archive"; break;
	        case "sisx": $mime = "x-epoc/x-sisx-app"; break;
	        case "sis": $mime = "application/vnd.symbian.install"; break;
	        case "ipa": $mime = "application/iphone"; break;
	        default: $mime = "application/force-download";
	    }
	    if (ob_get_length() !== false) @ob_end_clean(); //清除以前的缓冲
		header("Pragma: public");
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header('Content-Description: File Transfer');
    	header('Content-Type: ' . $mime);
   		header('Content-Disposition: attachment; filename=' . $file_name);
		header("Content-Transfer-Encoding: binary" );
		header("Content-Length: $fsize");
		header("Expires: 0" );
		header('Content-Disposition: attachment; filename="' . $file_name .'"');
		while(!feof($file)) {
			echo fread($file,8192);
		}
		fclose ($file);
		@flush();
		@ob_flush();
	    
	}
	
	/**
	 * 取出空格 \r\n
	 * @param unknown_type $str
	 */
	function performstr($str) {
        $str=chop($str); //去掉连续空白
        $str=str_replace("<P></P>","",$str);//去掉<p></P>
        $str=str_replace("\r","",$str);//去掉\r
        $str=str_replace("\n","<br>",$str);//去掉\n
        $str=addslashes($str);//加入斜线
        return($str);
	}
	/***
	 * 过滤
	 */
  	public static function filter($str) {
	    $document = $str;
	    $search = array(
	        /*"'<script[^>]*?>.*?</script>'si",*/ // 去掉 javascript
	    	"/",
	        /*"'<style[^>]*?>.*?</style>'si", // 去掉 css
	        "'<[/!]*?[^<>]*?>'si", // 去掉 HTML 标记
	        "'<!--[/!]*?[^<>]*?>'si", // 去掉 注释标记
	        "'([rn])[s]+'", // 去掉空白字符
	        "'&(quot|#34);'i", // 替换 HTML 实体
	        "'&(amp|#38);'i",
	        "'&(lt|#60);'i",
	        "'&(gt|#62);'i",
	        "'&(nbsp|#160);'i",
	        "'&(iexcl|#161);'i",
	        "'&(cent|#162);'i",
	        "'&(pound|#163);'i",
	        "'&(copy|#169);'i",
	        "'&#(d+);'e"*/
	        ); // 作为 PHP 代码运行
	    $replace = array(//"",
	        "//",
	        /*"",
	        "",
	        "\1",
	        "\"",
	        "&",
	        "<",
	        ">",
	        " ",
	        chr(161),
	        chr(162),
	        chr(163),
	        chr(169),
	        "chr(\1)"*/);
	//$document为需要处理字符串，如果来源为文件可以
	//$document = file_get_contents('http://www.phpzixue.cn/');
	    $out = preg_replace($search, $replace, $document);
	    return $out;
	}
	/**
	 * 获取IP地址
	 */
	public static function getIp() {
		if( getenv('HTTP_CLIENT_IP') && 'unknown' != getenv('HTTP_CLIENT_IP') ) {
			$IP	=	getenv('HTTP_CLIENT_IP');
		}elseif( getenv('HTTP_X_FORWARDED_FOR') && 'unknown' != getenv('HTTP_X_FORWARDED_FOR') ) {
			$IP =	getenv('HTTP_X_FORWARDED_FOR');
		}elseif( getenv('HTTP_X_FORWARDED') && 'unknown' != getenv('HTTP_X_FORWARDED') ) {
			$IP =	getenv('HTTP_X_FORWARDED');
		}elseif( getenv('HTTP_FORWARDED_FOR') && 'unknown' != getenv('HTTP_FORWARDED_FOR') ) {
			$IP =	getenv('HTTP_FORWARDED_FOR');
		}elseif( getenv('HTTP_FORWARDED') && 'unknown' !=getenv('HTTP_FORWARDED') ) {
			$IP =	getenv('HTTP_FORWARDED');
		}else{
			$IP =	$_SERVER['REMOTE_ADDR'];
		}
		return $IP;
	}

	/**
	 * 整型过滤函数
	 * @param  $number   整型
	 */
	public static function getInt($number) {
		return intval(trim($number));
	}
	
	/**
	 * 字符串型过滤函数
	 * @param  $string   字符型
	 */
	public static function getStr($string) {
		if (!get_magic_quotes_gpc()) {
			return addslashes(trim($string));
		}
		return $string;
	}
	/**
	 * @author kk 
	 * 电话号码的过滤获取
	 * @param string $phone
	 * @return string
	 */
	public static function getPhone ($phone = '') {
		$phone = str_replace("-", '', trim($phone));
		$phone = strrev($phone);
		return strrev(substr($phone,0,11));
	}
	
	static function authcode($string, $operation = 'DECODE', $key = 'kk2014', $expiry = 0) {
	    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
	    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	    // 当此值为 0 时，则不产生随机密钥
	    $ckey_length = 4;
	
	    // 密匙
	    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
	
	    // 密匙a会参与加解密
	    $keya = md5(substr($key, 0, 16));
	    // 密匙b会用来做数据完整性验证
	    $keyb = md5(substr($key, 16, 16));
	    // 密匙c用于变化生成的密文
	    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	    // 参与运算的密匙
	    $cryptkey = $keya.md5($keya.$keyc);
	    $key_length = strlen($cryptkey);
	    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
	    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
	    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	    $string_length = strlen($string);
	    $result = '';
	    $box = range(0, 255);
	    $rndkey = array();
	    // 产生密匙簿
	    for($i = 0; $i <= 255; $i++) {
	        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
	    }
	    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
	    for($j = $i = 0; $i < 256; $i++) {
	        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
	        $tmp = $box[$i];
	        $box[$i] = $box[$j];
	        $box[$j] = $tmp;
	    }
	    // 核心加解密部分
	    for($a = $j = $i = 0; $i < $string_length; $i++) {
	        $a = ($a + 1) % 256;
	        $j = ($j + $box[$a]) % 256;
	        $tmp = $box[$a];
	        $box[$a] = $box[$j];
	        $box[$j] = $tmp;
	        // 从密匙簿得出密匙进行异或，再转成字符
	        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	    }
	    if($operation == 'DECODE') {
	        // substr($result, 0, 10) == 0 验证数据有效性
	        // substr($result, 0, 10) - time() > 0 验证数据有效性
	        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
	        // 验证数据有效性，请看未加密明文的格式
	        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
	            return substr($result, 26);
	        } else {
	            return '';
	        }
	    } else {
	        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
	        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
	        return $keyc.str_replace('=', '', base64_encode($result));
	    }
	}
	static public function get($name){
		$query = $_SERVER["QUERY_STRING"];
		
		if(empty($name)){
			return "";
		}
		
		$query = explode('&',$query);
		
		$rs = "";
		foreach($query as $k=>$v){
			
			if(!empty($v)){
				$val = explode('=',$v);
				
				if($val[0] == $name){
					
					$rs = $val[1];
				}
			}
		}
		
		return $rs;
	}
	
	public static function is_Date($str,$format="Y-m-d"){
		if(!preg_match('/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}/',$str)) {
			return 0;
		}
		$unixTime_1 = strtotime($str);
		if ( !is_numeric($unixTime_1) || $unixTime_1 <= 0) return 0; //如果非日期，则返回
		$checkDate = date($format, $unixTime_1);
		$unixTime_2 = strtotime($checkDate);;
		if($unixTime_1 == $unixTime_2){
			return 1;
		}else{
			return 0;
		}
	}
	
	static public function getVariable($name){
		return empty($name) ? '': $name;
	}
	
	/*
	* 加密，可逆
	* 可接受任何字符
	* 安全度非常高
	*/
	public static function encrypt($txt, $key = CRYPTKEY) {
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@.";
		$ikey = "-x6g6ZWm2G9g_.vr0BopOq3kRIxsZ6rm";
		$nh1 = rand ( 0, 64 );
		$nh2 = rand ( 0, 64 );
		$nh3 = rand ( 0, 64 );
		$ch1 = $chars {$nh1};
		$ch2 = $chars {$nh2};
		$ch3 = $chars {$nh3};
		$nhnum = $nh1 + $nh2 + $nh3;
		$knum = 0;
		$i = 0;
		while ( isset ( $key {$i} ) )
			$knum += ord ( $key {$i ++} );
		$mdKey = substr ( md5 ( md5 ( md5 ( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$txt = base64_encode ( $txt );
		$txt = str_replace ( array ('+', '/', '=' ), array ('-', '_', '.' ), $txt );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen ( $txt );
		$klen = strlen ( $mdKey );
		for($i = 0; $i < $tlen; $i ++) {
			$k = $k == $klen ? 0 : $k;
			$j = ($nhnum + strpos ( $chars, $txt {$i} ) + ord ( $mdKey {$k ++} )) % 64;
			$tmp .= $chars {$j};
		}
		$tmplen = strlen ( $tmp );
		$tmp = substr_replace ( $tmp, $ch3, $nh2 % ++ $tmplen, 0 );
		$tmp = substr_replace ( $tmp, $ch2, $nh1 % ++ $tmplen, 0 );
		$tmp = substr_replace ( $tmp, $ch1, $knum % ++ $tmplen, 0 );
		return $tmp;
	}
	
	/*
	 * 解密
	*
	*/
	public static function decrypt($txt, $key = CRYPTKEY) {
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@.";
		$ikey = "-x6g6ZWm2G9g_.vr0BopOq3kRIxsZ6rm";
		$knum = 0;
		$i = 0;
		$tlen = strlen ( $txt );
		while ( isset ( $key {$i} ) )
			$knum += ord ( $key {$i ++} );
		$ch1 = $txt {$knum % $tlen};
		$nh1 = strpos ( $chars, $ch1 );
		$txt = substr_replace ( $txt, '', $knum % $tlen --, 1 );
		$ch2 = $txt {$nh1 % $tlen};
		$nh2 = strpos ( $chars, $ch2 );
		$txt = substr_replace ( $txt, '', $nh1 % $tlen --, 1 );
		$ch3 = $txt {$nh2 % $tlen};
		$nh3 = strpos ( $chars, $ch3 );
		$txt = substr_replace ( $txt, '', $nh2 % $tlen --, 1 );
		$nhnum = $nh1 + $nh2 + $nh3;
		$mdKey = substr ( md5 ( md5 ( md5 ( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen ( $txt );
		$klen = strlen ( $mdKey );
		for($i = 0; $i < $tlen; $i ++) {
			$k = $k == $klen ? 0 : $k;
			$j = strpos ( $chars, $txt {$i} ) - $nhnum - ord ( $mdKey {$k ++} );
			while ( $j < 0 )
				$j += 64;
			$tmp .= $chars {$j};
		}
		$tmp = str_replace ( array ('-', '_', '.' ), array ('+', '/', '=' ), $tmp );
		return trim ( base64_decode ( $tmp ) );
	}
	/**
	 * @param 1 Android，2 iphone，3 kjava，4 symbian，5 wap，6 web
	 * post
	 * @param $url
	 * @param $vars
	 */
	public static function curl_post($params = '') {
		$post_arr['url']	=	$params['url'];
		$post_arr['vars']	=	$params['vars'];
		$result				=	self::postCurl($post_arr);
		if( 0 != $result['ret'] ){
			return false;
		}
		return $result['data'];
	}
	/**
	 * 请求指定url地址，POST方式
	 */
	public static function postCurl($params) {
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_postCurl', 'error'=>'', 'data'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['url']) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				$url 	= 	trim($params['url']);//请求地址
				$vars	=	$params['vars'];
				if( !Common::matchUrl($url) ){
					$ret_array['msg']	= 	'请求地址不正确';
					break;
				}
				$ch		=	curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
				$data	=	curl_exec($ch);
				curl_close($ch);
				$data	=	str_replace("\r\n","",$data);
				if( 0 == strlen($data) ){
					$ret_array['ret']	=	-2;
					$ret_array['msg']	= 	'请求超时或没用响应';
					break;
				}
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$data;
			}catch(Exception $e){
				$ret_array['ret']		=	-2;
				$ret_array['msg']		= 	'程序执行异常';
				$ret_array['error']		= 	$e->getMessage();
				break;
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_postCurl.txt', 'txt'=>'Input:'.var_export($params, true).'|操作结果:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	/**
	 * 微信post接口
	 */
	public static function postCurlForm($params)
	{
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_postCurlForm', 'error'=>'', 'data'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['url']) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				$url 	= 	trim($params['url']);//请求地址
				$vars	=	$params['vars'];
				if( !Common::matchUrl($url) ){
					$ret_array['msg']	= 	'请求地址不正确';
					break;
				}
				$ch		=	curl_init();
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
				curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
				curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
				$data	=	curl_exec($ch);
				curl_close($ch);
				$data	=	str_replace("\r\n","",$data);
				if( 0 == strlen($data) ){
					$ret_array['ret']	=	-2;
					$ret_array['msg']	= 	'请求超时或没用响应';
					break;
				}
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$data;
			}catch(Exception $e){
				$ret_array['ret']		=	-2;
				$ret_array['msg']		= 	'程序执行异常';
				$ret_array['error']		= 	$e->getMessage();
				break;
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_postCurlForm.txt', 'txt'=>'Input:'.var_export($params, true).'|操作结果:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	/**
	 * 下载图片
	 * @author kk
	 * @since 2015-07-14
	 * $params 下载微信二维码文件
	 */
	public static function downLoadImage( $params='' )
	{
		$ret_array		=	array( 'ret'=>-1 , 'msg'=>null , 'data'=>null ,'ocurr'=>'Common' );
		do{
			try {
				$url	=	isset( $params['url'] ) ? $params['url'] : '';
				if( empty( $url ) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'请求失败，请传递正确的链接';
					break;
				}
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				curl_setopt($curl, CURLOPT_NOBODY, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$data 		= 	curl_exec($curl);
				$httpinfo	=	curl_getinfo($curl);
				curl_close($curl);
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$data;//array_merge(array( 'body'=>$data ) , array( 'header'=>$httpinfo ) );
			} catch (Exception $e) {
				$ret_array['ret']		=	13;
				$ret_array['msg']		=	'服务器忙，请稍后再试！';
				$ret_array['data']		=	$e->getMessage();
				break;
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_downLoadImage.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array,true)));
		}
		return $ret_array;
	}
	/**
	 * 生成对应的图片文件
	 */
	public static function createImgesForCode($arr)
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null );
		do{
			$file	=	isset($arr['file']) ? trim($arr['file']) : '';
			$txt 	=	isset($arr['txt']) ? $arr['txt'] : '';
			if( empty( $file ) || empty( $txt ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'必要参数缺失';
				break;
			}
			if (is_file($file)) {
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'生成二维码失败，已经存在对应的二维码。';
				break;
			}
			//循环创建对应的文件夹
			if( !is_dir( dirname($file ) ) ){
				self::makeDir( dirname($file) );
			}
			$fp = fopen($file,"a+");
			fwrite($fp,$txt);
			fclose($fp);
			$ret_array['ret']	=	0;
		}while(0);
		return $ret_array;
	}
	/**
	 * curl get 数据到指定url中
	 * @param  $params   参数为数组
	 * @param  $url   请求的url
	 * @param  $post  请求的get内容
	 */
	public static function curl_get($params = '') {
		$params['url']	=	$params['url'];
		$result	=	self::getCurl($params);
		if( 0 != $result['ret'] ){
			return false;
		}
		return $result['data'];
	}
	
	/**
	 * 请求指定url地址，GET方式
	 */
	public static function getCurl($params) {
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_getCurl', 'error'=>'', 'data'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['url']) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				$url 	= 	trim($params['url']);//请求地址
				if( !Common::matchUrl($url) ){
					$ret_array['msg']	= 	'请求地址不正确';
					break;
				}
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
				$data = curl_exec($curl);
				curl_close($curl);
				$data = str_replace("\r\n","",$data);
				if( 0 == strlen($data) ){
					$ret_array['ret']	=	-2;
					$ret_array['msg']	= 	$data . '请求超时或没用响应';
					break;
				}
				
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$data;
			}catch(Exception $e){
				$ret_array['ret']		=	-2;
				$ret_array['msg']		= 	'程序执行异常';
				$ret_array['error']		= 	$e->getMessage();
				break;
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_getCurl.txt', 'txt'=>$url.'|操作结果:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	
	public static function getBrowser(){
		$data			=	array('agent' => '', 'version' => '');
		$agent			=	$_SERVER["HTTP_USER_AGENT"];
		$browseragent	=	""; //浏览器
		$browserversion	=	""; //浏览器的版本
		if (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent, $version)) {
			$browserversion	=	$version[1];
			$browseragent	=	"Internet Explorer";
		}
		elseif(preg_match( '/Opera\\/([0-9]{1,2}.[0-9]{1,2})/',$agent,$version)) {
			$browserversion	=	$version[1];
			$browseragent	=	"Opera";
		}
		elseif(preg_match( '/Firefox\\/([0-9.]{1,5})/',$agent,$version)) {
			$browserversion	=	$version[1];
			$browseragent	=	"Firefox";
		}
		elseif(preg_match( "/Chrome\\/([0-9.]{1,4})/", $agent, $version)) {
			$browserversion	=	$version[1];
			$browseragent	=	"Chrome";
		}
		elseif(preg_match( '/Safari\\/([0-9.]{1,3})/',$agent,$version)) {
			$browseragent	=	"Safari";
			$browserversion	=	$version[1];
		}
		else {
			$browserversion	=	"";
			$browseragent	=	"Unknown";
		}
		$data['agent']		=	$browseragent;
		$data['version']	=	$browserversion;
		return $data;
	}
	 
	 /**
	  * 设置网站的COOKIE
	  */
	 public static function setckie($params=array()){
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_setckie', 'error'=>'', 'data'=>"");
		do{
			try {
				if( !is_array($params) || 0 == count($params) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'服务器忙，请稍后再试';
					break;
				}

				foreach($params as $item ){
						setcookie($item['name'], $item['value'], $item['time'], '/', BASE_ADDRESS_SHORT );
				}
				$ret_array['ret']	=	0;
			}catch (Exception $e) {
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试';
				$ret_array['error']	=	$e->getMessage();
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_setckie.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true) ) );
		}
					
	 }
     /**
	  * 销毁网站的COOKIE
	  */
	 public static function delckie($params=array()){
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_delckie', 'error'=>'', 'data'=>"");
		do{
			try {
				if( !is_array($params) || 0 == count($params) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'服务器忙，请稍后再试';
					break;
				}

				foreach($params as $item ){
					if( isset( $_COOKIE[$item] ) ){
						setcookie($item, 'del', time()-86400, '/', BASE_ADDRESS_SHORT);
					}
				}
				$ret_array['ret']	=	0;
			}catch (Exception $e) {
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试';
				$ret_array['error']	=	$e->getMessage();
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_delckie.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true) ) );
		}
	 }

	/**
	 * 随机生成指定长度的字符串
	 */
	public static function getRandChar($params){
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'Common_getRandChar', 'error'=>'', 'data'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['count']) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				if( !is_int($params['count']) ){
					$ret_array['msg']	= 	'参数类型错误';
					break;
				}
				$count		=	$params['count'];
				$character 	= 	array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');	
				$temp		=	array();
				do{
					$rand	=	rand(0, $count-1);
					$vale	=	$character[$rand];
					if( in_array($vale, $temp) ){
						continue;
					}
					$temp[]	=	$vale;
				}while( count($temp) < $count );
				$ret_array['ret']	=	0;
				$ret_array['data']	=	join('',$temp);
			}catch(Exception $e){
				$ret_array['ret']	= 	13;
				$ret_array['error']	= 	'服务器忙，请稍后再试';
				break;
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_getRandChar.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array,true)));
		}
		return $ret_array;
	}

	/**
	 *	获取URL提交过来的参数值，忽略大小写，没有的话，返回默认值
	 */
	public static function getParams($reqname=false, $default=false, $method='GET'){
		$value	=	$default;
		do{
			if( empty($reqname) ){
				break;
			}
			
			if( 'GET' == strtoupper($method) ){
				$fetch	=	$_GET;
			}else if('POST' == strtoupper($method) ){
				$fetch	=	$_POST;
			}else{
				$fetch	=	$_REQUEST;
			}

			if( isset($fetch[$reqname]) ){
				$value	=	rawurldecode($fetch[$reqname]);
				break;
			}

			$another	=	ucfirst($reqname);
			if( $reqname == $another ){//首字母大小写转换，看是否有该参数
				$another	=	lcfirst($reqname);
			}

			if( isset($fetch[$another]) ){
				$value	=	rawurldecode($fetch[$another]);
				break;
			}
			//没有此参数
		}while(0);
		return $value;
	}

	/**
	 * 对数组元素做rawurlencode操作
	 */
	public static function enCodeParams($params=array()){
		$temp	=	array();
		do{
			if( is_string($params) ){
				$temp	=	rawurlencode($params);
				break;
			}

			if( !is_array($params) ){
				break;
			}

			foreach($params as $key => $value ){
				$temp[$key]	=	rawurlencode($value);	
			}
		}while(0);
		return $temp;
	}

	/**
	 * 构造URL上的参数信息，做rawurlencode处理
	 */
	public static function urlParams($params=array()){
		$value	=	'';
		do{
			if( !is_array($params) ){
				break;
			}
			
			$temp	=	array();
			foreach($params as $key => $value ){
				$temp[]	=	$key .'='.rawurlencode($value);	
			}
			$value	=	join('&', $temp);
		}while(0);
		return $value;
	}

	/**
	 * 利用ksort生成Sign信息,支持对URL的签名,不再用md5
	 */
	public static function signParams($signkey=false, $params=array(), $signurl=false){
		$value	=	false;
		do{
			if( is_bool($signkey) || ( !is_array($params) && is_bool($signurl) ) ){
				break;
			}
			
			ksort( $params );//对数组按照key进行排序,保留原来的key值
			$temp	=	array();
			foreach( $params as $key => $value ){
				array_push($temp, $key .'='.$value );	
			}

			$reqParams	=	join('&', $temp);
			$reqParams	=	str_replace('~', '%7E', rawurlencode($reqParams) );//详见http://hi.baidu.com/k45hifz/item/640acf009bfc9d1acd34ea5a
			if( !is_bool($signurl) ){
				$reqParams	=  $signurl.'&'.$reqParams;//不在关注url前面是否有?符号
			}
			
			$hashresult	=	hash_hmac("sha1", $reqParams, $signkey, true);
			$signresult	=	base64_encode($hashresult);
			
			$value	=	strtoupper($signresult);
		}while(0);
		return $value;
	}
	
	/**
	 * 析取URL上的参数信息
	 */
	public static function getUrlParams($Url=false){
		$params	=	array();
		do{
			if( empty($Url) || !is_string($Url) || !preg_match('/\?/', $Url) ){
				break;
			}
			$temp	=	explode('?', $Url);
			$temp	=	explode('&', $temp[1]);//只要问号后面的进行分割就行了
			foreach( $temp as $item ){
				$tmp	=	explode('=', $item );
				if( 2 == count($tmp) ){//xx=xx
					$params[$tmp[0]] = $tmp[1];
				}
			}
		}while(0);
		return $params;
	}
	
	/**
	 * 获取URL的完整地址（没有参数的部分）;
	 */
	public static function getAbsoluteUrl($Url=false){
		$value	=	'';
		do{
			if( empty($Url) || !is_string($Url) ){
				break;
			}

			$temp	=	explode('?', $Url);
			$value	=	$temp[0];
		}while(0);
		return $value;
	}
	/**
	 * 读取指定目录下的文件
	 */
	public static function getAllFile($params=array()){
		$ret_array	=	array('ret'=>1, 'msg'=>'', 'occur'=>'Common_getAllFile', 'error'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['filepath']) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'调用参数错误';
					break;
				}
				
				$allfiles	=	array();
				$filepath	=	trim($params['filepath']);
				if( is_dir ( $filepath ) ){//判断是不是文件夹
					$dirhandle	=	dir( $filepath );//目录句柄
					while( $file =	$dirhandle->read() ){
						if( "." != $file && ".." != $file ){
							$absdir	=	$filepath.$file;
							if( is_dir($absdir) ){
								$result	=	Common::getAllFile(array('filepath'=>$absdir));
								if( 0 != $result['ret'] ){
									continue;
								}
								$allfile[$absdir]	=	$result['data'];//数组
							}else{
								array_push($allfiles, $absdir);
							}
						}
					}
					$dirhandle->close();
				}

				$ret_array['ret']	=	0;
				$ret_array['data']	=	$allfiles;				
			}catch(Exception $e){
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试';
				$ret_array['error']	=	$e->getMessage();	
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_Common_getAllFile.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	
	/**
	 * 通过时间戳返回和现在对比的时间
	 */
	public static function tranTime ( $time = '' ) {
	    $str   =   '';
	    do {
	        if ( empty($time) ) {
	            return $str;
	        }
	        //获取今天凌晨的时间戳
	        $day = strtotime(date('Y-m-d',time()));
	        //获取昨天凌晨的时间戳
	        $pday = strtotime(date('Y-m-d',strtotime('-1 day')));
	        //获取现在的时间戳
	        $nowtime = time();
	        $tc      = $nowtime-$time;
            if($time<$pday){    //昨天前原样返回
              $str = date('Y-m-d H:i:s',$time);
            }elseif($time<$day && $time>$pday){
              $str = "昨天  ".date('H:i:s',$time);
            }else{
              $str = "今天  ".date('H:i:s',$time);
            }
	    } while ( 0 );
	    return $str;
	}
	
	/**
	 * qq表情解析
	 */
	public static function qqFace ( $text  ) {
	    $qqface_maps   =array(
	        "/::)", "/::~", "/::B", "/::|", "/:8-)", "/::<", "/::$", "/::X", "/::Z", "/::'(", "/::-|", "/::@", "/::P", "/::D", "/::O", "/::(", "/::+", "/:--b", "/::Q", "/::T", "/:,@P", "/:,@-D", "/::d", "/:,@o", "/::g", "/:|-)", "/::!", "/::L", "/::>", "/::,@", "/:,@f", "/::-S", "/:?", "/:,@x", "/:,@@", "/::8", "/:,@!", "/:!!!", "/:xx", "/:bye", "/:wipe", "/:dig", "/:handclap", "/:&-(", "/:B-)", "/:<@", "/:@>", "/::-O", "/:>-|", "/:P-(", "/::'|", "/:X-)", "/::*", "/:@x", "/:8*", "/:pd", "/:<W>", "/:beer", "/:basketb", "/:oo", "/:coffee", "/:eat", "/:pig", "/:rose", "/:fade", "/:showlove", "/:heart", "/:break", "/:cake", "/:li", "/:bome", "/:kn", "/:footb", "/:ladybug", "/:shit", "/:moon", "/:sun", "/:gift", "/:hug", "/:strong", "/:weak", "/:share", "/:v", "/:@)", "/:jj", "/:@@", "/:bad", "/:lvu", "/:no", "/:ok", "/:love", "/:<L>", "/:jump", "/:shake", "/:<O>", "/:circle", "/:kotow", "/:turn", "/:skip", "/:oY");
	    return str_replace( $qqface_maps,
    		array_map(function ( $v ) {
    		    return '<img src="https://res.wx.qq.com/mpres/htmledition/images/icon/emotion/'.$v.'.gif" width="24" >';
    		}, array_keys($qqface_maps) ),
    		htmlspecialchars_decode($text, ENT_QUOTES)
    	);
	}
	
	/**
	 * 用户机型解析
	 */
	public static function getSystem ( $system ) {
	    $spotSystem   =   array('ARM');   //其他机型
	    return empty($system) ? '未获取到机型' : (stristr($system,'Linux') ? '安卓' : ( stristr($system,'Win') ? '电脑' : ( in_array($system,$spotSystem) ? '未识别机型' : $system  ) ));
	}
	
	/**
	 * 获取一个随机颜色
	 */
	public static function getRandColor () {
	    $str='0123456789ABCDEF';
	    $estr='#';
	    $len=strlen($str);
	    for($i=1;$i<=6;$i++)
	    {
	    $num=rand(0,$len-1);
	    $estr=$estr.$str[$num];
	    }
	    return $estr;
	}
	
	/**
	 * 获取ip详情信息
	 */
	public static function getIpInfo ( $ip = '' ) {
	    if ( empty($ip) ) {
	        return '';
	    }
	    $url   =   "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
	    $res   =   CfgAR::deJson( Common::curl_get(array('url' => $url )) );
	    if ( $res['code'] == 0  ) {
	        return $res['data'];
	    }
	    return '';
	}
	
}