<?php
require_once 'db.php';
$note_id = $store_student = $student_bid = $created_by = $date_created = $note_description = $note_text = $note_private = '';
if (!empty($_POST)) {
	foreach ($_POST as $k => $v) {
		$$k = filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	}
	if (empty($note_id)) {
		$store_student = store_student($student_bid, $created_by);
	}
	if (!empty($store_student['Error'])) {
		$return = array('count' => 0, 'error' => '<p class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><i class="uofs-icon uofs-icon-error"></i> ' . $store_student['Error'] . '</p>');
	} else {
		$note_date = (strtotime($date_created)) ? date('Y-m-d h:i', strtotime($date_created)) : date('Y-m-d h:i', time());
		$private = (empty($note_private)) ? 'N' : $note_private;
		$store_note = store_note($student_bid, $note_date, $note_description, $note_text, $private, $created_by, $note_id);
		if (!empty($store_note['Error'])) {
			$return = array('count' => 0, 'error' => '<p class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><i class="uofs-icon uofs-icon-error"></i> ' . $store_note['Error'] . '</p>');
		} else {
			$fetch = fetch_notes(null, $student_bid);
			if (!empty($fetch['Error'])) {
				$return = array('count' => 1, 'html' => '<p><i class="uofs-icon uofs-icon-error text-danger"></i> ' . $fetch['Error'] . '</p>');
			} else {
				$return['html'] = '<div class="table-responsive" style="margin-top: 0;"><table class="table table-striped table-condensed"><thead><tr><th></th><th>Author</th><th nowrap>Note date</th><th>Text</th></tr></thead><tbody>';
				foreach ($fetch as $k => $v) {
					if ($v['Private'] == 'Y' && $created_by != $v['Created_By']) {
						$note_text = '<span class="label label-warning">Private</span>';
						$view_link = '';
					} else {
						$note_text = ($v['Private'] == 'Y') ? '<span class="label label-default">Private</span> ' : '';
						if (strlen($v['Note_Text']) > 80) {
							$note_text .= substr($v['Note_Text'], 0, 79) . '&#8230;';					
						} else {
							$note_text .= $v['Note_Text'];
						}
						$view_link = '<a class="view-note" href="#" data-toggle="modal" data-target="#student-note-modal" data-id="' . $v['Note_ID'] . '">View</a>';
					}
					$return['html'] .= '<tr><td>' . $view_link . '</td><td>' . $v['Created_By'] . '</td><td>' . date('Y-m-d', strtotime($v['Created_Date'])) . '</td><td>' . $note_text . '</td></tr>';
				}
				$return['html'] .= '</tbody></table></div>';
			}
		}
	}
	echo json_encode($return);
}
