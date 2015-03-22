/* Author: Craig McNaughton */
(function($) {

	var ROOT = '/expenses2';

	if ($.isFunction($.fn.placeholder)) {
		$('input, textarea').placeholder();
	}

	$('a.debit').each(function(i, el){
		$(el).attr('href', $(el).data('target'))
			.on('click', function(e){
			e.preventDefault();
			var id = $(this).data('id');
			$('#modal-actions .modal-footer').empty(); // reset modal footer content
			$('#modal-actions .modal-body').load('./inc/debit.php', { 'd' : id });
			if (id == undefined) {
				$('#modal-actions .modal-title').text('Add debit');
				$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button>');
			} else {
				$('#modal-actions .modal-title').text('Update debit');
				$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button> &#160; <button id="delete" name="_delete" class="btn btn-default" type="submit" value="delete"><i class="glyphicon glyphicon-trash"></i> Delete</button>');
			}
					console.log($('#add-edit-debit'));
			$('#add-edit-debit').on('submit', function(ev){
					console.log($(this));
				//ev.preventDefault();
				return false;
				/*var $save = $('#save');
				if ($('#delete').length) {
					$('#delete').attr('disabled', 'disabled');
				}
				if (!$('#error-message').length) {
					$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
				}
				$save.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh glyphicon-white"></i> Saving&#8230;');
				$.post(ROOT+'/actions/add-edit-debit.php', $(this).serialize(), function(data){
					console.log(data);
					if (data.status == 'OK') {
						//window.location.href = ROOT+'/?id='+data.id+'&type=debit&action='+data.message;
					} else {
						$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
						$save.removeAttr('disabled').html('<i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save');
						if ($('#delete').length) {
							$('#delete').removeAttr('disabled');
						}
					}
				}, 'json');*/
			});
			if ($('#delete').length) {
				$('#delete').on('click', function(ev){
					ev.preventDefault();
					var $this = $(this);
					$('#save').attr('disabled', 'disabled');
					if (!$('#error-message').length) {
						$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
					}
					$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh"></i> Deleting&#8230;');
					$.post(ROOT+'/actions/delete-debit.php', $('#add-edit-debit').serialize(), function(data){
						if (data.status == 'OK') {
							window.location.href = ROOT+'/?id='+data.id+'&type=debit&action='+data.message;
						} else {
							$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
							$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-retweet glyphicon-white"></i> Update');
							$('#save').removeAttr('disabled');
						}
					}, 'json');
				});
			}
		});
	});

	$('a.credit').each(function(i, el){
		$(el).attr('href', $(el).data('target'))
			.on('click', function(e){
			e.preventDefault();
			var id = $(this).data('id');
			$('#modal-actions .modal-footer').empty(); // reset modal footer content
			$('#modal-actions .modal-body').load('./inc/credit.php', { 'c' : id });
			if (id == undefined) {
				$('#modal-actions .modal-title').text('Add credit');
				$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button>');
			} else {
				$('#modal-actions .modal-title').text('Update credit');
				$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button> &#160; <button id="delete" name="_delete" class="btn btn-default" type="submit" value="delete"><i class="glyphicon glyphicon-trash"></i> Delete</button>');
			}
			$('#save').on('click', function(e){
				e.preventDefault();
				var $this = $(this);
				if ($('#delete').length) {
					$('#delete').attr('disabled', 'disabled');
				}
				if (!$('#error-message').length) {
					$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
				}
				$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh glyphicon-white"></i> Saving&#8230;');
				$.post(ROOT+'/actions/add-edit-credit.php', $('#add-edit-credit').serialize(), function(data){
					if (data.status == 'OK') {
						window.location.href = ROOT+'/?id='+data.id+'&type=credit&action='+data.message;
					} else {
						$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
						$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save');
						if ($('#delete').length) {
							$('#delete').removeAttr('disabled');
						}
					}
				}, 'json');
			});
			if ($('#delete').length) {
				$('#delete').on('click', function(e){
					e.preventDefault();
					var $this = $(this);
					$('#save').attr('disabled', 'disabled');
					if (!$('#error-message').length) {
						$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
					}
					$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh"></i> Deleting&#8230;');
					$.post(ROOT+'/actions/delete-credit.php', $('#add-edit-credit').serialize(), function(data){
						if (data.status == 'OK') {
							window.location.href = ROOT+'/?id='+data.id+'&type=credit&action='+data.message;
						} else {
							$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
							$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-retweet glyphicon-white"></i> Update');
							$('#save').removeAttr('disabled');
						}
					}, 'json');
				});
			}
		});
	});

	$('a.cash').each(function(i, el){
		$(el).attr('href', $(el).data("target"))
			.on('click', function(e){
			e.preventDefault();
			$('#modal-actions .modal-footer').empty(); // reset modal footer content
			$('#modal-actions .modal-body').load('./inc/cash.php');
			$('#modal-actions .modal-title').text('Update cash');
			$('#modal-actions').attr('aria-labelledby', 'Update cash');
			$('#modal-actions .modal-footer').append('<button id="update" name="_update" class="btn btn-primary" type="submit" value="update"><i class="glyphicon glyphicon-retweet glyphicon-white"></i> Update</button>');

			$('#update').on('click', function(e){
				e.preventDefault();
				var $this = $(this);
				if (!$('#error-message').length) {
					$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
				}
				$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh glyphicon-white"></i> Updating&#8230;');
				$.post(ROOT+'/actions/update-cash.php', $('#update-cash').serialize(), function(data){
					if (data.status == 'OK') {
						window.location.href = ROOT+'/?id=c1&type=cash&action=updated';
					} else {
						$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
						$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-retweet glyphicon-white"></i> Update');
					}
				}, 'json');
			});
		})
	});

	$('a.edit-payor').each(function(i, el){
		$(el).attr('href', $(el).data('target'))
			.on('click', function(e){
			e.preventDefault();
			var id = $(this).data('id');
			$('#modal-actions .modal-footer').empty(); // reset modal footer content
			$('#modal-actions .modal-body').load('./inc/payor.php', { 'p' : id });
			if (id == undefined) {
				$('#modal-actions .modal-title').text('Add payor');
			} else {
				$('#modal-actions .modal-title').text('Update payor');
			}
			$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button>');
			$('#save').on('click', function(e){
				e.preventDefault();
				var $this = $(this);
				if (!$('#error-message').length) {
					$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
				}
				$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh glyphicon-white"></i> Saving&#8230;');
				$.post(ROOT+'/actions/add-edit-payor.php', $('#add-edit-payor').serialize(), function(data){
					if (data.status == 'OK') {
						window.location.href = ROOT+'/payor.php?id='+data.id+'&action='+data.message;
					} else {
						$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
						$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save');
					}
				}, 'json');
			});
		});
	});

	$('a.delete-payor').each(function(i, el){
		$(el).on('click', function(e){
			e.preventDefault();
			if (confirm('Are you sure you want to delete ' + $(this).data('name') + ' from the list of payors?')) {
				var $tr = $(this).closest('tr');
				if ($('#response').length) {
					$('#response').removeClass('alert-success alert-danger').hide();
					if ($('#message').length) {
						$('#message').remove();
					}
				} else {
					$('#manage-payors').before('<div id="response" class="alert alert-dismissable" style="display: none;"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>');
				}
				$.post(ROOT+'/actions/delete-payor.php', { payor_id : $(this).data('id') }, function(data){
					console.log(data);
					console.log($tr);
					if (data.status == 'OK') {
						$tr.slideUp('normal').promise().done(function() {
							$tr.remove();
						});
						$('#response').addClass('alert-success')
							.append('<span id="message"><i class="glyphicon glyphicon-saved"></i> ' + data.message + '</span>')
							.slideDown('fast');
					} else {
						$('#response').addClass('alert-danger')
							.append('<span id="message"><i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + '</span>')
							.slideDown('fast');
					}
				}, 'json');
			}
		});
	});

	$('a.edit-category').each(function(i, el){
		$(el).attr('href', $(el).data('target'))
			.on('click', function(e){
			e.preventDefault();
			var id = $(this).data('id');
			$('#modal-actions .modal-footer').empty(); // reset modal footer content
			$('#modal-actions .modal-body').load('./inc/category.php', { 'id' : id });
			if (id == undefined) {
				$('#modal-actions .modal-title').text('Add category');
			} else {
				$('#modal-actions .modal-title').text('Update category');
			}
			$('#modal-actions .modal-footer').append('<button id="save" name="_save" class="btn btn-primary" type="submit" value="save"><i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save</button>');
			$('#save').on('click', function(e){
				e.preventDefault();
				var $this = $(this);
				if (!$('#error-message').length) {
					$('#modal-actions .modal-body').prepend('<div id="error-message"></div>');
				}
				$this.attr('disabled', 'disabled').html('<i class="glyphicon glyphicon-refresh glyphicon-white"></i> Saving&#8230;');
				$.post(ROOT+'/actions/add-edit-category.php', $('#add-edit-category').serialize(), function(data){
					if (data.status == 'OK') {
						window.location.href = ROOT+'/category.php?id='+data.id+'&action='+data.message;
					} else {
						$('#error-message').addClass('alert alert-danger alert-dismissable').html('<i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + ' <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>');
						$this.removeAttr('disabled').html('<i class="glyphicon glyphicon-hdd glyphicon-white"></i> Save');
						console.log(data);
					}
				}, 'json');
			});
		});
	});

	$('a.delete-category').each(function(i, el){
		$(el).on('click', function(e){
			e.preventDefault();
			if (confirm('Are you sure you want to delete ' + $(this).data('name') + ' from the list of categories?')) {
				var $tr = $(this).closest('tr');
				if ($('#response').length) {
					$('#response').removeClass('alert-success alert-danger').hide();
					if ($('#message').length) {
						$('#message').remove();
					}
				} else {
					$('#manage-categories').before('<div id="response" class="alert alert-dismissable" style="display: none;"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>');
				}
				$.post(ROOT+'/actions/delete-category.php', { category_id : $(this).data('id') }, function(data){
					if (data.status == 'OK') {
						$tr.slideUp('normal').promise().done(function() {
							$tr.remove();
						});
						$('#response').addClass('alert-success')
							.append('<span id="message"><i class="glyphicon glyphicon-saved"></i> ' + data.message + '</span>')
							.slideDown('fast');
					} else {
						$('#response').addClass('alert-danger')
							.append('<span id="message"><i class="glyphicon glyphicon-exclamation-sign"></i> ' + data.message + '</span>')
							.slideDown('fast');
						console.log(data.error);
					}
				}, 'json');
			}
		});
	});

	// populate debit name autocomplete values
	$('#modal-actions').on('shown.bs.modal', function () {
		if ($('#debit_name').length) {
			$('#debit_name').autocomplete({
				source: ROOT+'/inc/debit-names.php',
				minLength: 2
			}).focus();
		}

		if ($('#payor_name').length) {
			$('#payor_name').focus();
		}

		if ($('#category_name').length) {
			$('#category_name').focus();
		}
	});
 
})(jQuery);
