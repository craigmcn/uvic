(function($) { // process the following when the DOM is ready

	if ($.isFunction($.fn.placeholder)) {
		$('input, textarea').placeholder();
	}

	$('#takehome_exam_yes').on('click', function(e){
		$('#take_home').slideUp('fast', function(){
			$('#take_home_alert').show();
		});
	});

	$('#takehome_exam_no').on('click', function(e){
		$('#take_home_alert').hide(function(){
			$('#take_home').slideDown('fast');
		});
	});
	
	/* Add class for Common Exams */
	$('#add_class').on('click', function(e){
		e.preventDefault();
		var p = $('#class_1').clone(true),
			num = $('#classes').children('p').length+1; // increment the number of classes
		p.attr('id', 'class_'+num);
		p.children('select').children('option:selected').attr('selected', false); // deselect value
		p.children('input').val(''); // set course number value and name
		p.children('button').attr('data-id', num); // set delete value
		p.appendTo('#classes').hide().slideDown('fast'); // create new class selection and show it by sliding down
	});
	
	/* Delete class for Common Exams */
	$('button.delete_class').on('click', function(e){
		e.preventDefault();
		var num = $('#classes').children('p').length,
			j = id = $(this).data('id');
		if (num == 1) { 
			var p = $('#class_1');
			p.children('select').children('option:selected').attr('selected', false); // deselect value
			p.children('input').val('');
		} else {
			// delete the selected class
			$('#class_'+id).slideUp('fast', function (){
				$('#class_'+id).remove();
			}); // slide class up, then remove
			//loop from id to num_classes to renumber the remaining classes
			for (i=id+1; i<num+1; i++) {
				$('#class_'+i+' button').attr('data-id', j); // set delete value
				$('#class_'+i).attr('id','class_'+j); // set class
				j++;
			}
		}
	});

	/* Add date conflict for Religious Conflicts */
	$('#add_conflict').on('click', function(e){
		e.preventDefault();
		var p = $('#conflict_1').clone(true), // copy the first date selection
			num = $('#conflicts').children('p').length+1; // increment the number of classes
		p.attr('id', 'conflict_'+num);
		p.children('span').html(num); // set number
		p.children('select').children('option:selected').attr('selected', false); // deselect value
		p.children('button').attr('data-id', num); // set delete
		p.appendTo('#conflicts').hide().slideDown('fast'); // show new date
	});

	/* Delete date conflict for Religious Conflicts */
	$('button.delete_conflict').on('click', function(e){
		e.preventDefault();
		var num = $('#conflicts').children('p').length,
			j = id = $(this).data('id');
		if (num == 1) {
			var p = $('#conflict_1');
			p.children('select').children('option:selected').attr('selected', false); // deselect value
		} else {
			// delete the selected class
			$('#conflict_'+id).slideUp('fast', function (){ // slide date up, then remove
				$('#conflict_'+id).remove();
			});
			
			//loop to renumber the remaining dates
			for (i=id+1; i<num+1; i++) {
				$('#conflict_'+i+' span').html(j); // set number
				$('#conflict_'+i+' button').attr('data-id', j); // set delete
				$('#conflict_'+i).attr('id','conflict_'+j); // set class
				j++;
			}
		}
	});

	/* Enable/disable "Electronic devices" room selection */
	$('#electronics_building_id, #electronics_building_room').prop('disabled', true);
	$('#electronics_ind_yes').on('click', function(e){
		$('#electronics_building_id, #electronics_building_room').prop('disabled', false);
	});
	$('#electronics_ind_no').on('click', function(e){
		$('#electronics_building_id, #electronics_building_room').prop('disabled', true);
	});
	
	/* Set Specific Room indicator when room selected */
	$('select[name=cmpt_building_id]').on('blur', function(e){
		if ($(this).val() != '' && $(this).val() != undefined) {
			$('input[name=special_room_ind][value=C]').prop('checked', true);
		}
	});
	$('select[name=sci_building_id]').on('blur', function(e){
		if ($(this).val() != '' && $(this).val() != undefined) {
			$('input[name=special_room_ind][value=S]').prop('checked', true);
		}
	});
	$('select[name=aud_building_id]').on('blur', function(e){
		if ($(this).val() != '' && $(this).val() != undefined) {
			$('input[name=special_room_ind][value=A]').prop('checked', true);
		}
	});
	$('select[name=mus_building_room]').on('blur', function(e){
		if ($(this).val() != '' && $(this).val() != undefined) {
			$('input[name=special_room_ind][value=M]').prop('checked', true);
		}
	});

})(jQuery);
