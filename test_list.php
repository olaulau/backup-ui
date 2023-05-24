<?php
require __DIR__ . '/vendor/autoload.php';

use olafnorge\borgphp\ListCommand;

// list the contents of a repository or an archive 
$repo = '/home/laulau/test/repo1';
$listCommand = new ListCommand([
	$repo,
	"--short",
]);
$contents = $listCommand->mustRun()->getOutput();
var_dump($contents);
