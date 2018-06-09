$(document).ready(function(){
	$(".nickname-input").blur(function(){
		var nickname = this.value;
		$.ajax({
			url: URL+'/nickname-test',
			data: {nickname: nickname},
			type: 'POST',
			success: function(response){
				if(response == "used"){
					$(".nickname-input").css("border","1px solid red");
				}else{
					$(".nickname-input").css("border","1px solid green");
				}
			}
		});
	});
});