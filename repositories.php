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
	catch (Exception $ex)
	{
		$errors = $cmd->getErrorOutput();
		$error_message = $errors[0]["message"];
		if(str_starts_with($error_message, "Failed to create/acquire the lock "))
		{
			$output = "LOCK_FAIL";
		}
		else
		{
			echo "<pre>" . $ex->getMessage() . "</pre>";
			echo "<hr>";
			echo "<pre>"; var_dump($errors); echo "</pre>";
			die;
		}
	}
// 	var_dump($output); die;
	?>
	<tr>
		<td><?= $name ?></td>
		<?php
		if($output === "LOCK_FAIL")
		{
			?>
			<td>locked</td>
			<td>&nbsp;</td>
			<?php
		}
		else
		{
			?>
			<td><a href="./repository.php?location=<?= $output["repository"]["location"] ?>"><?= $output["repository"]["location"] ?></a></td>
			<td><?= ByteUnits\Binary::bytes($output["cache"]["stats"]["unique_csize"])->format("GiB", " ") ?></td>
			<?php
		}
		?>
	</tr>
	<?php
}
?>
</table>
