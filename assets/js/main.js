function LRPostSMS2(){
	var mlr_mobile = $('#mlr_mobile').val();
	var mlr_authorkey = 'test2'; 
	
	$("#mlr_mbtn").attr('disabled',true);
	$.ajax({ 
		url: 'bin.php?clause=postsms',
		dataType: "json",
		data : {"mobile":mlr_mobile},
		type: "POST",
		success: function(json){
			if(json.state == 'success'){
				layui.layer.msg('短信发送成功，请检查您的手机',{time:2000});
				
				iTime = 59;
				SmsTimeDown();
			} else {
				layui.layer.alert(json.msg);
			}
		},
		complete:function(){
			$("#mlr_mbtn").attr('disabled',false);
		}
	});
};

function LRLogin(){
	var mlr_mobile = $('#mlr_mobile').val();
	var mlr_authorkey = $('#mlr_authorkey').val();
	var mlr_mcode = $('#mlr_mcode').val();
	var jsonRes = {};
	jsonRes['mobile'] = mlr_mobile;
	jsonRes['vAuthorkey'] = mlr_authorkey;
	jsonRes['mCode'] = mlr_mcode;
	var jsonResStr = JSON.stringify(jsonRes);
	
	$.ajax({ 
		url: "login_key.php",
		dataType: "JSON",
		data:{
			"json":jsonResStr,
		},
		type: "POST",
		success: function(obj){
			if(obj.state == 'success'){
				layui.layer.msg(obj.msg,{time:2000});
				setTimeout(function(){
					$('#keyLoginModal').hide();
					$('.login-modal').fadeOut();
				},2000);
			} else {
				layui.layer.msg(obj.msg);
			}
		},
		complete:function(json){
			if (typeof(json.responseJSON) == 'undefined')
			{
				console.log(json);
			}
		}
		
	});
}