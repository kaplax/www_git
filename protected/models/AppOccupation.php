<?php
/**
 * 获取岗位信息
 * 内部使用
 * @author kk
 * @since 2017-09-07
 *
 */
class AppOccupation {
	public static $occupationList_key		=	'AppOccupation_occupationList';//岗位列表信息缓存前缀
	public static $occupationTableName    	=   'app_occupation';
	public static $abilityTableName    		=   'app_occupation_ability';
	public static $ability_key				=	'AppOccupation_ability';
	public static $abilityCustomerTableName =   'app_occupation_custom';
	/**
	 * 清空部门缓存信息
	 */
	public static function clearAppOccupationMemcache( $params =array() )
	{
		do{
			$canmcArr	=	array(
					self::$occupationList_key
			);
			foreach( $canmcArr as $key =>$val){
				CfgAR::delMc( array( 'link'=>'memcache' , 'key'=>$val ) );
			}
		}while(0);
	}
	/**
	 * 检查部门名称是否已经存在了
	 */
	public static function checkOccupationName( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_checkOccupationName' );
		do{
			$name	=	isset( $params['name'] ) ? $params['name'] : '';
			if( empty( $name ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'必要参数缺失';
				break;
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$where		=	' where name=:name';
			$sql		=	"select id from ".self::$occupationTableName." {$where}";
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":name", $name, PDO::PARAM_STR);
			$resDate	=	$command->queryAll();
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_checkOccupationName.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 新增岗位信息
	 */
	public static function addInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_addInfo' );
		do{
			$name				=	isset( $params['name'] ) ? $params['name'] : '';
			$status				=	0;
			$lasttime			=	date( 'Y-m-d H:i:s' );
			$remark				=	date( 'Y-m-d H:i:s' ).'新增记录';
			if( empty( $name ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增岗位信息失败！必要参数缺失';
				break;
			}
			//检查名字是否已经存在了
			$resData			=	AppOccupation::checkOccupationName( $params );
			if( 0!=$resData['ret'] ){
				$ret_array		=	$resData;
				break;
			}
			if( count( $resData['data'] ) >0 ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增岗位信息失败！该岗位已经存在了';
				break;
			}
			$connection					=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
				
			$sql		=	"INSERT INTO ". self::$occupationTableName ." (name,status,lasttime,remark) VALUES(:name,:status,:lasttime,:remark)";
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":name", $name , PDO::PARAM_INT);
			$command->bindParam(":status", $status , PDO::PARAM_STR);
			$command->bindParam(":lasttime", $lasttime , PDO::PARAM_STR);
			$command->bindParam(":remark", $remark , PDO::PARAM_STR);
			$resDate	=	$command->execute();
			if( $resDate )
			{
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$connection->getLastInsertID();
				AppDepartment::clearDepartmentMemcache();
				break;
			}
			$ret_array['ret']			=	4;
			$ret_array['msg']			=	'新增岗位信息失败！记录数据失败！';
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_addInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 修改岗位信息
	 */
	public static function updateInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_updateInfo' );
		do{
			$department_id		=	isset( $params['department_id'] ) ? $params['department_id'] : '';
			$remark				=	isset( $params['remark'] ) ? $params['remark'] : '|'.date('Y-m-d H:i:s') .'修改内容';
			$lasttime			=	date( 'Y-m-d H:i:s' );
			if( empty( $department_id ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改岗位信息失败！必要参数缺失';
				break;
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$sql		=	'UPDATE ' . self::$occupationTableName . ' SET ';
			foreach( $params as $key =>$val ){
				if( 'id'!=$key && 'remark'!=$key ){
					$sql	.=	" {$key}=:{$key},";
				}
			}
			$sql		.=	" lasttime=:lasttime,remark=CONCAT(remark,:remark)";
			$sql		.=	' WHERE id=:id';
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":id", $department_id , PDO::PARAM_INT);
			foreach( $params as $key =>$val ){
				if( 'id'!=$key && 'remark'!=$key ){
					$command->bindParam(":{$key}", $val , PDO::PARAM_STR);
					unset( $val );
				}
			}
			$command->bindParam(":lasttime", $lasttime , PDO::PARAM_STR);
			$command->bindParam(":remark", $remark , PDO::PARAM_STR);
			$resDate	=	$command->execute();
			if( $resDate ){
				$ret_array['ret']	=	0;
				$ret_array['msg']	=	'修改成功！';
				AppOccupation::clearAppOccupationMemcache();
				break;
			}
			$ret_array['msg']		=	'修改失败，请稍候再试！';
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_updateInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取岗位信息列表
	 */
	public static function getInfoList( $params=array() )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_getInfoList' );
		do{
			$status	=	isset( $params['status'] ) ? $params['status'] : 0;
			$canmc	=	isset( $params['canmc'] ) ? $params['canmc'] : true;
			$mc_key	=	self::$occupationList_key . $status;
			if( $canmc ){
				$resData			=	CfgAR::getMc( array( 'link'=>'memcache' , 'key'=>$mc_key ) );
				if( $resData ){
					$ret_array['ret']	=	0;
					$ret_array['data']	=	$resData;
					break;
				}
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$where		=	' where id<>0  ';
			if( 'all'!= (string)( $status ) ){
				$where	.=	'and status=:status';
			}
			$sql		=	"select id,name,status from ".self::$occupationTableName." {$where}";
			$command	=	$connection->createCommand($sql);
			if( 'all'!= (string)( $status ) ){
				$command->bindParam(":status", $status, PDO::PARAM_INT);
			}
			$resDate	=	$command->queryAll();
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
			if( !empty($resDate) ){
				CfgAR::setMc( array( 'link'=>'memcache' ,'key'=>$mc_key ,'data'=>$resDate ) );
			}
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_getInfoList.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取对应的岗位职责信息列表
	 */
	public static function getAbilityList( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_getAbilityList' );
		do{
			$oid	=	isset( $params['oid'] ) ? $params['oid'] : '';//对应的职业id
			$canmc	=	isset( $params['canmc'] ) ? $params['canmc'] : true;
			$mc_key	=	self::$ability_key . $oid;
			if( $canmc ){
				$resData			=	CfgAR::getMc( array( 'link'=>'memcache' , 'key'=>$mc_key ) );
				if( $resData ){
					$ret_array['ret']	=	0;
					$ret_array['data']	=	$resData;
					break;
				}
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$where		=	' where oid=:oid  ';
			$sql		=	"select id,oid,ability,ability_description,status,flag from ".self::$abilityTableName." {$where}";
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":oid", $oid, PDO::PARAM_INT);
			$resDate	=	$command->queryAll();
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
			if( !empty($resDate) ){
				CfgAR::setMc( array( 'link'=>'memcache' ,'key'=>$mc_key ,'data'=>$resDate ) );
			}
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_getAbilityList.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 新增岗位职责信息
	 */
	public static function addAbilityInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_addAbilityInfo' );
		do{
			$oid				=	isset( $params['oid'] ) ? $params['oid'] : '';
			$ability			=	isset( $params['ability'] ) ? $params['ability'] : '';
			$flag				=	isset( $params['flag'] ) ? $params['flag'] : '';
			$ability_description=	isset( $params['ability_description'] ) ? $params['ability_description'] : '';
			$status				=	0;
			$lasttime			=	date( 'Y-m-d H:i:s' );
			$remark				=	date( 'Y-m-d H:i:s' ).'新增记录';
			if( empty( $oid ) || empty( $ability ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增岗位职责信息失败！必要参数缺失';
				break;
			}
			//检查名字是否已经存在了
			$resData			=	AppOccupation::checkAbilityName( array('name'=>$ability,'oid'=>$oid ) );
			if( 0!=$resData['ret'] ){
				$ret_array		=	$resData;
				break;
			}
			if( count( $resData['data'] ) >0 ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增岗位职责信息失败！该岗位已经存在了';
				break;
			}
			$connection					=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
	
			$sql		=	"INSERT INTO ". self::$occupationTableName ." (oid,ability,ability_description,status,flag,lasttime,remark) VALUES(:oid,:ability,:ability_description,:status,:flag,:lasttime,:remark)";
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":oid", $oid , PDO::PARAM_INT);
			$command->bindParam(":ability", $ability , PDO::PARAM_STR);
			$command->bindParam(":ability_description", $ability_description , PDO::PARAM_STR);
			$command->bindParam(":flag", $flag , PDO::PARAM_INT);
			$command->bindParam(":status", $status , PDO::PARAM_INT);
			$command->bindParam(":lasttime", $lasttime , PDO::PARAM_STR);
			$command->bindParam(":remark", $remark , PDO::PARAM_STR);
			$resDate	=	$command->execute();
			if( $resDate )
			{
				$ret_array['ret']		=	0;
				$ret_array['data']		=	$connection->getLastInsertID();
				AppDepartment::clearDepartmentMemcache();
				break;
			}
			$ret_array['ret']			=	4;
			$ret_array['msg']			=	'新增岗位职责信息失败！记录数据失败！';
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_addAbilityInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 删除对应的岗位职责
	 * 只保留最新的岗位职责
	 */
	public static function deleteAbilityInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_updateAbilityInfo' );
		do{
			$uid	=	isset( $params['uid'] ) ? $params['uid'] : '';
			if( empty( $uid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改岗位职责信息失败！必要参数缺失';
				break;
			}
			$connection					=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$sql		=	'DELETE FROM ' . self::$abilityCustomerTableName . ' WHERE uid=:uid ';
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":uid", $uid , PDO::PARAM_INT);
			$resDate	=	$command->execute();
			$ret_array['ret']	=	0;
			$ret_array['msg']	=	'操作成功！';
			break;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_updateAbilityInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 新增用户岗位职责信息
	 */
	public static function addNewAbilityInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_addNewAbilityInfo' );
		do{
			$uid			=	isset( $params['uid'] ) ? $params['uid'] : '';
			$oid			=	isset( $params['oid'] ) ? $params['oid'] : '';
			$ability		=	isset( $params['ability'] ) ? $params['ability'] : '';
			$ability_description=	isset( $params['ability_description'] ) ? $params['ability_description'] : '';
			$status			=	0;
			$flag			=	isset( $params['flag'] ) ? $params['flag'] : 0;
			$remark			=	isset( $params['remark'] ) ? $params['remark'] : '|'.date('Y-m-d H:i:s') .'修改内容';
			$lasttime		=	date( 'Y-m-d H:i:s' );
			if( empty( $uid ) || empty( $ability ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增岗位职责信息失败！必要参数缺失';
				break;
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$sql		=	'INSERT INTO ' . self::$abilityTableName . ' (uid,oid,ability,ability_description,status,flag,lasttime,remark) VALUES(:uid,:oid,:ability,:ability_description,:status,:flag,:lasttime,:remark) ';
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":uid", $uid , PDO::PARAM_INT);
			$command->bindParam(":oid", $oid , PDO::PARAM_STR);
			$command->bindParam(":ability", $ability , PDO::PARAM_STR);
			$command->bindParam(":ability_description", $ability_description , PDO::PARAM_STR);
			$command->bindParam(":status", $status , PDO::PARAM_STR);
			$command->bindParam(":flag", $flag , PDO::PARAM_STR);
			$command->bindParam(":lasttime", $lasttime , PDO::PARAM_STR);
			$command->bindParam(":remark", $remark , PDO::PARAM_STR);
			$resDate	=	$command->execute();
			if( $resDate ){
				$ret_array['ret']	=	0;
				$ret_array['msg']	=	'新增成功！';
				break;
			}
			$ret_array['msg']		=	'新增失败，请稍候再试！';
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_addNewAbilityInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 修改岗位职责信息
	 */
	public static function updateAbilityInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_updateAbilityInfo' );
		do{
			$uid			=	isset( $params['uid'] ) ? $params['uid'] : '';
			$ability		=	isset( $params['ability'] ) ? $params['ability'] : '';
			$remark			=	isset( $params['remark'] ) ? $params['remark'] : '|'.date('Y-m-d H:i:s') .'修改内容';
			$lasttime		=	date( 'Y-m-d H:i:s' );
			if( empty( $uid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改岗位职责信息失败！必要参数缺失';
				break;
			}
			AppOccupation::deleteAbilityInfo( array( 'uid'=>$uid ) );
			foreach( $ability as $key =>$val ){
				$temp				=	$val;
				$temp['uid']		=	$uid;
				AppOccupation::addNewAbilityInfo($temp);
				unset( $temp );
			}
			$ret_array['ret']		=	0;
			$ret_array['msg']		=	'修改失败，请稍候再试！';
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_updateAbilityInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 检查岗位信息是否已经存在了
	 */
	public static function checkAbilityName( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_checkAbilityName' );
		do{
			$name	=	isset( $params['name'] ) ? $params['name'] : '';
			$oid	=	isset( $params['oid'] ) ? $params['oid'] : '';
			if( empty( $name ) || empty( $oid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'必要参数缺失';
				break;
			}
			$connection	=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$where		=	' where ability=:ability and oid=:oid';
			$sql		=	"select id from ".self::$abilityTableName." {$where}";
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":oid", $oid, PDO::PARAM_INT);
			$command->bindParam(":ability", $name, PDO::PARAM_STR);
			$resDate	=	$command->queryAll();
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_checkAbilityName.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取用户岗位职能列表
	 */
	public static function getUserAbilityList( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_AppOccupation_getUserAbilityList' );
		do{
			$uid			=	isset( $params['uid'] ) ? $params['uid'] : '';
			if( empty( $uid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改岗位职责信息失败！必要参数缺失';
				break;
			}
			$connection					=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$sql		=	'select * FROM ' . self::$abilityCustomerTableName . ' WHERE uid=:uid ';
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":uid", $uid , PDO::PARAM_INT);
			$resDate	=	$command->queryAll();
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_AppOccupation_getUserAbilityList.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
}
