<?php

/* Login */
require_once 'cas_authentication.php';

// check staff access
$is_staff = (in_array('uofs_staff', get_groups_by_nsid($nsid))) ? true : false;


/* Server-Side Scripts */
// Set up db connections
include_once '../inc/db.php';
include_once '../inc/functions.php';

// check administrator access
$admin = fetch_infosheet_administrator($nsid);
$is_admin = (isset($admin['NSID']));
$admin['Name'] = ($is_admin) ? get_name_from_nsid($nsid) : null;

// initialize variables
$crn = $subject = $course_number = $section = $cross_list_group = $enterer_phone = $enterer_email = $building_id = $room = $day_night_ind = $night_weekday =  $takehome_exam = $common_paper_indicator = $common_paper_yes = $common_paper_no = $cmpt_selected = $sci_selected = $aud_selected = $mus_selected = $cmpt_building_id = $sci_building_id = $aud_building_id = $cmpt_building_room = $sci_building_room = $aud_building_room = $mus_building_room = $none_selected = $music_room_options = $electronics_ind = $electronics_yes = $electronics_no = $electronics_building_id = $electronics_building_room = $conflict_dates = $religious_reasons = $comments = '';
$common_subject = $common_course = $common_section = $religious_day = array();
$num_common = $num_religious = 1;
$show = true;
$error = array();
$error_init = '';
	
$schedule = getSchedule($nsid);
//print_r($schedule); //DEBUG
if (isset($_GET['admin']) && $is_admin) { // only implement admin if 'admin' has been added to the querystring
	$schedule['preliminary_pending'] = false;
	$schedule['preliminary_posted'] = false;
	$schedule['final_pending'] = false;
	$schedule['final_posted'] = false;
}

//get page data
if (isset($_REQUEST['crn'])) {
	$crn = filter_var(trim($_REQUEST['crn']), FILTER_SANITIZE_NUMBER_INT);
} else {
	$error_init = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt="">You must select a CRN from the list of classes.</p>' . PHP_EOL;
	$show = false;
}

//get class data
$class_db_info = fetch_class($crn);
//print_r($class_db_info); //DEBUG

