(function($) {

	$('.gallery').each(function(i, el){
		var $gallery = $('.gallery'),
			$el = $(el);
		$el.on('click', function(e){
			e.preventDefault();
			loadImage($gallery.index(el), $el.attr('href'), $el.attr('title'));
		});
	});

	$('.rotator-button>a').each(function(i, el){
		var $el = $(el)
		$el.on('click', function(e){
			e.preventDefault();
			var $image = $('.gallery:eq('+$el.data('index')+')'),
				index = parseInt($el.data('index'));
			loadImage(index, $image.attr('href'), $image.attr('title'));
		});
	});

	function loadImage(index, src, text) {
		var gallery_count = $('.gallery').length-1,
			image_prev = ((index-1) < 0) ? gallery_count : (index-1),
			image_next = ((index+1) > gallery_count) ? 0 : (index+1);
		if ($('#rotator-image').attr('src') != src) {
			$('#rotator').prepend('<div id="loading" class="rotator-loading"><div class="rotator-loading-indicator"></div></div>');
			$('#rotator-image').one('load', function(e){
				$('#loading').remove();
				}).attr('src', src);
			$('#rotator-text').html(text);
			$('#rotator-button-prev').data('index', image_prev);
			$('#rotator-button-next').data('index', image_next);
		}
	}

})(jQuery);
