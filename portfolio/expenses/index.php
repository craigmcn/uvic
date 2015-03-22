<?php

require_once 'config.php';


$id = $type = $action = $message = '';
if (isset($_GET)) {
	foreach ($_GET as $k => $v) {
		$$k = filter_var(trim($v), FILTER_SANITIZE_STRING);
	}

	if ($id == 'c1' && $type == 'cash') {
		$query = $conn->prepare("SELECT `Amount`, `Date` FROM cash WHERE Cash_ID= ?");
		$query->execute(array('1'));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$message = 'Cash updated to $' . $result['Amount'] . ' as of ' . date('Y M j', strtotime($result['Date']));
	} elseif ($type == 'debit') {
		if ($action == 'deleted') {
			$result = $_SESSION[$type][$id];
		} else {
			$query = $conn->prepare("SELECT `Name`, `Amount`, `Date`, `Reference` FROM debits WHERE ID= ?");
			$query->execute(array($id));
			$result = $query->fetch(PDO::FETCH_ASSOC);
		}
		$reference = (!empty($result['Reference'])) ? ' (' . $result['Reference'] . ')' : '';
		$message = 'Debit at ' . $result['Name'] . ' for $' . $result['Amount'] . $reference . ' on ' . date('Y M j', strtotime($result['Date'])) . ' was ' . $action . ' successfully';
	} elseif ($type == 'credit') {
		if ($action == 'deleted') {
			$result = $_SESSION[$type][$id];
		} else {
			$query = $conn->prepare("SELECT p.`Name`, c.`Amount`, c.`Date`, c.`Reference` FROM credits c INNER JOIN payors p ON p.`ID` = c.`Payor_ID` WHERE c.ID= ?");
			$query->execute(array($id));
			$result = $query->fetch(PDO::FETCH_ASSOC);
		}
		$reference = (!empty($result['Reference'])) ? ' (' . $result['Reference'] . ')' : '';
		$message = 'Credit from ' . $result['Name'] . ' for $' . $result['Amount'] . $reference . ' on ' . date('Y M j', strtotime($result['Date'])) . ' was ' . $action . ' successfully';
	}
	$message = (!empty($message)) ? '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="glyphicon glyphicon-saved"></i> ' . $message . '</div>' . PHP_EOL : '';
}

// list current balance
$sth = $conn->prepare("SELECT (c.Total_Credits - d.Total_Debits - ca.Total_Cash) AS Balance
FROM (SELECT 1 AS CID, SUM(`Amount`) AS Total_Credits
FROM `credits`) c
INNER JOIN (SELECT 1 AS DID, SUM(`Amount`) AS Total_Debits
FROM `debits`) d ON d.DID = c.CID
INNER JOIN (SELECT 1 AS CASH, `Amount` AS Total_Cash
FROM `cash`) ca ON ca.CASH= c.CID");
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);
$balance = $result['Balance'];

$sth = $conn->prepare("SELECT `Amount`, `Date` FROM cash");
$sth->execute();
$result = $sth->fetch(PDO::FETCH_ASSOC);


include_once SITE_ROOT . '/inc/header.php';


echo '<ul class="nav nav-pills" style="margin-bottom: 20px;">
	<li><a href="#debits">Recent Debits</a></li>
	<li><a href="#credits">Recent Credits</a></li>
</ul>

' . $message . '

<div class="alert alert-info">
	Current balance: <strong>$' . $balance . '</strong><br>
	Cash: <strong>$' . $result['Amount'] . '</strong> <small>(' . date('Y M j', strtotime($result['Date'])) . ')</small>
</div>' . PHP_EOL . PHP_EOL;


// list all debits for the previous and current month (and future months, if available)
$sql = "SELECT d.ID, d.Name AS Debit_Name, d.Amount, d.Date, d.Reference, c.Name AS Category_Name FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND d.Date BETWEEN '" . date('Y-m-d', $display_date) . "' AND '9999-12-31' ORDER BY d.Date DESC";

