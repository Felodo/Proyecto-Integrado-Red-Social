$(document).ready(function(){
	//alert("USERS");
	
	var ias = jQuery.ias({
		container: '.box-users',
		item: '.user-item',
		pagination: '.pagination',
		next: '.pagination .next_link',
		triggerPageThreshold: 5
	}); 
	
	
	ias.extension(new IASTriggerExtension({
		text: 'Ver mas',
		offset: 3
	})); 
	
	ias.extension(new IASSpinnerExtension({
		src: URL+'/../assets/images/ajax-loader.gif'
	}));
	
	ias.extension(new IASNoneLeftExtension({
		text: 'No hay mas personas'
	})); 
	
	ias.on('ready', function(event){
		followButtons();
		activeButtons();
		deleteButtons();
	});
	
	ias.on('rendered', function(event){
		followButtons();
		activeButtons();
		deleteButtons();
	});
});

function activeButtons(){
	$(".btn-active").unbind("click").click(function(){
		$(this).addClass("hidden");
		$(this).parent().find(".btn-deactive").removeClass("hidden");
		$.ajax({
			url: URL+'/active',
			type: 'POST',
			data: { actived: $(this).attr("data-actived")},
			success: function(response){
				console.log(response);
			}				
		});
	});
	
	$(".btn-deactive").unbind("click").click(function(){
		$(this).addClass("hidden");
		$(this).parent().find(".btn-active").removeClass("hidden");
		$.ajax({
			url: URL+'/deactive',
			type: 'POST',
			data: { actived: $(this).attr("data-actived")},
			success: function(response){
				console.log(response);
			}				
		});
	});
}

function deleteButtons(){
	$(".btn-delete-user").unbind("click").click(function(){
		$(this).parent().parent().addClass("hidden");
		$.ajax({
			url: URL+'/delete/'+$(this).attr("data-user"),
			type: 'POST',
			data: { user: $(this).attr("data-user")},
			success: function(response){
				console.log(response);
			}				
		});
	});
}

/*function followButtons(){
	$(".btn-follow").unbind("click").click(function(){
		$(this).addClass("hidden");
		$(this).parent().find(".btn-unfollow").removeClass("hidden");
		$.ajax({
			url: URL+'/follow',
			type: 'POST',
			data: {followed: $(this).attr('data-followed')},
			success: function(response){
				console.log(response);
			}
		});
	}); 
	
	$(".btn-unfollow").unbind("click").click(function(){
		$(this).addClass("hidden");
		$(this).parent().find(".btn-follow").removeClass("hidden");
		$.ajax({
			url: URL+'/unfollow',
			type: 'POST',
			data: {followed: $(this).attr('data-followed')},
			success: function(response){
				console.log(response);
			}
		});
	}); 
}*/