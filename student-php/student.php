<?php

/* Configuration */
require_once 'config.php';


/* Server-Side Scripts */
if (!empty($_GET['bid'])) {
	unset($_SESSION['asc']['student']);
	$student = fetch_student(filter_var(trim($_GET['bid']), FILTER_SANITIZE_NUMBER_INT), filter_var(trim($_GET['type']), FILTER_SANITIZE_STRING));
	if (empty($student['Error'])) {
		$term_code = (!empty($student[0]['Term_Code'])) ? $student[0]['Term_Code'] : null;
		$student[0]['Last_Contact_Date'] = (!empty($student[0]['Last_Contact_Date'])) ? $student[0]['Last_Contact_Date'] : date('Y-m-d H:i:s', time());
		$_SESSION['asc']['student'] = $student[0];
		$_SESSION['asc']['student']['banner'] = fetch_student_misc($student[0]['Student_BID'], $term_code);
		$_SESSION['asc']['student']['Student_Age'] = floor((time() - strtotime($_SESSION['asc']['student']['banner']['BIRTHDATE'])) / 31556926);
		$_SESSION['asc']['student']['academic'] = fetch_academic($student[0]['Student_BID']);
		$_SESSION['asc']['student']['ldap'] = get_ldap_person($student[0]['Student_NSID']);
		//$_SESSION['asc']['student']['notes'] = fetch_notes(null, $student[0]['Student_BID']);
	}
	header('Location: ' . $_SERVER['PHP_SELF'], true, 303);
	exit;
} elseif (!empty($_SESSION['asc']['student'])) {
	$student = $_SESSION['asc']['student'];
	foreach ($student as $k => $v) {
		${strtolower($k)} = $v;
	}
	$notes = fetch_notes(null, $student_bid);
	$student_name = $student_first_name . ' ' . $student_last_name;
	$student_nsid_text = (!empty($student_nsid)) ? ', ' . $student_nsid : '';
	$biographical_information = '<dl class="dl-horizontal">';
	$biographical_information .= '<dt>First name</dt><dd>' . $student_first_name . '</dd>';
	$biographical_information .= '<dt>Last name</dt><dd>' . $student_last_name . '</dd>';
	$biographical_information .= '<dt>Student number</dt><dd>' . $student_bid . '</dd>';
	$biographical_information .= '<dt>NSID</dt><dd>' . $student_nsid . '</dd>';
	$biographical_information .= '<dt>Birthdate</dt><dd>' . date('Y-m-d', strtotime($banner['BIRTHDATE'])) . '</dd>';
	$biographical_information .= '<dt>Age</dt><dd>' . $student_age . '</dd>';
	$biographical_information .= '<dt>Gender</dt><dd>' . $banner['GENDER'] . '</dd>';
	$biographical_information .= '<dt>Last Contact Date</dt><dd>' . $last_contact_date . '</dd>';
	$biographical_information .= '</dl>';
	if (empty($academic['Error'])) {
		$academic_information = '<div class="table-responsive" style="margin-top: 0;"><table class="table table-striped table-condensed"><thead><tr><th>Term</th><th>College</th><th>Program</th><th>Year</th><th></th></tr></thead><tbody>';
		foreach ($academic as $k => $v) {
			$academic_information .= '<tr><td>' . $v['Term_Code'] . '</td><td>' . $v['College_Code'] . '</td><td>' . $v['Program_Code'] . '</td><td>' . $v['Program_Year'] . '</td><td><a class="view-courses" href="#" data-term-code="' . $v['Term_Code'] . '" data-term-desc="' . $v['Term_Description'] . '" data-bid="' . $student_bid . '" data-toggle="modal" data-target="#view-courses-modal">Courses</a></td></tr>';
		}
		$academic_information .= '</tbody></table></div>';
	} else {
		$academic_information = '<p><i class="uofs-icon uofs-icon-alert text-warning"></i> ' . $academic['Error'] . '</p>';
	}
	$address_information = '<dl class="dl-horizontal">';
	$address_information .= '<dt>Address (line 1)</dt><dd>' . $banner['ADDRESS_LINE_1'] . '</dd>';
	$address_information .= '<dt>Address (line 2)</dt><dd>' . $banner['ADDRESS_LINE_2'] . '</dd>';
	$address_information .= '<dt>Address (line 3)</dt><dd>' . $banner['ADDRESS_LINE_3'] . '</dd>';
	$address_information .= '<dt>City</dt><dd>' . $banner['ADDRESS_CITY'] . '</dd>';
	$address_information .= '<dt>Province</dt><dd>' . $banner['ADDRESS_PROVINCE'] . '</dd>';
	$address_information .= '<dt>Postal code</dt><dd>' . $banner['ADDRESS_POSTAL_CODE'] . '</dd>';
	$address_information .= '<dt>Telephone</dt><dd>' . $banner['TELEPHONE_NUMBER'] . '</dd>';
	if (strpos($ldap, 'No results found') === false) {
		$address_information .= '<dt>Email (PAWS)</dt><dd><a href="mailto:' . $ldap['mail'] . '">' . $ldap['mail'] . '</a></dd>';
	} else {
		$address_information .= '<dt>Email (PAWS)</dt><dd>Unavailable</dd>';
	}
	if (!empty($banner['PREFERRED_EMAIL'])) {
		$address_information .= '<dt>Email (other)</dt><dd><a href="mailto:' . $banner['PREFERRED_EMAIL'] . '">' . $banner['PREFERRED_EMAIL'] . '</a></dd>';
	} else {
		$address_information .= '<dt>Email (other)</dt><dd>Unavailable</dd>';
	}
	
	$address_information .= '</dl>';
	if (empty($notes['Error'])) {
		$notes_table = '<div class="table-responsive" style="margin-top: 0;"><table class="table table-striped table-condensed"><thead><tr><th></th><th>Author</th><th nowrap>Note date</th><th>Text</th></tr></thead><tbody>';
		foreach ($notes as $k => $v) {
			if ($v['Private'] == 'Y' && $nsid != $v['Created_By']) {
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
			$notes_table .= '<tr><td>' . $view_link . '</td><td>' . $v['Created_By'] . '</td><td>' . date('Y-m-d', strtotime($v['Created_Date'])) . '</td><td>' . $note_text . '</td></tr>';
		}
		$notes_table .= '</tbody></table></div>';
	} else {
		$notes_table = '<p><br><i class="uofs-icon uofs-icon-alert text-warning"></i> This student has no notes</p>';
	}
} else {
	$student_name = 'No student selected';
	$student_bid = $student_nsid = $notes_table = '';
	$student_nsid_text = (!empty($student_nsid)) ? ', ' . $student_nsid : '';
	$biographical_information = $academic_information = $address_information = 'Not available';
}


/* Navigation */
$page_title = 'Students';
$page_title_hidden = true;


/* Client-Side Scripts */
$js[] = 'student.js';


/* Content */
include $_SERVER['DOCUMENT_ROOT'] . '/common/framework-header.php';
//echo '<pre>' . print_r($_SESSION['asc']['student'], true) . '</pre>';
echo '<div class="pull-right"><button class="btn btn-default btn-sm" data-toggle="modal" data-target="#student-search-modal"><i class="uofs-icon uofs-icon-search"></i> Student search</button></div>

<h3><span id="student_name" data-name="' . $student_name . '">' . $student_name . '</span> <small><span id="student_bid" data-bid="' . $student_bid . '">' . $student_bid . '</span>' . $student_nsid_text . '</small></h3>

<div class="row">
	<div class="col-md-5">
		<div class="panel panel-default">
			<div class="panel-heading">Biographical information</div>
			<div class="panel-body">
				' . $biographical_information . '
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">Academic information</div>
			<div class="panel-body">
				' . $academic_information . '
			</div>
		</div>	
	</div>
	<div class="col-md-7">
		<div class="panel panel-default">
			<div class="panel-heading">Address information</div>
			<div class="panel-body">
				' . $address_information . '
			</div>
		</div>
		<div class="panel panel-success">
			<div class="panel-heading">Notes</div>
			<div class="panel-body">
				<button class="btn btn-info btn-sm view-note" data-toggle="modal" data-target="#student-note-modal"><i class="uofs-icon uofs-icon-plus"></i> New note</button>
				<!--<button class="btn btn-default btn-sm" data-toggle="modal" data-target="#">Print all notes</button>-->
				<div id="notes-table">
					' . $notes_table . '
				</div>
			</div>
		</div>	
	</div>
</div>


<!-- Modals -->
<div class="modal fade" id="student-search-modal" tabindex="-1" role="dialog" aria-labelledby="student-search-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="student-search-label">Student Search</h4>
			</div>
			<div class="modal-body">
				<form id="student-search-form" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-sm-4">
							<div class="radio">
								<label data-toggle="tooltip" title="Find students who already have notes"><input type="radio" name="student_search_type" value="asc" checked> Quick search</label>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="radio">
								<label data-toggle="tooltip" title="Find students with or without notes"><input type="radio" name="student_search_type" value="new"> New person</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<label class="sr-only" for="student">Student #, NSID or Last name</label>
							<input id="student" class="form-control" name="student" type="text" maxlength="80" placeholder="Student #, NSID or Last name" required>
						</div>
					</div>
					<button id="student-search-submit" class="btn btn-primary" name="_student_search_submit" type="submit"><i class="uofs-icon uofs-icon-search"></i> Find</button>
				</form>
				<div id="student-search-results"></div>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="student-note-modal" tabindex="-1" role="dialog" aria-labelledby="student-note-label" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="student-note-label">Note for ' . $student_name . '</h4>
			</div>
			<div class="modal-body">
				<form id="student-note-form" class="form-horizontal" role="form">
					<input id="note_id" name="note_id" type="hidden" value="">
					<input id="student_bid" name="student_bid" type="hidden" value="' . $student_bid . '">
					<input id="created_by" name="created_by" type="hidden" value="' . $nsid . '">
					<div class="form-group">
						<label class="control-label col-sm-3">Created by</label>
						<div class="col-sm-9">
							<p class="form-control-static">' . $login_person['cn'] . '</p>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3" for="date_created">Date</label>
						<div class="col-sm-9">
							<input id="date_created" name="date_created" class="form-control" type="text" maxlength="40" placeholder="yyyy-mm-dd h:mm am/pm" required>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3" for="note_description">Description</label>
						<div class="col-sm-9">
							<input id="note_description" name="note_description" class="form-control" type="text" value="" maxlength="100" placeholder="Brief description" required>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-6" for="note_text" style="text-align: left;">Discussion notes</label>
						<div class="col-sm-12">
							<textarea id="note_text" name="note_text" class="form-control" rows="12" maxlength="2000" placeholder="Discussion notes" required></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12">
							<label><input id="note_private" name="note_private" type="checkbox" value="Y"> This note is private</label>
						</div>
					</div>
					<button id="student-note-submit" class="btn btn-primary" type="submit"><i class="uofs-icon uofs-icon-checkmark"></i> Save</button>
					<!--<button class="btn btn-default" name="_student_note_print" type="submit"><i class="uofs-icon uofs-icon-pdf"></i> Print</button>-->
					<button class="btn btn-link" type="button" data-dismiss="modal">Cancel</button>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="view-courses-modal" tabindex="-1" role="dialog" aria-labelledby="view-courses-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="view-courses-label">Academic Detail for STUDENT for TERM</h4>
			</div>
			<div class="modal-body"></div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->';

include $_SERVER['DOCUMENT_ROOT'] . '/common/framework-footer.php';
