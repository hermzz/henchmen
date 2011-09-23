<?php 

header('Content-Type: application/json; charset=UTF-8');

$connection = ssh2_connect($_GET['server'], 22, array('hostkey'=>'ssh-rsa'));
$auth = ssh2_auth_pubkey_file($connection, 'www-data', 'id_rsa.pub', 'id_rsa');

?>
{"server": "<?=$_GET['server'];?>", "auth": "<?=$auth ? 'true' : 'false';?>"}