if (!empty($class_db_info['Error'])) {
	$error_init = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=" ">' . $class_db_info['Error'] . '</p>
		<p class="fine-print">If you feel this message is an error, please email <a href="mailto:exams@usask.ca">exams@usask.ca</a></p>' . PHP_EOL;
	$show = false;
} elseif ($schedule['preliminary_pending'] || $schedule['preliminary_posted'] || $schedule['final_pending']) {
	$error_init = '<p><img class="icon" src="/img/icons/error.png" alt=" ">The date to enter Information Sheets has passed. Please call Debi Bokshowan at 306&#8209;966&#8209;6726 for assistance.</p>';
	$show = false;
} elseif (time() < $schedule['form_open_date'] && !isset($_GET['admin'])) {
	$error_init = '<p class="notice"><img class="icon" src="/img/icons/information.png" alt=" ">Exam information sheets will be available ' . date('l, F j, Y', $schedule['form_open_date']) . '.</p>';
	$show = false;
} elseif (isset($_POST['_submit'])) {
	//echo '<pre>$_POST is ' . print_r($_POST, true) . '</pre>'; //DEBUG
	foreach ($_POST as $k => $v) {
		if (is_array($v)) {
			$sanitized_array = array();
			foreach ($v as $kv => $vv) {
				$sanitized_array[] = filter_var($vv, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			}
			$$k = $sanitized_array;
		} else {
			$$k = filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		}
	}
	$num_common = count($common_subject);
	$num_religious = count($religious_day);
	
	/* BEGIN: error checking */
	if (empty($enterer_phone) || empty($enterer_email)) {
		$error['information'] = 'Phone number and email address are required';	
	}
	if (empty($takehome_exam)) {
		$error['takehome_exam'] = 'You must indicate whether this is a take-home exam';
	}
	if ($takehome_exam == 'N') { // check for take-home exam
		if (empty($common_paper_indicator)) {
			$error['common_exams'] = 'You must indicate whether this exam is a common exam with any other';
		} elseif (isset($common_paper_indicator) && $common_paper_indicator == 'Y' && empty($common_subject[0])) {
			$error['common_exams'] = 'You have indicated that this exam is a common exam with another, but not listed any common exams';
		} elseif (isset($common_paper_indicator) && $common_paper_indicator == 'N' && !empty($common_subject[0])) {
			$error['common_exams'] = 'You have listed common exams, but indicated that this exam is not a common exam with any other';
		}
		
		if (empty($special_room_ind)) {
			$error['special_room'] = 'You must indicate if you have a specific room requirement';	
		} elseif ($special_room_ind != 'Z') {
			switch ($special_room_ind) {
				case 'C':
					$special_room_building_id = $cmpt_building_id;
					$special_room_building_room = $cmpt_building_room;
				break;
			
				case 'S':
					$special_room_building_id = $sci_building_id;
					$special_room_building_room = $sci_building_room;
				break;

				case 'A':
					$special_room_building_id = $aud_building_id;
					$special_room_building_room = $aud_building_room;
				break;
			
				case 'M':
					$special_room_building_id = 'EDUC';
					$special_room_building_room = $mus_building_room;
				break;
			}
			// A/V or Oral exam rooms are optional (change requested Nov. 4, 2008)
			if ((empty($special_room_building_id) || empty($special_room_building_room)) && ($special_room_ind != 'A')) {
				$error['special_room'] = 'You must indicate the specific room you would like';
			}
		} elseif (!empty($cmpt_building_id) || !empty($sci_building_id) || !empty($aud_building_id) || !empty($mus_building_room)) {
			if (!empty($cmpt_building_id)) {
				$special_room_ind = 'C';
				$special_room_building_id = $cmpt_building_id;
				$special_room_building_room = $cmpt_building_room;
			} elseif (!empty($sci_building_id)) {
				$special_room_ind = 'S';
				$special_room_building_id = $sci_building_id;
				$special_room_building_room = $sci_building_room;
			} elseif (!empty($aud_building_id)) {
				$special_room_ind = 'A';
				$special_room_building_id = $aud_building_id;
				$special_room_building_room = $aud_building_room;
			} elseif (!empty($mus_building_room)) {
				$special_room_ind = 'M';
				$special_room_building_id = 'EDUC';
				$special_room_building_room = $mus_building_room;
			}
		}

		if (empty($electronics_ind)) {
			$error['electronics_ind'] = 'You must indicate whether this exam will require the use of electronic devices';
		}
		
		if (!empty($religious_day) && $religious_day[0] != '' && empty($religious_reasons)) {
			$error['religious_reasons'] = 'You must provide a reason for the religious conflicts';
		}
		if (!empty($religious_reasons) && empty($religious_day)) {
			$error['religious_day'] .= 'You must indicate on which days you have religious conflicts';
		}
		if (!empty($religious_reasons) && strlen($religious_reasons) > 200) {
			$error['religious_reasons'] = 'Your religious reasons can not be longer than 200 characters';
		}
		if (!empty($comments) && strlen($comments) > 2000) {
			$error['comments'] = 'Your comments can not be longer than 2000 characters';
		}

		/* Check Common Papers */
		$common_papers = array();
		for ($i = 0; $i <= ($num_common-1); $i++) {
			if (!empty($common_subject[$i])) {
				$common_papers[$i]['Subj'] = $common_subject[$i];
				$common_papers[$i]['CNum'] = $common_course[$i];
				$common_papers[$i]['Sect'] = $common_section[$i];
			}
		}
		
		//print_r($common_papers); //DEBUG
		if (count($common_papers) > 0) {
			$check = check_common_papers($common_papers);
			if (!is_null($check)) {
				$error['common_exams'] = $check;
			}
		}
	} elseif ($takehome_exam == 'Y') { // it is a take-home exam		
		$electronics_ind = $electronics_building_id = $electronics_building_room = $religious_day = $religious_reasons = $comments = $special_room_building_id = $special_room_building_room = null;
		$common_paper_indicator = 'N';
		$special_room_ind = 'Z';
	}
	/* END: error checking*/
	
	if (empty($error)) { // no errors, save form
	
		$results = store_class($crn, $enterer_phone, $enterer_email, $religious_reasons, $comments, $common_paper_indicator, $special_room_ind, $special_room_building_id, $special_room_building_room, $electronics_ind, $electronics_building_id, $electronics_building_room, $takehome_exam);
		//print_r($results); //DEBUG
		
		if ($results['Success'] == $crn) { // stored successfully
			// handle common papers
			if ($common_paper_indicator == 'Y') {
				$common_papers = array();
				for ($i = 0; $i <= ($num_common-1); $i++) {
					if (!empty($common_subject[$i])) {
						$common_papers[] = fetch_crn($nsid, $common_subject[$i], $common_course[$i], $common_section[$i]);
					}
				}
				$current_common_papers = fetch_common_papers($crn);
				$current_cps = array();
				foreach ($current_common_papers as $ccp) {
					$current_cps[] = $ccp['CRN'];
				}
				$delete_cps = array_diff($current_cps, $common_papers);
				foreach ($delete_cps as $dcp) {
					$delete = delete_common_paper($dcp);
				}
				foreach ($common_papers as $cp) {
					$crosslisted_classes = fetch_crosslisted_classes($cp);
					if (!array_key_exists('Error', $crosslisted_classes)) {
						foreach ($crosslisted_classes as $cc) {
							$common_papers[] = $cc['CRN'];
						}
					}
				}
				sort($common_papers);
				$results_common_papers = store_common_papers($crn, array_unique($common_papers));
				//print_r($results_common_papers); //DEBUG
			} else {
				// delete this CRN from Common_Papers
				$results_common_papers = delete_common_paper($nsid, $crn);				
			}
			
			if (array_key_exists('Successful', $results_common_papers)) {
				
				if ($num_religious > 0) {
					$religious_dates = array();
					foreach ($religious_day as $k => $v) {
						if (!empty($v)) {
							$religious_dates[$k]['religious_day'] = $v;
						}
					}
					$results_religious_dates = store_religious_dates($crn, $religious_dates);
					//print_r($results_religious_dates); //DEBUG
				}
				
				if (!array_key_exists('Successful', $results_religious_dates) && $num_religious > 0) {
					$error['religious_conflicts'] = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=""><strong>Error</strong> ' . $results_religious_dates['Error'] . ' (' . __LINE__ . ')</p>';
				}
				
			} else {
				$error['common_exams'] = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=""><strong>Error</strong> ' . $results_common_papers['Error'] . ' (' . __LINE__ . ')</p>';
			}
			
		} else {
			$error['general'] = true;
			$error_init = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=""><strong>Error</strong> ' . $results['Error'] . ' (' . __LINE__ . ')</p>';
		}
		
		if (!empty($error)) {
			$error_init = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=" ">Your form has not been saved.</strong> Please note the errors indicated below.</p>';
			foreach ($error as $k => $v) {
				${'error_' . $k} = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt="">' . $v . '</p>';
			}
			//echo '<pre>' . print_r($error, true) . '</pre>';	 //DEBUG
		} else {
			// update PAWS Channel cache
			$serverport = ($_SERVER['SERVER_PORT'] > 2000) ? ':' . $_SERVER['SERVER_PORT'] : '';
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, 'https://sesdchannels.usask.ca' . $serverport . '/academics/exams/cache-classes.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			curl_exec($ch); 
			curl_close($ch);

			$admin_qs = (isset($_GET['admin'])) ? '&admin' : '';
			header('Location: ?success=true&crn=' . $crn . $admin_qs, true, 303);
			exit;
		}
			
	} else { // something went wrong, display error message
		$error_init = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt=" ">Your form has not been saved.</strong> Please fix the errors indicated below.</p>';
		foreach ($error as $k => $v) {
			${'error_' . $k} = '<p class="error"><img class="icon" src="/img/icons/exclamation.png" alt="">' . $v . '</p>';
		}
		//echo '<pre>' . print_r($error, true) . '</pre>';	 //DEBUG
	}
	
// end: save form

} elseif (isset($_GET['success'])) {
	$class_db_info = fetch_class($crn);
	if (empty($class_db_info['Error'])) {
		foreach ($class_db_info as $k => $v) {
			${strtolower($k)} = $v;
		}
	}
	$error_init = '<p class="success"><strong><img class="icon" src="/img/icons/tick.png" alt=" ">Your form has been successfully saved and submitted.</strong>';
	if ($takehome_exam == 'Y') {
		$error_init .= ' You have indicated a take-home exam, therefore no date or room will be assigned.';
	}
	$error_init .= ' You may come back and <a href="/exams/information-sheets/' . $crn . '/">update this information</a> until the deadline.</p>
		<p><a href="/exams/information-sheets/">Return to class list</a></p>';
	$show = false;
} else { //display the page (not saving form data)

	//var_dump($class_db_info); //DEBUG
	foreach ($class_db_info as $k => $v) {
		${strtolower($k)} = $v;
	}
	
}

if ($show) {
		
	/* Create heading section of form */
	$crosslist = fetch_crosslist($cross_list_group);
	$temp_section = (strlen($section) >= 3 && !is_int(substr($section, 2, 1))) ?
		substr($section, 0, 2) : $section;
	$class_name = $subject . ' ' . $course_number . ' ' . $temp_section;
	$enterer_email = (empty($enterer_email)) ? get_mail_from_nsid($nsid) : $enterer_email;
	
	/* Create Instructor Information */
	$instructors = fetch_instructors($crn);
	//print_r($instructors); //DEBUG
	
	/* Build Take-home Exam Section */	
	if (isset($takehome_exam)) {
		if ($takehome_exam == 'Y') {
			$takehome_exam_yes = ' checked';
		} elseif ($takehome_exam == 'N') {
			$takehome_exam_no = ' checked';
		}
	}

	/* Common Exams */
	$subject_codes = fetch_subj_codes();
	if (!empty($common_paper_indicator)) {
		if ($common_paper_indicator == 'Y') {
			$common_papers_yes = ' checked';
			if (empty($common_subject)) {
				$common_papers = fetch_common_papers($crn);
				$num_common = count($common_papers);
				//var_dump($common_papers, $num_common); //DEBUG
				if (empty($common_papers['Error'])) {
					foreach ($common_papers as $k => $v) {
						$common_subject[] = $v['Subject'];
						$common_course[] = $v['Course_Number'];
						$common_section[] = $v['Section'];
					}
				}
			} else {
				$num_common = count($common_subject);
			}
		} elseif ($common_paper_indicator == 'N') {
			$common_papers_no = ' checked';
		}
	}
	
	/* Specific Rooms */	
	$buildings = fetch_buildings();
	$music_rooms = array('1004', '1033', '1036', '1038');

	switch ($special_room_ind) {
		case 'C':
			$cmpt_selected = ' checked';
			$cmpt_building_id = $special_room_building_id;
			$cmpt_building_room = $special_room_building_room;
		break;
		
		case 'S':
			$sci_selected_id = ' checked';
			$sci_building_id = $special_room_building_id;
			$sci_building_room = $special_room_building_room;
		break;
		
		case 'A':
			$aud_selected = ' checked';
			$aud_building_id = $special_room_building_id;
			$aud_building_room = $special_room_building_room;
		break;
		
		case 'M':
			$mus_selected = ' checked';
			$mus_building_room = $special_room_building_room;
		break;
		
		default:
			$none_selected = ' checked';
		break;
	}
	
	if (!empty($electronics_ind)) {
		if ($electronics_ind == 'Y') {
			$electronics_yes = ' checked';
		} elseif ($electronics_ind == 'N') {
			$electronics_no = ' checked';
		}
	}

	if (empty($religious_day)) {
		$religious_dates = fetch_religious_dates($crn);
		$num_religious = count($religious_dates);
		if (empty($religious_dates['Error'])) {
			foreach ($religious_dates as $religious_date) {
				$religious_day[] = $religious_date['Religious_Day'];
			}
		}
	}

}

/* END Server-Side Scripts */


/* Navigation */
$site_name = 'Exam Information Sheets';
$header_url = '/exams/information-sheets/';

// default context-nav


/* Styles */
// page-specific styles
$css[] = '/common/stylesheets/forms.css';


/* Client-side scripts */
// page-specific scripts (included in common footer)
$js[] = '/common/javascript/jquery.placeholder.min.js';
$js[] = 'standard.js';


/* Page content */
// include the header file
include_once $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

// Content ...
if ($is_admin) {
	echo '<p class="notice">You are logged in as <strong>' . $admin['Name'] . '</strong> <span class="dimmed">(' . $admin['NSID'] . ')</span> with administrator access.</p>' . PHP_EOL;
}

echo $error_init . PHP_EOL;
//echo '<pre>$error is ' . print_r($error, true) . '</pre>'; //DEBUG

if ($show) {
	echo '<p class="dimmed">Deadline for submission is <strong>' . $schedule['submission_deadline'] , '.</strong></p>
		<form name="infosheet" action="' . $_SERVER['PHP_SELF'] . (isset($_GET['admin']) ? '?admin' : '') . '" method="post">
		
			<h2>Class: <strong>' . $class_name . '</strong> &#160; CRN: <strong>' . $crn . '</strong></h2>
			<input type="hidden" name="crn" value="' . $crn . '">
			<input type="hidden" name="subject" value="' . $subject . '">
			<input type="hidden" name="course_number" value="' . $course_number . '">
			<input type="hidden" name="section" value="' . $section . '">' . PHP_EOL . PHP_EOL;

	if (!empty($cross_list_group)) {
		echo '			<p style="margin-bottom:0.5em;"><strong>Cross-listed classes:</strong></p>
			<ul class="bulleted">' . PHP_EOL;
		foreach ($crosslist as $xl) {
			echo '				<li>' . $xl['Subject'] . ' ' . $xl['Course_Number'] . ' ' . $xl['Section'] . ' (' . $xl['CRN'] . ')</li>' . PHP_EOL;
		}
		echo '			</ul>';
	}
	
	echo '
		<fieldset class="clearit">
			<legend>Your Information</legend>
			' . $error_information . '
			<ol>
				<li><label for="enterer_phone">Phone <span class="required">*</span> </label><input id="enterer_phone" type="text" name="enterer_phone" value="' . $enterer_phone . '" required></li>
				<li><label for="enterer_email">Email <span class="required">*</span> </label><input id="enterer_email" class="wide" type="email" name="enterer_email" value="' . $enterer_email . '" required></li>
			</ol>
		</fieldset>
		
		<fieldset>
			<legend>Instructors</legend>' . PHP_EOL;
	if (!array_key_exists('Error', $instructors)) {
		echo '			<ul class="bulleted">' . PHP_EOL;
		foreach ($instructors as $instructor) {
			echo '				<li>' . $instructor['Firstname'] . ' ' . $instructor['Surname'] . ' (' . $instructor['NSID'] . ')';
			if ($instructor['Primary_Ind'] == 'Y') {
				echo ' <span class="dimmed">Primary</span>';	
			}
			echo '</li>' . PHP_EOL;
		}
		echo '			</ul>';
	} else {
		echo '			<p><img class="icon" src="/img/icons/exclamation.png" alt="">There are currently no instructors assigned to this class.</p>';
	}

	echo '
		</fieldset>

		<fieldset>
			<legend>General Class Information</legend>
			<p><strong>Classroom Taught In</strong>: ' . $building_id . ' ' . $room . '</p>
			<input type="hidden" name="building_id" value="' . $building_id . '">
			<input type="hidden" name="room" value="' . $room . '">
			<input type="hidden" name="day_night_ind" value="' . $day_night_ind . '">
			<input type="hidden" name="night_weekday" value="' . $night_weekday . '">
			<p>';
	echo ($day_night_ind == 'D') ? 'This is a <strong>Day</strong> class' : 'This is a <strong>Night</strong> class taught on ' . $night_weekday;
	echo '. (A night class is any class that has a start time of 5:30 p.m. or later.)</p>
			<p class="dimmed">If the above information is incorrect, please contact your department to have it corrected or errors may occur during exam scheduling.</p>
		</fieldset>
	
		<fieldset>
			<legend>Take-home Exam</legend>
				' . $error_takehome_exam . '
			<p><strong>A take-home exam will not be assigned a date or room.</strong> If this is a take-home exam, please indicate this below and then press Submit at the bottom of this form. Answers to all the other questions will not be required.</p>
			<p>Is this a take-home exam? <span class="required">*</span></p>
			<div class="inset input-group">
				<label><input id="takehome_exam_yes" type="radio" name="takehome_exam" value="Y"' . $takehome_exam_yes . '> Yes</label><br>
				<label><input id="takehome_exam_no" type="radio" name="takehome_exam" value="N"' . $takehome_exam_no . '> No</label>
			</div>
			<p id="take_home_alert" class="alert"';
	echo (empty($takehome_exam) || $takehome_exam == 'N') ? ' style="display:none;"' : '';
	echo '><img class="icon" src="/img/icons/error.png" alt="">By indicating a take-home exam, no date or room will be assigned.</p>
		</fieldset>
		
		<div id="take_home"';
	if (!empty($takehome_exam) && $takehome_exam == 'Y') {
		echo ' style="display:none;"';
	}
	echo '>
	
			<fieldset>
				<legend>Common Exams</legend>
				' . $error_common_exams . '
				<p>A common exam is when two or more classes/sections are writing the exact same exam at the same time and in the same room if enrolment allows it. Common exams are not allowed between day and night classes unless the Exams office approves it.</p>
				<p>Please list every class/section that need to be scheduled at the same time.  It is important to note the exact section number for each section needing a common exam (e.g. POLS 111 03 and POLS 111 05 or, if crosslisted, MATH 101 02A, MATH 101 04A, etc.). <strong>Do not</strong> enter invalid section numbers (e.g. &quot;all&quot;) as they will be rejected.</p>
				<p>Is this exam a common exam with any other? <span class="required">*</span></p>
				<div class="inset input-group">
					<label><input type="radio" name="common_paper_indicator" value="Y"' . $common_papers_yes . '> Yes</label><br>
					<label><input type="radio" name="common_paper_indicator" value="N"' . $common_papers_no . '> No</label>
				</div>
				<p>Please list any exams that need to be scheduled at the same time as this exam:</p>
				<div id="classes" class="indent">' . PHP_EOL;

	for ($i = 0; $i <= ($num_common-1); $i++) {
		echo '<p id="class_' . ($i+1) . '">Subject <select name="common_subject[]">
			<option value="">Select</option>' . PHP_EOL;
		foreach ($subject_codes as $k => $v) {
			echo '<option value="' . $v['Subject'] . '"';
			echo ($v['Subject'] == $common_subject[$i]) ? ' selected' : '';
			echo '>' . str_replace('&', '&amp;', $v['Subject']) . '</option>' . PHP_EOL;
		}
		echo '</select>
			 &#160; Course Num <input name="common_course[]" type="text" size="4" maxlength="3" value="' . $common_course[$i] . '">
			 &#160; Section <input name="common_section[]" type="text" size="4" maxlength="3" value="' . $common_section[$i] . '">
			<button class="delete_class" data-id="' . ($i+1) . '" type="button">Remove</button></p>' . PHP_EOL;
	}

	echo '				</div><!-- close #classes -->
				<p><button id="add_class" type="button">Add another class</button></p>
			</fieldset>
			
			<fieldset>
				<legend>Specific Room Requirements</legend>
				' . $error_special_room . '
				<p><strong>Note</strong>: Exams are scheduled as much as is possible into rooms with tables.</p>
				<p>Do you require a specific room for this exam? <span class="required">*</span> <span class="fine-print">(e.g. computer lab, science lab, audio/visual or oral requirement)</span></p>
				<ol>
					<li><label><input type="radio" name="special_room_ind" value="C" ' . $cmpt_selected . '> Computer Lab</label></li>
					<li class="indent">Building <select name="cmpt_building_id">
						<option value="">Select building</option>' . PHP_EOL;
	foreach ($buildings as $k => $v) {
		$select = ($v['Building_Code'] == $cmpt_building_id) ? ' selected' : '';
		echo '						<option value="' . $v['Building_Code'] . '"' . $select . '>' . str_replace('&', '&amp;', $v['Building_Name']) . '</option>' . PHP_EOL;
	}
	echo '						</select>
						&#160; Room <input type="text" name="cmpt_building_room" size="5" value="' . $cmpt_building_room . '"></li>
					<li><label><input type="radio" name="special_room_ind" value="S"' . $sci_selected . '> Science Lab</label></li>
					<li class="indent">Building <select name="sci_building_id">
						<option value="">Select building</option>' . PHP_EOL;
	foreach ($buildings as $k => $v) {
		$select = ($v['Building_Code'] == $sci_building_id) ? ' selected' : '';
		echo '						<option value="' . $v['Building_Code'] . '"' . $select . '>' . str_replace('&', '&amp;', $v['Building_Name']) . '</option>' . PHP_EOL;
	}
	echo '						</select>
						&#160; Room <input type="text" name="sci_building_room" size="5" value="' . $sci_building_room . '"></li>
					<li><label><input type="radio" name="special_room_ind" value="A"' . $aud_selected . '> Audio/Visual or Oral</label></li>
					<li class="indent">Building <span class="dimmed">(optional)</span> <select name="aud_building_id">
						<option value="">Select building</option>' . PHP_EOL;
	foreach ($buildings as $k => $v) {
		$select = ($v['Building_Code'] == $aud_building_id) ? ' selected' : '';
		echo '						<option value="' . $v['Building_Code'] . '"' . $select . '>' . str_replace('&', '&amp;', $v['Building_Name']) . '</option>' . PHP_EOL;
	}
	echo '						</select>
						&#160; Room <span class="dimmed">(optional)</span> <input type="text" name="aud_building_room" size="5" value="' . $aud_building_room . '"></li>
					<li><label><input type="radio" name="special_room_ind" value="M"' . $mus_selected . '> Music Room</label></li>
					<li class="indent">Building <strong>Education</strong>
					&#160; Room <select name="mus_building_room">
						<option value="">Select room</option>' . PHP_EOL;
	foreach ($music_rooms as $music_room) {
		$select = ($mus_building_room == $music_room) ? ' selected' : '';
		echo '						<option value="' . $music_room . '"' . $select . '>' . $music_room . '</option>' . PHP_EOL;
	}
	echo '						</select></li>
					<li><label><input type="radio" name="special_room_ind" value="Z"' . $none_selected . '> None</label></li>
				</ol>
				
				<fieldset>
					<legend>Electronic devices</legend>
					' . $error_electronics_ind . '
					<ol>
						<li><label class="wide">Will this exam require the use of electronic devices (e.g., e-books, tablets, laptops)?</label>
							<div class="input-group indent">
								<label><input id="electronics_ind_yes" name="electronics_ind" type="radio" value="Y"' . $electronics_yes . '> Yes</label><br>
								<label><input id="electronics_ind_no" name="electronics_ind" type="radio" value="N"' . $electronics_no . '> No</label>
							</div>
						</li>
						<li class="indent">Building <span class="dimmed">(optional)</span> <select id="electronics_building_id" name="electronics_building_id">
							<option value="">Select building</option>' . PHP_EOL;
	foreach ($buildings as $k => $v) {
		echo '								<option value="' . $v['Building_Code'] . '"';
		echo ($v['Building_Code'] == $electronics_building_id) ? ' selected' : '';
		echo '>' . str_replace('&', '&amp;', $v['Building_Name']) . '</option>' . PHP_EOL;
	}
	echo '							</select>
							&#160; Room <span class="dimmed">(optional)</span> <input id="electronics_building_room" name="electronics_building_room" type="text" size="5" value="' . $electronics_building_room . '">
						</li>
					</ol>
					
					<p class="dimmed"><strong>Note:</strong> These exams will not be scheduled in a gym due to disruption of other students.</p>
					
				</fieldset>
				
			</fieldset>	
			<fieldset>
				<legend>Conferences</legend>
				<p>The University Council Academic Courses Policy states that final examinations may be scheduled at any time during examination periods; until the schedule has been finalized and posted, students and instructors should avoid making travel or other commitments for these periods.</p>
				<p>Due to the increasing number of final examinations and the circumscribed examination period in which we schedule them, <strong>we can no longer accommodate requests for scheduling exceptions for conferences, research trips, and similar absences for university business</strong>. If your final examination is scheduled for a time during which you anticipate being away from the University, you will need to arrange for invigilation according to the guidelines of your college.</p>
				<p>If submission of your final grades will be delayed due to your travel or other commitments, the Academic Courses Policy stipulates that the Registrar and the students in your course be notified regarding the anticipated date of submission.</p>
			</fieldset>
	
			<fieldset>
				<legend>Religious Conflicts</legend>
				' . $error_religious_conflicts . '
				<p>Are there any dates on which we <strong>cannot</strong> schedule your exam due to religious conflicts?</p>
				<div id="conflicts">' . PHP_EOL;

	for ($i = 0; $i <= ($num_religious-1); $i++) {
		echo '<p id="conflict_' . ($i+1) . '" class="indent">Date <span>' . ($i+1) . '</span>:
			' . $schedule['exam_start_month'] . ' <select name="religious_day[]">
				<option value="">Day</option>' . PHP_EOL;
		for ($j = $schedule['exam_start_day']; $j <= $schedule['exam_end_day']; $j++) {
			echo '				<option value="' . $j . '"';
			echo ($j == $religious_day[$i]) ? ' selected' : '';
			echo '>' . $j . '</option>' . PHP_EOL;
		}
		echo '</select>, ' . $schedule['exam_start_year'] . '
			<button class="delete_conflict" data-id="' . ($i+1) . '" type="button">Remove</button></p>' . PHP_EOL;
	}

	echo '				</div><!-- close #conflicts -->
				<p><button id="add_conflict" type="button">Add another religious exemption date</button></p>
				' . $error_religious_reasons . '
				<p>Reasons for Religious Exemptions: <span class="fine-print">(Maximum 200 Characters)</span></p>
				<textarea name="religious_reasons">' . $religious_reasons . '</textarea>
			</fieldset>
			
			<fieldset>
				<legend>General Comments</legend>
				' . $error_comments . '
				<p class="fine-print">(Maximum 2000 Characters)</p>
				<textarea name="comments">' . $comments . '</textarea>
			</fieldset>
		</div><!-- close #take_home -->
		<p class="dimmed"><strong>Note</strong>: University policy requires exams to be scheduled into rooms with seating capacity double the enrolment for the class so that students do not sit beside each other (e.g., a class with enrolment of 30 will be scheduled into a room with seating for 60).</p>
		<p class="dimmed">It may not be possible to accommodate every special request.</strong></p>
		<p><button class="button" type="submit" name="_submit"><img class="icon" src="/img/icons/page_go.png" alt="">Submit</button> <a class="button-link" href="' . $header_url . '">Cancel</a></p>
	</form>' . PHP_EOL;
}

// End of Content ... include common footer
include_once $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php';
