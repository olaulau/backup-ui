<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.inc.php';

use olafnorge\borgphp\ListCommand;
?>

<h1> repo infos </h1>
<h2> archives </h2>

<?php
// list the contents of a repository or an archive 
$repo = $_GET["location"];
$listCommand = new ListCommand([
	$repo,
]);
$output = $listCommand->mustRun()->getOutput();
// var_dump($output);

?>
<table>
<tr>
	<th>id</th>
	<th>name</th>
	<th>start</th>
</tr>

<?php
foreach ($output["archives"] as $archive)
{
	?>
	<tr>
		<td><?= $archive["id"] ?></td>
		<td><?= $archive["name"] ?></td>
		<td><?= $archive["start"] ?></td>
	</tr>
	<?php
}
?>
</table>
