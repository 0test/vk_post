$(".vk_post_action").click(function(){
	if(thisid!=0){
		$.ajax({
			type:"POST",
			url:"/assets/plugins/vkpost_old/ajax.php",
			data:{
				id:thisid
			},
			success:function(data){
				$(mytv).val(parseInt($(mytv).val())+1);
				$(".vk_post_result").css({'display':'inline-block','background':'#008b00'});
				$(".vk_post_result").html(data);
			},
			beforeSend:function(XMLHttpRequest){
				//$(".vk_post_action").attr('disabled',true);
				$(".vk_post_result").css({'display':'inline-block','background':'#777'});
				$(".vk_post_result").html('Отправляю...');
				
			},
			complete:function(){
				/*
				setTimeout(function() {
						$(".vk_post_result").slideUp('slow');
						$(".vk_post_action").prop('disabled',false);
					}, 2000);
				*/
				
			},
			error: function() {
				$('.vk_post_result').css({'display':'inline-block','background':'#8B0000'});
				$('.vk_post_result').html('Ошибка ajax').slideDown('slow');
				
				}
			});
		}
		else{
			$('.vk_post_result').css({'display':'inline-block','background':'#8B0000'});
			$('.vk_post_result').html('Документ не сохранен').slideDown('slow');
			
		}			
	});
