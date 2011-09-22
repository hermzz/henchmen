<?php

$config = yaml_parse_file('config.yml');

if(!isset($config['servers']))
	die('Set some servers in the config first!');
?>
<html>
	<head>
		<title>Deploy</title>
		<script src="jquery.js"></script>
	</head>
	<body>
		<h2>Servers</h2>

		<ul>
			<?php foreach($config['servers'] as $server): ?>
				<li><?=$server;?></li>
			<?php endforeach; ?>
		</ul>

		<form action="#" method="post">
			<input type="submit" name="submitDeploy" value="Deploy" />
		</form>
	</body>
</html>
