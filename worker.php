<?php 

session_start();

$config = yaml_parse_file('config.yml');

if(!isset($config['servers']))
	die('Set some servers in the config first!');

header('Content-Type: application/json; charset=UTF-8');

$lockfile = __DIR__.'/locks/'.$_GET['server'].'-'.session_id().'.lock';
exec('ls '.__DIR__.'/locks/'.$_GET['server'].'-* | grep -v '.session_id().'', $ls);
if(strlen(trim(implode('', $ls))) > 0)
	die(json_encode(array('success' => false, 'server' => $_GET['server'], 'error' => 'Lock file in place')));

touch($lockfile);

$start_time = microtime(true);

$server_name = false;
foreach($config['servers'] as $k => $server)
	if($k == $_GET['server'])
		$server_name = $server;

if(!$server_name)
	die(json_encode(array('success' => false, 'server' => $_GET['server'], 'error' => 'Server name not found')));

$command_settings = $config['commands'][$_GET['task']];
if(!$command_settings)
	die(json_encode(array('success' => false, 'server' => $_GET['server'], 'error' => 'Command settings not found')));

$connection = ssh2_connect($server_name, 22, array('hostkey'=>'ssh-rsa'));
$auth = ssh2_auth_pubkey_file(
	$connection, 
	($command_settings['user'] ? $command_settings['user'] : $config['user']), 
	'id_rsa.pub', 
	'id_rsa'
);

$log = array();

if($auth)
{
	foreach($config['commands'][$_GET['task']]['cmds'] as $k => $command)
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
