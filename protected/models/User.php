<?php
/**
 * 用户基本信息
 * @author kk
 * @since 2017-09-05
 */
class User {
	public static $userCodeTableName    =   'app_user_code';
	public static $userInfoTableName    =   'app_user_info';
	/**
	 * 调用方法： user::checkUserRegInfo($params)
	 * 用户注册信息认证
	 * @since 2017-09-04
	 * @author kk
	 * @param  传递的参数
	 * 	name(用户姓名) 必须 
	 *  code(识别码)  必须 
	 *  employee_id 员工编号    选填内容
	 *  phone       员工电话，  选填内容
	 * @return  返回的参数
	 *  ret  状态值：0 正常，其他错误
	 *  msg  错误提示
	 *  data 返回绑定的其他用户信息，
	 *  数组形式返回 array( 'username','phone','department_id','sex','employee_id' )
	 *  具体参照 app_user_code 表
	 */
	public static function checkUserRegInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_checkUserRegInfo' );
		do{
			$name	=	isset( $params['name'] ) ? $params['name'] : '';
			$code	=	isset( $params['code'] ) ? $params['code'] : '';
			$employee_id	=	isset( $params['employee_id'] ) ? $params['employee_id'] : '';
			$phone			=	isset( $params['phone'] ) ? $params['phone'] : '';
			if( empty( $name ) || empty( $code ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'注册失败，没有传递正确的用户信息！';
				break;
			}
			$where			=	' WHERE username=:username and PIN=:PIN ';
			$connection		=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			$sql		=	"SELECT * FROM ". self::$userCodeTableName . $where ;
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":username", $name , PDO::PARAM_STR );
			$command->bindParam(":PIN", $code , PDO::PARAM_STR );
			$resDate	=	$command->queryRow();
			if( empty( $resDate ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'注册失败，没有对应的用户信息！请联络管理员分配账户！';
				break;
			}
			if( 1==$resDate['status'] ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'注册失败，'.REG_CODE_NAME .'已被使用！';
				break;
			}
			if( 2==$resDate['status'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'注册失败，'.REG_CODE_NAME .'已过期！';
				break;
			}
			if( 3==$resDate['status'] ){
				$ret_array['ret']	=	4;
				$ret_array['msg']	=	'注册失败，'.REG_CODE_NAME .'已被删除！';
				break;
			}
			if( 0!=$resDate['status'] ){
				$ret_array['ret']	=	5;
				$ret_array['msg']	=	'注册失败，'.REG_CODE_NAME .'不正确！请联系管理员重新分配！';
				break;
			}
			$ret_array['ret']		=	0;
			$ret_array['data']		=	$resDate;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_checkUserRegInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	
	/**
	 * 调用方法 User::login($params)
	 * 用户登录接口
	 * @since 2017-09-04
	 * @param  传递的参数
	 * @author kk
	 * 	name(用户姓名、电话、邮箱) 必须 
	 *  password   必须 
	 * @return  返回的参数
	 *  ret  状态值：0 正常，其他错误
	 *  msg  错误提示
	 *  data 返回绑定的其他用户信息，
	 *  数组形式返回 array()
	 *  id,username,department_id,employee_id,phone,e_mail,occupation_id,   status,      grade
	 *  用户id,用户名，     部门项目id,     员工编号,      电话，     邮箱，          岗位id，                     状态（0，正常，1删除），权限等级（0，普通，》0 管理员）
	 *  具体参照 app_user_Info 表
	 */
	public static function userLogin($params)
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_userLogin' );
		do{
			$name		=	isset( $params['name'] ) ? $params['name'] : '';
			$password	=	isset( $params['password'] ) ? md5($params['password']) : '';
			if( empty( $name ) || empty( $password ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'登录失败，请输入正确的用户名和密码！';
				break;
			}
			$connection		=	CfgAR::setDbLink( 'db' );
			if( '-3306'==$connection ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'服务器异常，请稍后再试！';
				break;
			}
			//判断是否是邮箱，判断条件是否还@
			if( strpos( $name , '@' ) ){
				$where			=	' WHERE e_mail=:username and password=:password ';
			}
			//判断是否是手机号码
			if( Common::matchPhone( $name ) ){
				$where			=	' WHERE phone=:username and password=:password ';
			}
			//用户姓名判断
			if( !preg_match("[^\x80-\xff]",$name) ){
				$where			=	' WHERE username=:username and password=:password ';
			}
			$sql		=	"SELECT id,username,department_id,employee_id,phone,e_mail,occupation_id,status,grade FROM ". self::$userInfoTableName . $where ;
			$command	=	$connection->createCommand($sql);
			$command->bindParam(":username", $name , PDO::PARAM_STR );
			$command->bindParam(":password", $password , PDO::PARAM_STR );
			$resDate	=	$command->queryRow();
			if( !empty( $resDate ) ){
				if( 0!=$resDate['status'] ){
					$ret_array['ret']	=	1;
					$ret_array['msg']	=	'登录失败，用户已被删除！';
					break;
				}
				$ret_array['ret']	=	0;
				$ret_array['data']	=	$resDate;
				break;
			}
			$ret_array['ret']	=	3;
			$ret_array['msg']	=	'登录失败，用户名或者密码错误！';
			break;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_userLogin.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取对应的用户部门
	 * 调用方法 User::getUserDepartment($params)
	 * @since 2017-09-04
	 * @author kk
	 * @param  传递的参数
	 * 	id(用户id) 		必须 
	 *  grade(用户权限)   必须 
	 * @return  返回的参数
	 *  ret  状态值：0 正常，其他错误
	 *  msg  错误提示
	 *  data 返回绑定的其他用户信息，
	 *  数组形式返回 array(
	 *  	'list'=>'部门列表,多维数组','myDeparment'=>'已经加入的部门列表'
	 *  )
	 *  list=>array( array(),array() )数组字段: id(部门id),uid(部门管理员),department_name(名称),pic(缩略图),department_des（描述）,status（状态，0正常，1删除）
	 *  myDeparment=>array( 部门id1,部门id2 )
	 * 
	 */
	public static function getUserDepartment( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_getUserDepartment' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			if( empty( $id ) || !isset( $params['grade'] ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'获取'.APP_DEPARTMENT_NAME.'失败，请传递完整的用户信息！';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'获取'.APP_DEPARTMENT_NAME.'失败，用户信息不存在！';
				break;
			}
			if( 0!=$resData['data']['status'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'获取'.APP_DEPARTMENT_NAME.'失败，用户已被禁用！';
				break;
			}
			if( $grade!=$resData['data']['grade']  ){
				$ret_array['ret']	=	4;
				$ret_array['msg']	=	'获取'.APP_DEPARTMENT_NAME.'失败，用户权限不正确！';
				break;
			}
			//step 1 获取用户的部门信息，现有逻辑用户只绑定一个项目部门，数据来源于用户信息表
			$myDeparment			=	$resData['data']['department_id'];
			//step 2 获取项目部门列表
			$resData		=	AppDepartment::getDepartmentList( array( 'status'=>0 ) );
			if( 0!=$resData['ret'] ){
				$ret_array	=	$resData;
				break;
			}
			$ret_array['ret']	=	0;
			$ret_array['data']	=	array( 'list'=>$resData['data'] , 'myDeparment'=>array( 0=>$myDeparment ) );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_getUserDepartment.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * @author kk
	 * @since 2017-09-06
	 * 调用方法 User::addDepartmentInfo($params)
	 * 新增部门信息
	 * @param  传递的参数
	 * id,grade,uid,department_name,pic,department_des,(参数说明，同获取部门列表)
	 */
	public static function addDepartmentInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_addDepartmentInfo' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			if( empty( $id ) || empty( $grade ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'新增'.APP_DEPARTMENT_NAME.'失败！';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'新增'.APP_DEPARTMENT_NAME.'失败！';
				break;
			}
			if( 1!=$resData['data']['grade'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'新增'.APP_DEPARTMENT_NAME.'失败！管理员才能新增'.APP_DEPARTMENT_NAME;
				break;
			}
			unset( $params['id'] );
			unset( $params['grade'] );
			$ret_array	=	AppDepartment::addDepartmentInfo( $params );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_addDepartmentInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 修改对应的部门信息
	 * 调用方法 User::updateDepartmentInfo($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id,grade ( 修改人id,权限等级) 必须
	 * department_id, （部门id）必须
	 * uid, 部门管理
	 * department_name, 部门名称
	 * department_des, 部门描述
	 * pic, 部门图片
	 * status, 状态 0正常，1删除
	 * remark 修改描述
	 * 
	 */
	public static function updateDepartmentInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_addDepartmentInfo' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			if( empty( $id ) || empty( $grade ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改'.APP_DEPARTMENT_NAME.'失败！';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'修改'.APP_DEPARTMENT_NAME.'失败！';
				break;
			}
			if( 1!=$resData['data']['grade'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'修改'.APP_DEPARTMENT_NAME.'失败！管理员才能新增'.APP_DEPARTMENT_NAME;
				break;
			}
			unset( $params['id'] );
			unset( $params['grade'] );
			$ret_array	=	AppDepartment::updateDepartmentInfo( $params );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_addDepartmentInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 修改用户密码
	 * 调用方法 User::updateUserPassword($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id,name,password ( 修改人id,权限等级) 必须
	 */
	public static function updateUserPassword( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_updateUserPassword' );
		do{
			$id			=	isset( $params['id'] ) ? $params['id'] : '';
			$password	=	isset( $params['password'] ) ? $params['password'] : '';
			if( empty( $id ) || empty( $password ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改密码失败！';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'修改密码失败，用户信息不存在！';
				break;
			}
			$ret_array	=	UserCommon::updateUserInfo( array( 'id'=>$id , 'password'=>$password ,'remark'=>'|'.date('Y-m-d H:i:s').'修改密码' ) );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_updateUserPassword.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 管理员删除用户 
	 *  调用方法 User::delUserInfo($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id,grade,uid ( 管理员id,权限等级，用户id) 必须
	 */
	public static function delUserInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_delUserInfo' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			$uid	=	isset( $params['uid'] ) ? $params['uid'] : '';
			if( empty( $id ) || empty( $grade ) || empty( $uid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'删除用户失败';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'删除用户失败,管理员信息不正确！';
				break;
			}
			if( 1!=$resData['data']['grade'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'删除用户失败,管理员信息不正确！';
				break;
			}
			$ret_array	=	AppDepartment::updateDepartmentInfo( array( 'id'=>$uid ,'status'=>1 ) );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_delUserInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 管理员重置用户密码
	 * 调用方法 User::setUserPassword($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id,grade,uid ( 管理员id,权限等级，用户id) 必须
	 */
	public static function setUserPassword( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_setUserPassword' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			$uid	=	isset( $params['uid'] ) ? $params['uid'] : '';
			if( empty( $id ) || empty( $grade ) || empty( $uid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'重置用户密码失败';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'重置用户密码失败,管理员信息不正确！';
				break;
			}
			if( 1!=$resData['data']['grade'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'重置用户密码失败,管理员信息不正确！';
				break;
			}
			$ret_array	=	AppDepartment::updateDepartmentInfo( array( 'id'=>$uid ,'password'=>md5(APP_DEFINE_PASSWORD) ) );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_setUserPassword.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 生成对应的识别码
	 * 调用方法 User::createCode($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id,grade,uname ( 管理员id,权限等级，用户名) 必须
	 * phone，department_id 电话，部门id
	 */
	public static function createCode( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_createCode' );
		do{
			$id		=	isset( $params['id'] ) ? $params['id'] : '';
			$grade	=	isset( $params['grade'] ) ? $params['grade'] : '';
			$uname	=	isset( $params['uname'] ) ? $params['uname'] : '';
			if( empty( $id ) || empty( $grade ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'生成'.REG_CODE_NAME.'失败';
				break;
			}
			$resData	=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'生成'.REG_CODE_NAME.'失败';
				break;
			}
			if( 1!=$resData['data']['grade'] ){
				$ret_array['ret']	=	3;
				$ret_array['msg']	=	'生成'.REG_CODE_NAME.'失败'.',管理员信息不正确！';
				break;
			}
			$params['creator']		=	$resData['data']['username'];
			unset($params['id']);
			unset($params['grade']);
			$ret_array	=	UserCommon::createUserCode( $params );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_createCode.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	
	/**
	 * 获取岗位列表信息
	 * 调用方法 User::getOccupationList($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * id岗位id,name岗位名称
	 */
	public static function getOccupationList( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_getOccupationList' );
		do{
			$ret_array	=	AppOccupation::getInfoList();
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_getOccupationList.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取岗位信息职能列表
	 * 调用方法 User::getOccupationAbility($params)
	 * @author kk
	 * @since 2017-09-06
	 * @param
	 * oid 对应的岗位id
	 * @return 多维数组
	 * id,oid,ability(技能名称),ability_description（技能描述）,status,flag（0,固定，1选择，2自定义）
	 */
	public static function getOccupationAbility( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_getOccupationAbility' );
		do{
			$oid	=	isset( $params['oid'] ) ? $params['oid'] : '';
			if( empty( $oid ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'获取岗位职责信息失败，没有传递对应的信息';
				break;
			}
			$ret_array	=	AppOccupation::getAbilityList( $params );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_getOccupationAbility.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 保存个人信息，用于第一次注册
	 * User::saveUserInfo( $params )
	 * @since 2017-09-08
	 * @author kk
	 * @param
	 * code_id	//使用对应的code,注册的时候传递回去的id
	 * username,//用户名 --必须
	 * department_id,//所属部门id
	 * employee_id, //员工编号
	 * phone,	//电话
	 * occupation_id, //职位id  必须
	 * sex,
	 * birth,
	 * e_mail,
	 * password,
	 * ability	//技能多维数组 array( oid（技能id）,ability,ability_description,flag(0,固定，1选择，2自定义) )
	 */
	public static function saveUserInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_saveUserInfo' );
		do{
			$codeId			=	isset( $params['code_id'] ) ? $params['code_id'] : '';
			$username		=	isset( $params['username'] ) ? $params['username'] : '';
			$department_id	=	isset( $params['department_id'] ) ? $params['department_id'] : '';
			$employee_id	=	isset( $params['employee_id'] ) ? $params['employee_id'] : '';
			$phone			=	isset( $params['phone'] ) ? $params['phone'] : '';
			$occupation_id	=	isset( $params['occupation_id'] ) ? $params['occupation_id'] : '';
			$sex			=	isset( $params['sex'] ) ? $params['sex'] : '';
			$birth			=	isset( $params['birth'] ) ? $params['birth'] : '';
			$e_mail			=	isset( $params['e_mail'] ) ? $params['e_mail'] : '';
			$password		=	isset( $params['password'] ) ? md5($params['password']) : '';
			$ability		=	isset( $params['ability'] ) ? $params['ability'] : '';
			if( empty( $codeId ) || empty( $username ) || empty( $occupation_id ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'保存用户信息失败';
				break;
			}
			//step1 新增用户信息，
			$ret_array		=	UserCommon::addUserInfo($params);
			//step2 使用code码
			UserCommon::useUserCode( array( 'id'=>$codeId ) );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_saveUserInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * @author kk
	 * @since 2017-09-08
	 * 修改用户基本信息
	 * User::updateUserInfo($params)
	 * @param 
	 * id,必须
	 * department_id,//所属部门id
	 * employee_id, //员工编号
	 * phone,	//电话
	 * occupation_id, //职位id  必须
	 * sex,
	 * birth,
	 * e_mail,
	 * password,
	 * ability	//技能多维数组 array( oid（技能id）,ability,ability_description,flag(0,固定，1选择，2自定义) )
	 */
	public static function updateUserInfo( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_updateUserInfo' );
		do{
			$id				=	isset( $params['id'] ) ? $params['id'] : '';
			$ability		=	isset( $params['ability'] ) ? $params['ability'] : '';
			if( empty( $id ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'修改用户信息失败';
				break;
			}
			if( isset( $params['ability'] ) ){
				AppOccupation::updateAbilityInfo( array( 'uid'=>$id ,'ability'=>$ability ) );
			}
			unset( $params['ability'] );
			//修改用户信息，
			$ret_array		=	UserCommon::updateUserInfo( $params );
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_updateUserInfo.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
	/**
	 * 获取个人岗位职责信息
	 * User::getUserAbility( $params )
	 * id,必须 用户id
	 * 返回值，
	 *  data 多维数组
	 *  oid，ability，ability_description，flag（0，固定，1选择，2自定义），status=>1 选中状态
	 */
	public static function getUserAbility( $params )
	{
		$ret_array	=	array( 'ret'=>-1 ,'msg'=>null ,'data'=>null ,'occur'=>'M_User_getUserAbility' );
		do{
			$id				=	isset( $params['id'] ) ? $params['id'] : '';
			$occupationArr	=	array();
			if( empty( $id ) ){
				$ret_array['ret']	=	1;
				$ret_array['msg']	=	'获取个人岗位职责信息失败';
				break;
			}
			$resData		=	UserCommon::getUserInfoById( array( 'id'=>$id ) );
			if( 0!=$resData['ret'] || empty( $resData['data'] ) ){
				$ret_array['ret']	=	2;
				$ret_array['msg']	=	'获取个人岗位职责信息失败';
				break;
			}
			$occupation_id	=	$resData['data']['occupation_id'];
			$resData		=	AppOccupation::getAbilityList( array( 'oid'=>$occupation_id ) );
			if( 0!=$resData['ret'] ){
				$ret_array	=	$resData;
				break;
			}
			$t1Arr			=	$resData['data'];
			//获取用户岗位职责信息
			$resData		=	AppOccupation::getUserAbilityList( $params );
			if( 0!=$resData['ret'] ){
				$ret_array	=	$resData;
				break;
			}
			$t2Arr			=	$resData['data'];
			$tempArr		=	array();
			foreach( $t2Arr as $key =>$val)
			{
				if( 1==$val['flag'] ){//选择的
					$tempArr[$val['oid']]	=	$val;
				}else{//自定义的
					$val['status']	=	1;
					$t1Arr[]	=	$val;
				}
				unset( $val );
			}
			foreach( $t1Arr as $key =>$val )
			{
				if( 1==$val['flag'] && array_key_exists( $t1Arr[$key]['id'] , $tempArr ) ){
					$t1Arr[$key]['status']	=	1;
				}
			}
			$ret_array['ret']	=	0;
			$ret_array['data']	=	$t1Arr;
		}while(0);
		if( 0!=$ret_array['ret'] ){
			Common::toTxt( array('file'=>'Log_M_User_getUserAbility.txt', 'txt'=>'input:'. var_export( $params ,true ) .'| outPut:'.var_export( $ret_array, true) ) );
		}
		return $ret_array;
	}
}
