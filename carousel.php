<?php

$page_title = 'Carousel';

$images = array(
		array('6H', 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean.'),
		array('23H', 'A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.'),
		array('28H', 'Even the all-powerful Pointing has no control about the blind texts it is an almost unorthographic life. One day however a small line of blind text by the name of Lorem Ipsum decided to leave for the far World of Grammar.'),
		array('114H', 'The Big Oxmox advised her not to do so, because there were thousands of bad Commas, wild Question Marks and devious Semikoli, but the Little Blind Text didn&#8217;t listen. She packed her seven versalia, put her initial into the belt and made herself on the way.'),
		array('126H', 'When she reached the first hills of the Italic Mountains, she had a last view back on the skyline of her hometown Bookmarksgrove, the headline of Alphabet Village and the subline of her own road, the Line Lane.'),
		array('127H', 'Pityful a rethoric question ran over her cheek, then she continued her way. On her way she met a copy. The copy warned the Little Blind Text, that where it came from it would have been rewritten a thousand times&#8230;'),
		array('135H', '&#8230;and everything that was left from its origin would be the word &#8220;and&#8221; and the Little Blind Text should turn around and return to its own, safe country.'),
		array('154H', 'But nothing the copy said could convince her and so it didn&#8217;t take long until a few insidious Copy Writers ambushed her, made her drunk with Longe and Parole and dragged her into their agency, where they abused her for their projects again and again.')
		);
$image_count = (count($images)-1);
$image = rand(0, $image_count);
$image_prev = (($image-1) < 0) ? $image_count : ($image-1);
$image_next = (($image+1) > $image_count) ? 0 : ($image+1);

$css[] = 'carousel.css';

include 'header.inc';

echo '<h1>' . $page_title . '</h1>

<div id="rotator">
	<img id="rotator-image" class="img-responsive" src="img/' . $images[$image][0] . '.jpg" alt="'. $images[$image][1] .'" height="760">
	<p id="rotator-text">' . $images[$image][1] . '</p>
	<div id="rotator-nav">
		<div id="rotator-prev">
			<div class="rotator-button">
				<a id="rotator-button-prev" href="#" data-index="' . $image_prev . '"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span><span class="sr-only">Previous</span></a>
			</div>
		</div>
		<div id="rotator-next">
			<div class="rotator-button">
				<a id="rotator-button-next" href="#" data-index="' . $image_next . '"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span><span class="sr-only">Next</span></a>
			</div>
		</div>
	</div>
</div>

<div class="row">' . PHP_EOL;

$i = 1;
foreach ($images as $k => $v) {
	echo '	<div class="col-md-3 col-sm-6" style="margin-bottom: 1em;">
		<a class="gallery" href="img/' . $v[0] . '.jpg" title="' . $v[1] . '""><img class="img-responsive img-thumbnail" src="img/thumbs/' . $v[0] . '.jpg" alt="' . $v[1] . '"></a>
	</div>' . PHP_EOL;
	if ($i == 4) {
		$i = 0;
		echo '</div><!-- /.row -->
<div class="row">' . PHP_EOL;
	}
	$i++;
}

include 'footer.inc';
