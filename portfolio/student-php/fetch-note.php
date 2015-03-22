<?php
require_once 'db.php';
if (!empty($_POST)) {
	$fetch = fetch_notes(filter_var(trim($_POST['id']), FILTER_SANITIZE_NUMBER_INT));
	if (!empty($fetch['Error'])) {
		$return = array('count' => 0, 'error' => '<i class="uofs-icon uofs-icon-alert text-warning"></i> ' . $fetch['Error']);
	} else {
		$return = array('count' => 1, 'note' => $fetch);
	}
	echo json_encode($return);
}
