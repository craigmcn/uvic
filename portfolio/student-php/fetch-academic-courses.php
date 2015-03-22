<?php
require_once 'db.php';
if (!empty($_POST)) {
	foreach ($_POST as $k => $v) {
		$$k = filter_var($v, FILTER_SANITIZE_STRING);
	}
	$fetch = fetch_academic_courses($term, $bid);
	$fetch_count = count($fetch);
	if (!empty($fetch['Error'])) {
		$return = array('count' => 0, 'error' => '<p><i class="uofs-icon uofs-icon-alert text-warning"></i> ' . $fetch['Error'] . '</p>');
	} else {
		$return['count'] = $fetch_count;
		$return['html'] = '<div class="table-responsive" style="margin-top: 0;"><table class="table table-striped table-condensed"><thead><tr><th>CRN</th><th>Course</th><th>Status</th><th>Final Grade</th></tr></thead><tbody>';
		foreach ($fetch as $k => $v) {
			$return['html'] .= '<tr><td>' . $v['CRN'] . '</td><td>' . $v['Course'] . '</td><td>' . $v['Status'] . '</td><td>' . $v['Final_Grade'] . '</td></tr>';
		}
		$return['html'] .= '</tbody></table></div>';
	}
	echo json_encode($return);
}
