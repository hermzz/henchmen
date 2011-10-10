<?php

session_start();

$config = yaml_parse_file('config.yml');

if(!isset($config['servers']))
	die('Set some servers in the config first!');
?>
<html>
	<head>
		<title>Henchmen</title>
		<script src="jquery.js"></script>
		<script>
			applies = [
				<?php foreach($config['commands'] as $command): ?>
					'<?=$command['servers']?>', // <?=$command['name'];?>
					
				<?php endforeach; ?>
				
				// leave blank space on purpose
			];
			
			$(document).ready(function() 
			{
				$('#deploy_form').submit(function() 
				{
					apps = applies[$('select[name="task"]').val()].split(',');
					$.each(apps, function(i, e) {
						if($('input[name="'+e+'"]')[0].checked)
						{
							$('.server_'+e)
								.find('.status')
								.html('<img src="spinner.gif" /'+'> Reticulating splines...')
								.attr('class', 'status neutral')
								.show();
						
							$.ajax(
								{
									url: 'worker.php',
									data: {server: e, task: $('select[name="task"]').val()},
									type: 'GET',
									dataType: 'json',
									success: show_ajax_results,
									error: function(jqXHR, text, error) {
										$('.server_'+e).find('.status').attr('class', 'status bad').html(
											'Error parsing worker response!'
										);
									}
								}
							);
						}
					});
					
					return false;
				});
				
				$('input[name="all"]').click(function(e) {
					$('input[type="checkbox"]').attr('checked', (e.target.checked ? true : false));
				});
				
				$('select[name="task"]').change(function(e) {
					if($(e.target).val())
					{
						$('#serverlist li').hide();
						$('#serverlist .status').hide();
						$('#serverlist .all').show();
						
						apps = applies[$(e.target).val()].split(',');
						$.each(apps, function(k,v) {
							$('.server_'+v).show().attr('class', 'server_'+v+' '+(k%2 ? 'even' : 'odd'));
						});
					}
				});
			});
			
			function show_ajax_results(data, t, jqXHR) 
			{
				var server_li = $('.server_'+data.server)
				if(data.success)
				{
					$(server_li).find('.status').attr('class', 'status good').show().html(
						'Success: ' + (data.success ? 'yes!' : 'no :-(') + 
						'<br /'+'>Total run time: ' + data.runtime + ' seconds' +
						'<ol></ol>'
					);
				
					$(data.log).each(function(i, v) {
						$(server_li).find('ol').append(
							'<li>Command: ' + v.command + 
							'<br /'+'>Output: <pre>' + v.output + '</pre>' +
							'<br /'+'>Stderr: ' + v.error + 
							'<br /'+'>Running time: ' + v.runtime + ' seconds</li>'
						);
					});
				} else {
					$(server_li).find('.status').attr('class', 'status bad').html(
						'Error: ' + (data.error ? data.error: data.log[data.log.length-1].error)
					);
				}
			}
		</script>
		
		<link rel="stylesheet" href="index.css" />
	</head>
	<body>
		<div id="content">
			<h1>Henchmen</h1>
			<p id="tagline">at your service</p>
	
			<ul id="serverlist">
				<li class="all"><input type="checkbox" name="all" checked="checked"/>All</li>
				<?php $i=0; ?>
				<?php foreach($config['servers'] as $k => $server): ?>
					<li class="server_<?=preg_replace('/\./', '_', $k);?> <?=($i%2==0 ? 'odd' : 'even');?>">
						<input type="checkbox" name="<?=$k;?>" checked="checked" class="servers" /><?=$k;?>
						<div class="status neutral"></div>
					</li>
					<?php $i++; ?>
				<?php endforeach; ?>
			</ul>
		
			<form action="#" id="deploy_form" method="post">
				<select name="task">
					<option value="">Select a task</option>
					<option value="">-------------</option>
					<?php foreach($config['commands'] as $k => $command): ?>
						<option value="<?=$k;?>"><?=$command['name'];?></option>
					<?php endforeach; ?>
				</select>
				<input type="submit" name="submit_deploy" value="Deploy" />
			</form>
		</div>
	</body>
</html>
