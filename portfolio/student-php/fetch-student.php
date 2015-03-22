<?php
require_once 'db.php';
if (!empty($_POST)) {
	foreach ($_POST as $k => $v) {
		$$k = filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	}
	$fetch = fetch_student($student, $student_search_type);
	$fetch_count = count($fetch);
	if (!empty($fetch['Error'])) {
		$return = array('count' => 0, 'error' => '<hr><p><i class="uofs-icon uofs-icon-alert text-warning"></i> ' . $fetch['Error'] . '</p>');
	} elseif ($fetch_count > 1) {
		$return['count'] = $fetch_count;
		$return['html'] = '<hr><div class="table-responsive" style="margin-top: 0;"><table class="table table-striped table-condensed"><thead><tr><th></th><th>Student #</th><th>Name</th><th>NSID</th><th>Birthdate</th></tr></thead><tbody>';
		foreach ($fetch as $k => $v) {
			$return['html'] .= '<tr><td><a class="select-student" href="student.php?bid=' . $v['Student_BID'] . '&type=' . $student_search_type . '">Select</a></td><td>' . $v['Student_BID'] . '</td><td>' . $v['Student_First_Name'] . ' ' . $v['Student_Last_Name'] . '</td><td>' . $v['Student_NSID'] . '</td><td>' . date('F j, Y', strtotime($v['Student_Birthdate'])) . '</td></tr>';
		}
		$return['html'] .= '</tbody></table></div>';
	} else {
		$return = array('count' => 1, 'bid' => $fetch[0]['Student_BID'], 'type' => $student_search_type);
	}
	echo json_encode($return);
}
