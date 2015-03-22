<?php

require_once '../config.php';

$return_names = array();
$term = filter_var($_GET['term'], FILTER_SANITIZE_STRING);
$query = $conn->prepare("SELECT DISTINCT `Name` FROM debits WHERE `Name` LIKE ? ORDER BY `Name`");
$query->execute(array('%' . $term . '%'));
$names = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($names as $k => $v) {
	$return_names[] = trim($v['Name']);
}

echo json_encode($return_names);
