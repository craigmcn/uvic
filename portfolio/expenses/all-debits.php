<?php

require_once 'config.php';

$datatables = true;
$filter = '';

// list all debits for the previous and current month (and future months, if available)
if (!empty($_GET['name'])) { // by name
	$sql_select = "SELECT d.ID, d.Name AS Debit_Name, d.Amount, d.Date, d.Reference, c.Name AS Category_Name FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND d.Name=? ORDER BY d.Date DESC";
	$sql_count = "SELECT COUNT(*) FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND d.Name=?";
	$parameters = array($_GET['name']);
	$filter = '<p class="text-info"><small><span class="glyphicon glyphicon-info-sign"></span> Displaying debits at ' . $_GET['name'] . '</small></p>' . PHP_EOL;
} else if (!empty($_GET['category'])) { // by category
	$sql_select = "SELECT d.ID, d.Name AS Debit_Name, d.Amount, d.Date, d.Reference, c.Name AS Category_Name FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND c.Name=? ORDER BY d.Date DESC, d.Name";
	$sql_count = "SELECT COUNT(*) FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND c.Name=?";
	$parameters = array($_GET['category']);
	$filter = '<p class="text-info"><small><span class="glyphicon glyphicon-info-sign"></span> Displaying <em>' . $_GET['category'] . '</em> debits</small></p>' . PHP_EOL;
} else { // all, for the previous and current month (and future months, if available)
	$sql_select = "SELECT d.ID, d.Name AS Debit_Name, d.Amount, d.Date, d.Reference, c.Name AS Category_Name FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID ORDER BY d.Date DESC, d.Name";
	$sql_count = "SELECT COUNT(*) FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID";
	$parameters = null;
}

$error = false;
try {
	$select = $conn->prepare($sql_select);
	$select->execute($parameters);
	$result = $select->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	$error = $e->getMessage();
}

try {
	$count = $conn->prepare($sql_count);
	$count->execute($parameters);
	$num = $count->fetch(PDO::FETCH_NUM);
} catch (PDOException $e) {
	$error = $e->getMessage();
}


include_once SITE_ROOT . '/inc/header.php';


echo '<h3>Debits</h3>

' . $filter . PHP_EOL;

if ($error) {

	echo '<p class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Error: ' . $error . '</p>' . PHP_EOL;

} else {

	if ($num[0] > 0) {

		echo '<div class="table-responsive">
	<table id="datatable" class="table table-striped" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<th></th>
				<th>Date</th>
				<th>Name</th>
				<th>Amount</th>
				<th>Reference</th>
				<th>Category</th>
			</tr>
		</thead>
		<tbody>' . PHP_EOL;

		foreach ($result as $k => $v) {
			echo '		<tr>
				<td style="width: 1em;"><a class="debit" href="debit.php?d=' . $v['ID'] . '" data-id="' . $v['ID'] . '" data-target="#modal-actions" data-toggle="modal"><span class="glyphicon glyphicon-pencil"></span></a></td>
				<td>' . date('Y M d', strtotime($v['Date'])) . '</td>
				<td>' . PHP_EOL;
			echo (empty($_GET['name'])) ? '<a href="all-debits.php?name=' . urlencode($v['Debit_Name']) . '">' . $v['Debit_Name'] . '</a>' : $v['Debit_Name'];
			echo '</td>
				<td style="text-align:right;">$' . $v['Amount'] . '</td>
				<td>' . $v['Reference'] . '</td>
				<td>' . PHP_EOL;
			echo (empty($_GET['category'])) ? '<a href="all-debits.php?category=' . urlencode($v['Category_Name']) . '">' . $v['Category_Name'] . '</a>' : $v['Category_Name'];
			echo '</td>
			</tr>' . PHP_EOL;
		}

		echo '		</tbody>
	</table>
</div>' . PHP_EOL;

	} else {

		echo '<p class="text-warning"><span class="glyphicon glyphicon-warning-sign"></span> No debits</p>' . PHP_EOL;

	}

}


include_once SITE_ROOT . '/inc/footer.php';
