<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.inc.php';

use olafnorge\borgphp\InfoCommand;
?>

<h1> borg backups </h1>
<h2> repositories </h2>

<table>
<tr>
	<th>id</th>
	<th>location</th>
	<th>size</th>
</tr>

<?php
foreach ($conf["repos"] as $repo)
{
	$cmd = new InfoCommand([
		$repo,
	]);
	$output = $cmd->mustRun()->getOutput();
// 	var_dump($output);
	?>
	<tr>
		<td><?= $output["repository"]["id"] ?></th>
		<td><a href="./repository.php?location=<?= $output["repository"]["location"] ?>"><?= $output["repository"]["location"] ?></a></td>
		<td><?= $output["cache"]["stats"]["unique_csize"] ?></td>
	</tr>
	<?php
}
?>
</table>
