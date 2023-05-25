<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.inc.php';

use olafnorge\borgphp\InfoCommand;
?>

<h1> borg backups </h1>
<h2> repositories </h2>

<table>
<tr>
	<th>name</th>
	<th>location</th>
	<th>size</th>
</tr>

<?php
foreach ($conf["repos"] as $name => $location)
{
	$cmd = new InfoCommand([
		$location,
	]);
	$cmd->setEnv([
		"BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK" => "yes",
	]);
	try
	{
		$output = $cmd->mustRun()->getOutput();
	}
	catch (Exception $e)
	{
		echo "<pre>" . $e->getMessage() . "</pre>";
		echo "<hr>";
		$err = $cmd->getErrorOutput();
		echo "<pre>"; var_dump($err); echo "</pre>";
		die;
	}
// 	var_dump($output); die;
	?>
	<tr>
		<td><?= $name ?></td>
		<td><a href="./repository.php?location=<?= $output["repository"]["location"] ?>"><?= $output["repository"]["location"] ?></a></td>
		<td><?= $output["cache"]["stats"]["unique_csize"] ?></td>
	</tr>
	<?php
}
?>
</table>
