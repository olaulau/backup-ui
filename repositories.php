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
	<th>size</th>
</tr>

<?php
$error_string = "";
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
			$output = "UNKNOWN_ERROR";
			$error_string .= "<hr>";
			$error_string .= "<hr>";
			$error_string .= "<pre>" . $ex->getMessage() . "</pre>";
			$error_string .= "<hr>";
			$error_string .= "<pre>" . var_export($errors, true) . "</pre>";
		}
	}
// 	var_dump($output); die;
	?>
	<tr>
		<td><a href="./repository.php?location=<?= $location ?>"><?= $name ?></a></td>
		<?php
		if(!is_array($output))
		{
			$display = "";
			if($output === "LOCK_FAIL")
			{
				$display = "locked";
			}
			else 
			{
				$display = "error";
			}
			?>
			<td><?= $display ?></td>
			<?php
		}
		else
		{
			?>
			<td><?= ByteUnits\Binary::bytes($output["cache"]["stats"]["unique_csize"])->format("GiB", " ") ?></td>
			<?php
		}
		?>
	</tr>
	<?php
}
?>
</table>

<?= $error_string ?>