echo '<h3 id="debits">Debits since ' . date('F j, Y', $display_date) . '</h3>' . PHP_EOL;

if ($result = $conn->query("SELECT COUNT(*) FROM `debits` d, `categories` c WHERE c.ID=d.Category_ID AND d.Date BETWEEN '" . date('Y-m-d', $display_date) . "' AND '9999-12-31'", PDO::FETCH_ASSOC)) {

	if ($result->fetchColumn() > 0) {

		echo '<table class="table table-striped" cellpadding="0" cellspacing="0" border="0">
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

		$result = $conn->query($sql, PDO::FETCH_ASSOC);
		foreach ($result as $k => $v) {
			echo '		<tr>
			<td style="width: 1em;"><a class="debit" href="' . SITE_WWW . '/inc/debit.php?d=' . $v['ID'] . '" data-id="' . $v['ID'] . '"  data-target="#modal-actions" data-toggle="modal"><span class="glyphicon glyphicon-pencil"></span></a></td>
			<td>' . date('Y M j', strtotime($v['Date'])) . '</td>
			<td><a href="all-debits.php?name=' . urlencode($v['Debit_Name']) . '">' . $v['Debit_Name'] . '</a></td>
			<td style="text-align:right;">$' . $v['Amount'] . '</td>
			<td>' . $v['Reference'] . '</td>
			<td><a href="all-debits.php?category=' . urlencode($v['Category_Name']) . '">' . $v['Category_Name'] . '</a></td>
		</tr>' . PHP_EOL;
		}

		echo '	</tbody>
</table>' . PHP_EOL . PHP_EOL;

	} else {

		echo '<p>No debits</p>' . PHP_EOL;

	}

} else {

	$error_info = $result->errorInfo();
	echo '<p class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Error: ' . $error_info[2] . '</p>' . PHP_EOL;

}


// list all credits for the previous and current month (and future months, if available)
$sql = "SELECT c.ID, p.Name, c.Amount, c.Date, c.Reference FROM `credits` c, `payors` p WHERE p.ID=c.Payor_ID AND c.Date BETWEEN '" . date('Y-m-d', $display_date) . "' AND '9999-12-31' ORDER BY c.Date DESC";

echo '<h3 id="credits">Credits since ' . date('F j, Y', $display_date) . '</h3>' . PHP_EOL;

if ($result = $conn->query("SELECT COUNT(*) FROM `credits` c, `payors` p WHERE p.ID=c.Payor_ID AND c.Date BETWEEN '" . date('Y-m-d', $display_date) . "' AND '9999-12-31'", PDO::FETCH_ASSOC)) {

	if ($result->fetchColumn() > 0) {

		echo '<table class="table table-striped" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th></th>
			<th>Date</th>
			<th>Name</th>
			<th>Amount</th>
			<th>Reference</th>
		</tr>
	</thead>
	<tbody>' . PHP_EOL;

		$result = $conn->query($sql, PDO::FETCH_ASSOC);
		foreach ($result as $k => $v) {
			echo '		<tr>
			<td style="width: 1em;"><a class="credit" href="' . SITE_WWW . '/inc/credit.php?c=' . $v['ID'] . '" data-id="' . $v['ID'] . '" data-target="#modal-actions" data-toggle="modal"><span class="glyphicon glyphicon-pencil"></span></a></td>
			<td>' . date('Y M j', strtotime($v['Date'])) . '</td>
			<td><a href="all-credits.php?name=' . urlencode($v['Name']) . '">' . $v['Name'] . '</a></td>
			<td style="text-align:right;">$' . $v['Amount'] . '</td>
			<td>' . $v['Reference'] . '</td>
		</tr>' . PHP_EOL;
		}

		echo '	</tbody>
</table>';

	} else {

		echo '<p>No credits</p>' . PHP_EOL;

	}

} else {

	$error_info = $result->errorInfo();
	echo '<p class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> Error: ' . $error_info[2] . '</p>' . PHP_EOL;

}


include_once SITE_ROOT . '/inc/footer.php';
