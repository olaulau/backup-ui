<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.inc.php';

use olafnorge\borgphp\ListCommand;

$location = $_GET["location"];
$repo_name = array_search($location, $conf["repos"]);

// list repository's archives
$cmd = new ListCommand([
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
<h1> <?= $repo_name ?> </h1>
<h2> archives </h2>

<table>
<tr>
	<th>name</th>
	<th>start</th>
</tr>

<?php
foreach ($output["archives"] as $archive)
{
	?>
	<?php
	$dt = new DateTime($archive["start"]);
	$start =  $dt->format("d/m/Y H:i:s");
	?>
	<tr>
		<td><?= $archive["name"] ?></td>
		<td><?= $start ?></td>
	</tr>
	<?php
}
?>

<tr>
	<th>count</th>
	<th><?= count($output["archives"]) ?></th>
</tr>
</table>
