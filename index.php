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
						$.ajax(
							{
								url: 'http://deploy.skynet/worker.php?server='+$(e).attr('name'),
								dataType: 'json',
								success: show_ajax_results
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
				var server_li = $('.server_'+data.server.replace(/\./g, '_'))
				$(server_li).append(
					'<br /'+'>Success: ' + (data.success ? 'yes!' : 'no :-(') + 
					'<br /'+'>Total run time: ' + data.runtime + ' seconds' +
					'<ol></ol>'
				);
				
				console.log(data.log);
				$(data.log).each(function(i, v) {
					console.log(v);
					$(server_li).find('ol').append(
						'<li>Command: ' + v.command + 
						'<br /'+'>Output: ' + v.output + 
						'<br /'+'>Stderr: ' + v.error + 
						'<br /'+'>Running time: ' + v.runtime + ' seconds</li>'
					);
				});
			}
		</script>
	</head>
	<body>
		<h2>Servers</h2>
	
		<ul>
			<li><input type="checkbox" name="all" checked="checked"/>All</li>
			<?php foreach($config['servers'] as $server): ?>
				<li class="server_<?=preg_replace('/\./', '_', $server);?>"><input type="checkbox" name="<?=$server;?>" checked="checked" class="servers" /><?=$server;?></li>
			<?php endforeach; ?>
		</ul>
		
		<form action="#" id="deploy_form" method="post">
			<input type="submit" name="submit_deploy" value="Deploy" />
		</form>
	</body>
</html>
