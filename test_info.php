<?php
require __DIR__ . '/vendor/autoload.php';

use olafnorge\borgphp\InfoCommand;

// 
$repo = '/home/laulau/test/repo1';
$cmd = new InfoCommand([
	$repo,
// 	'-a *',
]);
$contents = $cmd->mustRun()->getOutput();
var_dump($contents);
