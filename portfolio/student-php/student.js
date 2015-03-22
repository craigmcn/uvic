(function($){
	$('[data-toggle="tooltip"]').tooltip();

	if ($('#student_bid').data('bid') == undefined || $('#student_bid').data('bid') == '') {
		$('#student-search-modal').modal();
	}
	$('#student-search-modal').on('show.bs.modal', function(e){
		$('#student-search-submit').prop('disabled', false).html('<i class="uofs-icon uofs-icon-search"></i> Find');
	});
	
	$('#student-search-form').on('submit', function(e){
		e.preventDefault();
		var $button = $('#student-search-submit'),
			button_html = $button.html();
		$('#student-search-submit').prop('disabled', true).html('<i class="uofs-icon uofs-icon-time"></i> Searching...');
		$.ajax({
			type: 'POST',
			url: 'inc/fetch-student.php',
			data: $(this).serialize(),
			success: function(data) {
				$button.html(button_html).prop('disabled', false);
				if (data.count == 0) {
					$('#student-search-results').html(data.error);
				} else if (data.count > 1) {
					$('#student-search-results').html(data.html);
					$('#student-search-results .select-student').each(function(i, el){
						var $el = $(el);
						$el.on('click', function(evt){
							console.log('loading');
							$('#student-search-submit').prop('disabled', true).html('<i class="uofs-icon uofs-icon-time"></i> Loading...');
						});
					});
				} else {
					$('#student-search-modal').modal('hide');
					window.location	= '?bid='+data.bid+'&type='+data.type;
				}
			},
			dataType: 'json'
		});
	});
	
	$('.view-courses').each(function(i, el){
		var $el = $(el);
		$el.on('click', function(e){
			e.preventDefault();
			$('#view-courses-modal .modal-title').html('Academic Detail <span class="text-muted">'+$el.data('term-desc')+'</span><br><small>'+$('#student_name').data('name')+'</small>');
			$.ajax({
				type: 'POST',
				url: 'inc/fetch-academic-courses.php',
				data: { term : $el.data('term-code'), bid : $('#student_bid').data('bid') },
				success: function(data) {
					if (data.count == 0) {
						$('#view-courses-modal .modal-body').html(data.error);
					} else {
						$('#view-courses-modal .modal-body').html(data.html);
					}
				},
				dataType: 'json'
			});
		});
	});
		
	$('.view-note').each(function(i, el){
		var $el = $(el);
		$el.on('click', function(e){
			e.preventDefault();
			if ($el.data('id') != undefined && $el.data('id') != '') {
				$.ajax({
					type: 'POST',
					url: 'inc/fetch-note.php',
					data: { id : $el.data('id') },
					success: function(data) {
						if (data.count == 0) {
							if (!$('#student-note-error').length) {
								$('#student-note-modal .modal-body').prepend('<p id="student-note-error"></p>');		
							}
							$('#student-note-error').html(data.error);
						} else {
							var note_date = new Date(data.note.Note_Date);
							$('#note_id').val(data.note.Note_ID);
							$('#date_created').val(formatDate(note_date, 2)).prop('disabled', true);
							$('#note_description').val(data.note.Note_Description);
							$('#note_text').val(data.note.Note_Text);
							if (data.note.Private == 'Y') {
								$('#note_private').prop('checked', true);
							}
						}
					},
					dataType: 'json'
				});
			} else {		
				var note_date = new Date();
				$('#note_id').val('');
				$('#date_created').val(formatDate(note_date, 2));
				$('#note_description').val(formatDate(note_date, 1));
				$('#note_text').val('');
				$('#note_private').prop('checked', false);
			}
		});
	});

	$('#student-note-form').on('submit', function(e){
		e.preventDefault();
		var $button = $('#student-note-submit'),
			button_html = $button.html();
		$('#student-note-submit').prop('disabled', true).html('<i class="uofs-icon uofs-icon-time"></i> Saving...');
		$.ajax({
			type: 'POST',
			url: 'inc/store-note.php',
			data: $(this).serialize(),
			success: function(data) {
				//console.log(data);
				$button.html(button_html).prop('disabled', false);
				if (data.error != undefined && data.error != '') {
					if (!$('#student-note-error').length) {
						$('#student-note-modal .modal-body').prepend('<p id="student-note-error"></p>');		
					}
					$('#student-note-error').html(data.error);
				} else {
					$('#notes-table').html(data.html);
					$('#student-note-modal').modal('hide');
				}
			},
			dataType: 'json'
		});
	});
	
	
	function formatDate(value, type) {
		var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			weekdayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
			month = value.getMonth(),
			monthNumber = parseInt(month)+1,
			monthZero = (monthNumber < 10) ? '0'+monthNumber : monthNumber,
			monthName = monthNames[month],
			year = value.getFullYear(),
			day = value.getDate(),
			dayZero = (day < 10) ? '0'+day : day,
			weekday = value.getDay(),
			weekdayName = weekdayNames[weekday],
			hour = value.getHours(),
			hourZero = (hour < 10) ? '0'+hour : hour,
			hour12 = (hour > 12) ? parseInt(hour)-12 : hour,
			minute = value.getMinutes(),
			minuteZero = (minute < 10) ? '0'+minute : minute,
			meridiem = (hour < 12) ? 'am' : 'pm';
		if (type == 1) {
			return weekdayName + ', ' + monthName + ' ' + day + ', ' + year + ' (' + hour12 + ':' + minuteZero + ' ' + meridiem + ')';
		} else if (type == 2) {
			return year + '-' + monthZero + '-' + dayZero + ' ' + hour12 + ':' + minuteZero + ' ' + meridiem;
		} else if (type == 3) {
			return year + '-' + monthZero + '-' + dayZero + ' ' + hourZero + ':' + minuteZero;
		}
	}

})(jQuery);
