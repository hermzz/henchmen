<?php 

$config = yaml_parse_file('config.yml');

if(!isset($config['servers']))
	die('Set some servers in the config first!');

header('Content-Type: application/json; charset=UTF-8');

$lockfile = __DIR__.'/locks/lock.lock';
if(file_exists($lockfile))
	die(json_encode(array('success' => false, 'server' => $_GET['server'], 'error' => 'Lock file in place')));

touch($lockfile);

$start_time = microtime(true);

$connection = ssh2_connect($_GET['server'], 22, array('hostkey'=>'ssh-rsa'));
$auth = ssh2_auth_pubkey_file($connection, $config['user'], 'id_rsa.pub', 'id_rsa');

$log = array();

if($auth)
{
	foreach($config['commands'] as $k => $command)
	{
		$command_start = microtime(true);
		
		$stream = ssh2_exec($connection, $command);
		stream_set_blocking($stream, true);
		$output = stream_get_contents($stream);
		
		$err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		stream_set_blocking($err_stream, true);
		$stderr_output = stream_get_contents($err_stream);
		
		$log[] = array(
			'command' => $command,
			'output' => trim($output),
			'error' => trim($stderr_output),
			'runtime' => round(microtime(true) - $command_start, 3)
		);
		
		if(strlen($stderr_output) > 0)
			break;
	}
}

unlink($lockfile);

echo json_encode(
	array(
		'server' => $_GET['server'],
		'success' => (strlen($stderr_output) == 0),
		'auth' => $auth,
		'log' => $log,
		'runtime' => round(microtime(true) - $start_time, 3)
	)
);

?>
