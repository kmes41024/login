<?php
	require_once('encoding.php');
	require_once($_SERVER['DOCUMENT_ROOT'] .'include/function_debug.php');
	require_once('db/conn.php');
	
	header('Content-type:text/json');
	
	session_start();
	
	if (isset($_POST["json"]))
		$jsonResStr = $_POST["json"];
	else
	{
		$resp['state'] = 'fail';
		$resp['msg'] = "ajax参数错误";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	$param=(array)(json_decode($jsonResStr));
	
	$mobile = $param['mobile'];
	if(strlen($mobile) != 11){
		$resp['state'] = 'fail';
		$resp['msg'] = "抱歉，请正确输入手机号码";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	if(is_numeric($mobile) === false){
		$resp['state'] = 'fail';
		$resp['msg'] = "抱歉，请正确输入手机号码";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	if(substr($mobile,0,1) != '1'){
		$resp['state'] = 'fail';
		$resp['msg'] = "抱歉，请正确输入手机号码";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	//取得uid
	$sql = "select * from `t_member` where f_mobile = '{$mobile}' order by id DESC";
	$rs_user = $conn->execute($sql);
	if (count($rs_user) == 0)
	{
		$resp['state'] = 'fail';
		$resp['msg'] = "抱歉，找不到使用信息, 请正确输入手机号码";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	$userid = $rs_user[0]['id'];
	
	//取得keyid
	$sql = "select * from t_company_authorkey where f_user_id = '{$userid}' order by id DESC"; 
	$keydata = $conn->execute($sql);
	
	if (count($keydata) == 0)
	{
		$resp['state'] = 'faial';
		$resp['msg'] = "抱歉，该授权码不存在，请重新输入";
		$resp['data'] = json_encode($keydata, JSON_UNESCAPED_UNICODE);
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if (strlen($keydata[0]['f_assign_datetime'])==null)
	{
		$resp['state'] = 'faial';
		$resp['msg'] = "抱歉，该授权码尚未被授权，请与贵公司人事部门进行确认";
		$resp['data'] = json_encode($keydata[0]['f_assign_datetime'], JSON_UNESCAPED_UNICODE);
		$resp['arr'] = json_encode($keydata, JSON_UNESCAPED_UNICODE);
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$gourl = '';
	$remqnid = intval($_COOKIE['remqnid']);
	if($remqnid > 0){
		$gourl = 'qnp_show.php?id=' . $remqnid;
		
		setcookie('remqnid','',NULL,'/');
	}
	$f_loginRndKey = FRndStr(20);
	$rs = $rs_user[0]; 
	
	if(count($rs) > 0){
		if(FPostInt('remember') <= 0){
			$expire = NULL;
		}else{
			$expire = time() + 60 * 60 * 24 * 365;
		}
		setcookie('muid');
		setcookie('mloginrndkey');
		setcookie('keyid');
		
		setcookie('muid', $userid, $expire, '/');
		setcookie('mloginrndkey', $f_loginRndKey, $expire, '/');
		setcookie('keyid', $keydata[0]['id'], $expire, '/');
		$conn->query("update `t_member` set f_logins = f_logins + 1,f_lastTimeInt = '" . time() . "',f_lastIP = '" . FGetClientIp() . "',f_loginRndKey = '" . $f_loginRndKey . "' where id = '{$userid}'");
		
		FMemberQNCookieToUid($userid);
		$rsMember = $rs;
		
		$resp['state'] = 'success';
		$resp['msg'] = "登入成功";
		$resp['data'] = $rsMember['f_fullname'];
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		
		exit;
	}
	else
	{
		$resp['state'] = 'fail';
		$resp['msg'] = "查无此账号或是手机号错误";
		echo json_encode($resp, JSON_UNESCAPED_UNICODE);
		exit;
	}
	