<?php 

header('Content-Type: application/json; charset=UTF-8');

$sleep = rand(1,3);

sleep($sleep);

?>
{"server": "<?=$_GET['server'];?>", "sleep": <?=$sleep;?>}
