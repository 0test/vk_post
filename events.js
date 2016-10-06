	$j(".vk_post_action").click(function(){
		if(thisid!=0){
			$j.ajax({
				type:"POST",
				url:"/assets/plugins/vkpost_old/ajax.php",
				data:{
					id:thisid
				},
				success:function(data){
					$j(mytv).val(parseInt($j(mytv).val())+1);
					$j(".vk_post_result").css({'display':'inline-block','background':'#008b00'});
					$j(".vk_post_result").html(data);
				},
				beforeSend:function(XMLHttpRequest){
					//$j(".vk_post_action").attr('disabled',true);
					$j(".vk_post_result").css({'display':'inline-block','background':'#777'});
					$j(".vk_post_result").html('Отправляю...');
					
				},
				complete:function(){
					/*
					setTimeout(function() {
							$j(".vk_post_result").slideUp('slow');
							$j(".vk_post_action").prop('disabled',false);
						}, 2000);
					*/
					
				},
				error: function() {
					$j('.vk_post_result').css({'display':'inline-block','background':'#8B0000'});
					$j('.vk_post_result').html('Ошибка ajax').slideDown('slow');
					
				  }
				});
			}
			else{
				$j('.vk_post_result').css({'display':'inline-block','background':'#8B0000'});
				$j('.vk_post_result').html('Документ не сохранен').slideDown('slow');
				
			}			
		});
