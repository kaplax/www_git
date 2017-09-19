<?php

/**
 * 链接服务器Memcache，Mysql，Mongo接口模块
 * @author kk
 * @since 2014.3.30
 */
class CfgAR extends CActiveRecord {
	
	/**
	 * 设置指定键的缓存信息，如果无法连接memcache则返回端口错误
	 * @author kk
	 * @param data string -- 内容
	 * @param link string -- 连接值
	 * @param key string -- 连接memcache键
	 * @param time int -- 过期时间，默认 0 不过期
	 */
	public static function setMc($params) {
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'CfgAR_setMc', 'error'=>'', 'data'=>false);
		do{
			try{
				if( !is_array($params) || !isset($params['link']) || !isset($params['key']) || !isset($params['data']) || empty($params['link']) || empty($params['key']) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'服务器忙，请稍后再试!';
					break;
				}
				$link	=	$params['link'];
				$data	=	$params['data'];
				$key	=	$params['key'];
				$time	=	isset($params['time']) ? intval($params['time']) : 0;
				$more	=	isset($params['more']) ? $params['more'] : false;//多台服务器
				$result	=	Yii::app()->$link->set($key, $data, $time);
				$ret_array['ret']	=	0;
				$ret_array['data']	=	$result;
			}catch (Exception $e) {
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试!';
				$ret_array['error']	=	$e->getMessage();
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_CfgAR_setMc.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true)));
		}
		return $ret_array['data'];//返回data字段是因为被以前的代码给害死了
	}
	
	/**
	 * 添加指定键的缓存信息，如果无法连接memcache则返回端口错误
	 * @author kk
	 * @param data string -- 内容
	 * @param link string -- 连接值
	 * @param key string -- 连接memcache键
	 * @param time int -- 过期时间，默认 0 不过期
	 */
	public static function addMc($params) {
		$ret_array	=	array('ret'=>1, 'msg'=>'', 'occur'=>'CfgAR_addMc', 'error'=>'', 'data'=>false);
		do{
			try{
				if( !is_array($params) || !isset($params['link']) || !isset($params['data']) || !isset($params['key']) || empty($params['link']) || empty($params['key']) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'调用参数错误';//前端提示此消息，一定是开发责任
					break;
				}

				$link	=	$params['link'];
				$data	=	$params['data'];
				$key	=	$params['key'];
				$time	=	isset($params['time']) ? intval($params['time']) : 0;
				$more	=	isset($params['more']) ? $params['more'] : false;//多台服务器

				$result	=	Yii::app()->$link->add($key, $data, $time);
				$ret_array['ret']	=	0;
				$ret_array['data']	=	$result;
			}catch (Exception $e) {
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试';
				$ret_array['error']	=	$e->getMessage();
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_CfgAR_addMc.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true)));
		}
		return $ret_array['data'];//返回data字段是因为被以前的代码给害死了
	}
	
	/**
	 * 获取指定键的缓存信息，如果无法连接memcache则返回端口错误
	 * @author kk
	 * @param name string -- 连接memcache值
	 * @param key string -- 连接memcache键
	 */
	public static function getMc($params) {
		$link	=	isset($params['link']) ? $params['link'] : '';
		$key	=	isset($params['key']) ? $params['key'] : '';
		if (!empty($link) && !empty($key)) {
			try {
				return Yii::app()->$link->get($key);
			} catch (Exception $e) {
				return false;//-11211
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 获取缓存内容
	 * @param unknown_type $params
	 * @return boolean
	 */
	public static function getMem($params) {
		$ret_array = array('ret'=>-1, 'msg'=>'', 'occur'=>'CfgAR_getMem', 'error'=>'', 'data'=>'');
		do{
			try{
				if( !is_array($params) || !isset($params['link']) || !isset($params['key']) ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'服务器忙，请稍后再试!';
					break;
				}

				$link	=	 $params['link'];
				$key	=	 $params['key'];
				$data	=	 Yii::app()->$link->get($key);//如果Memcache中没有找到，则返回false

				$ret_array['ret']	=	0;
				$ret_array['data']	=	$data;
			}catch (Exception $e) {
				$ret_array['ret']	=	13;
				$ret_array['msg']	=	'服务器忙，请稍后再试!';
				$ret_array['error']	=	$e->getMessage();
			}
		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_CfgAR_getMem.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	/**
	 * 删除指定键的缓存信息，如果无法连接memcache则返回端口错误
	 * @author kk
	 * @param name string -- 连接memcache值
	 * @param key string -- 连接memcache键
	 */
	public static function delMc( $params = array() ){
		$result	=	self::delMem($params);
		if( 0 != $result['ret'] ){
			return false;
		}
		return $result['data'];
	}

	public static function delMem($params=array()){
		$ret_array	=	array('ret'=>1, 'msg'=>'服务器忙，请稍后再试', 'occur'=>'CfgAR::delMem', 'error'=>'', 'data'=>false);
		do{
			try{
				if( !is_array($params) || !isset($params['link']) || !isset($params['key']) ){
					$ret_array['msg']	=	'调用参数缺失';
					break;
				}

				$link	=	trim($params['link']);
				$key	=	trim($params['key']);
				$result	=	Yii::app()->$link->delete($key);
				$ret_array['ret']	=	0;
				$ret_array['data']	=	$result;
			}catch( Exception $e ){
				$ret_array['ret']	=	0;
				$ret_array['error']	=	$e->getMessage();	
			}

		}while(0);
		if( 0 != $ret_array['ret'] ){
			Common::toTxt(array('file'=>'Log_Common_delMem.txt', 'txt'=>'Input:'.var_export($params, true).'|Output:'.var_export($ret_array, true)));
		}
		return $ret_array;
	}
	
	/**
	 * 删除指定端口的缓存信息，如果无法连接memcache则返回端口错误
	 * @author kk
	 * @copyright fzd
	 * @param name string -- 连接memcache值
	 * @param key string -- 连接memcache键
	 */
	public static function fusMc($params = array()) {
		$link	=	isset($params['link']) ? $params['link'] : '';
		$more	=	isset($params['more']) ? $params['more'] : false;//多台服务器
		if (!empty($link)) {
			try {
				$result	=	Yii::app()->$link->flush();
				return $result;
			} catch (Exception $e) {
				return false;//-11211
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 设置数据库链接信息，如果无法连接数据库则返回端口错误
	 * @author kk
	 * @param key string -- 连接数据库键
	 */
	public static function setDbLink($link) {
		if (!empty($link)) {
			try {
				return Yii::app()->$link;
			} catch (Exception $e) {
				return '-3306';
			}
		} else {
			return '-3306';
		}
	}
	
	/**
	 * 设置MongoDB数据库链接信息，如果无法连接数据库则返回端口错误
	 * @author kk
	 * @param key string -- 连接数据库键
	 */
	public static function setMgDbLink($link) {
		if (!empty($link)) {
			try {
				return $connection = Yii::app()->$link->getDbInstance();
			} catch (Exception $e) {
				return '-27017';
			}
		} else {
			return '-27017';
		}
	}
	
	/**
	 * 设置json信息
	 * @author kk
	 * @param data string -- 内容
	 */
	public static function enJson($data = '') {
		if (!empty($data)) {
			$args 			= array();
			if( isset($data['ret']) ){
				$args['ret'] 	= $data['ret'];
				$args['msg'] 	= isset( $data['msg'] ) ? $data['msg'] : '';
				$args['data'] 	= isset( $data['data'] ) ? $data['data'] : '';
			}else{
				$args	=	$data;
			}
			try {
				if( isset( $_GET['callback'] ) && !empty( $_GET['callback'] ) ){
					header('Content-type: text/javascript');
					return $_GET['callback'].'('. CJSON::encode($args) .')';
				}else{
					return CJSON::encode($args);
				}
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 解码json信息
	 * @author kk
	 * @param data string -- 内容
	 */
	public static function deJson($data = '') {
		if (!empty($data)) {
			try {
				return CJSON::decode($data);
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}
	
}