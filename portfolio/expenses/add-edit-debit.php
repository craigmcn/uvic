<?php

require_once '../config.php';

foreach ($_POST as $k => $v) {
	$$k = filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

if (!empty($debit_name) && !empty($debit_amount) && !empty($debit_date)) { // save/update debit

	if (!empty($debit_id)) {
		// update debit
		try {
			$query = $conn->prepare("UPDATE `debits` SET `Name`= ?, `Amount`= ?, `Date`= ?, `Reference`= ?, `Category_ID`= ? WHERE `ID`= ?");
			$query->execute(array($debit_name, $debit_amount, date('Y-m-d', strtotime($debit_date)), $debit_reference, $debit_category_id, $debit_id));
		} catch(PDOException $e) {
			echo json_encode(array('status'=>'Error', 'message'=>$e->getMessage()));
			exit;
		}
		$d = $conn->errorInfo();
		$process = 'updated';
	} else {
		// save debit
		try {
			$query = $conn->prepare("INSERT INTO `debits` ( `Name`, `Amount`, `Date`, `Reference`, `Category_ID` ) VALUES ( ?, ?, ?, ?, ? )");
			$query->execute(array($debit_name, $debit_amount, date('Y-m-d', strtotime($debit_date)), $debit_reference, $debit_category_id));
		} catch(PDOException $e) {
			echo json_encode(array('status'=>'Error', 'message'=>$e->getMessage()));
			exit;
		}
		$d = $conn->errorInfo();
		$debit_id = $conn->lastInsertId();
		$process = 'saved';
	}

	if (!is_null($d[1])) { // Update error

		echo json_encode(array('status'=>'Error', 'message'=>$d[2]));
		exit;

	} else {

		echo json_encode(array('status'=>'OK', 'message'=>$process, 'id'=>$debit_id));
		exit;

	}

} else {

	echo json_encode(array('status'=>'Error', 'message'=>'Name, Amount and Date are required'));
	exit;

}
