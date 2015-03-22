<?php

require_once '../config.php';

$query = $conn->prepare("SELECT DISTINCT `Name` FROM debits ORDER BY `Name`");
$query->execute();
$debit_names = $query->fetchAll(PDO::FETCH_ASSOC);

$debit_id = $debit_name = $debit_amount = $debit_date = $debit_reference = '';
$action = 'Add';

// get list of categories
$debit_category_id = null;
$categories = $conn->query("SELECT `ID`, `Name` FROM `categories` WHERE `Effective_End_Date` > '" . date('Y-m-d') . "' ORDER BY `Name`", PDO::FETCH_ASSOC);

if (!empty($_POST['d'])) { // get debit information, if it exists and is requested
	$query = $conn->prepare("SELECT `ID`, `Name`, `Amount`, `Date`, `Reference`, `Category_ID` FROM `debits` WHERE `ID`=?");
	$query->execute(array(filter_var($_POST['d'], FILTER_SANITIZE_NUMBER_INT)));
	$d = $query->fetch(PDO::FETCH_ASSOC);
	//print_r($d); //DEBUG
	foreach ($d as $k => $v) {
		${'debit_' . strtolower($k)} = $v;
	}
	$action = 'Edit';
}


echo '<div id="error-message"></div>

<form id="add-edit-debit" action="' . $_SERVER['PHP_SELF'] . '" method="post">
	<input type="submit" style="left: -1000px; position: absolute;">
	<input type="hidden" name="debit_id" value="' . $debit_id . '">
	<input type="hidden" name="action" value="' . $action . '">

	<div class="form-group">
		<label for="debit_name">Name</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphglyphicon glyphicon-file"></i></span>
			<input id="debit_name" class="form-control" name="debit_name" type="text" value="' . $debit_name . '" maxlength="120" placeholder="Groceries" autofocus required>
		</div>
	</div>

	<div class="form-group">
		<label for="debit_amount">Amount</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
			<input id="debit_amount" class="form-control" name="debit_amount" type="number" value="' . $debit_amount . '" step="any" maxlength="7" placeholder="99.95" required>
		</div>
	</div>

	<div class="form-group">
		<label for="debit_date">Date</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			<input id="debit_date" class="form-control" type="date" name="debit_date" value="' . $debit_date . '" maxlength="15" placeholder="2013-07-31" required>
		</div>
	</div>

	<div class="form-group">
		<label for="debit_reference">Reference</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-flag"></i></span>
			<input id="debit_reference" class="form-control" type="text" name="debit_reference" maxlength="10" value="' . $debit_reference . '">
		</div>
	</div>

	<div class="form-group">
		<label for="debit_category_id">Category</label>
		<div class="input-group">
			<span class="input-group-addon"><i class="glyphicon glyphicon-tag"></i></span>
				<select id="debit_category_id" class="form-control" name="debit_category_id">' . PHP_EOL;

	foreach ($categories as $k => $v) {
		echo '		<option value="' . $v['ID'] . '"';
		echo ($v['ID'] == $debit_category_id) ? ' selected' : '';
		echo '>' . $v['Name'] . '</option>' . PHP_EOL;
	}

	echo '				</select>

		</div>
	</div>

</form>' . PHP_EOL;
