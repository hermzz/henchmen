<?php

$config = yaml_parse_file('config.yml');

if(!isset($config['servers']))
	die('Set some servers in the config first!');
?>
<html>
	<head>
		<title>Henchmen</title>
		<script src="jquery.js"></script>
		<script>
			$(document).ready(function() 
			{
				$('#deploy_form').submit(function() 
				{
					$('.servers').each(function(i, e) {
						$(e).parent().find('.status').append('<img src="spinner.gif" /'+'> Reticulating splines...');
						$(e).parent().find('.status').show();
						
						$.ajax(
							{
								url: 'worker.php?server='+$(e).attr('name'),
								dataType: 'json',
								success: show_ajax_results,
								error: function(jqXHR, text, error) {
									$('.server_'+$(e).attr('name').replace(/\./g, '_')).find('.status').attr('class', 'status bad').html(
										'Error parsing worker response!'
									);
								}
							}
						);
					});
					
					return false;
				});
				
				$('input[name="all"]').click(function(e) {	
					if(e.target.checked)
					{
						$('input[type="checkbox"]').attr('checked', true);
					}
				});
			});
			
			function show_ajax_results(data, t, jqXHR) 
			{
				console.log(data);
				var server_li = $('.server_'+data.server.replace(/\./g, '_'))
				if(data.success)
				{
					$(server_li).find('.status').attr('class', 'status good').html(
						'Success: ' + (data.success ? 'yes!' : 'no :-(') + 
						'<br /'+'>Total run time: ' + data.runtime + ' seconds' +
						'<ol></ol>'
					);
				
					$(data.log).each(function(i, v) {
						console.log(v);
						$(server_li).find('ol').append(
							'<li>Command: ' + v.command + 
							'<br /'+'>Output: ' + v.output + 
							'<br /'+'>Stderr: ' + v.error + 
							'<br /'+'>Running time: ' + v.runtime + ' seconds</li>'
						);
					});
				} else {
					$(server_li).find('.status').attr('class', 'status bad').html(
						'Error: ' + data.error
					);
				}
			}
		</script>
		
		<style type="text/css">
			.status { 
				display: none; 
				padding: 3px;
			}
			
			.good {
				background-color: #a6ef7b;
				border: 1px solid #76c83f;
			}

			.neutral {
				background-color: #f7fdcb;
				border: 1px solid #c2d071;
			}

			.bad {
				background-color: #ef9398;
				border: 1px solid #dc5757;
			}
		</style>
	</head>
	<body>
		<h2>Servers</h2>
	
		<ul>
			<li><input type="checkbox" name="all" checked="checked"/>All</li>
			<?php foreach($config['servers'] as $server): ?>
				<li class="server_<?=preg_replace('/\./', '_', $server);?>">
					<input type="checkbox" name="<?=$server;?>" checked="checked" class="servers" /><?=$server;?>
					<div class="status neutral"></div>
				</li>
			<?php endforeach; ?>
		</ul>
		
		<form action="#" id="deploy_form" method="post">
			<input type="submit" name="submit_deploy" value="Deploy" />
		</form>
	</body>
</html>
