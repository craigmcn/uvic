(function($) {

	$('.gallery').each(function(i, el){
		var $el = $(el);
		$el.on('click', function(e){
			e.preventDefault();
			loadImage($el.attr('href'), $el.attr('title'));
		});
	});

	$('.rotator-button>a').each(function(i, el){
		var $el = $(el)
		$el.on('click', function(e){
			e.preventDefault();
			var $image = $('.gallery:eq('+$el.data('index')+')'),
				index = parseInt($el.data('index')),
				gallery_count = $('.gallery').length-1,
				image_prev = ((index-1) < 0) ? gallery_count : (index-1),
				image_next = ((index+1) > gallery_count) ? 0 : (index+1);
			$('#rotator-button-prev').data('index', image_prev);
			$('#rotator-button-next').data('index', image_next);
			loadImage($image.attr('href'), $image.attr('title'));
		});
	});

	function loadImage(src, text) {
		$('#rotator-image').attr('src', src);
		$('#rotator-text').html(text);
	}

})(jQuery);
