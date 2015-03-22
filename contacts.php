<?php
$xml = simplexml_load_file('contacts.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
$xml_item_count = count($xml->person);
$contacts = array();
$j = 0;
for ($i = 0; $i < $xml_item_count; $i++) {
	$contacts[$j]['Name'] = strval($xml->person[$i]->name);
	$telephone = strval($xml->person[$i]->phone);
	$contacts[$j]['Telephone'] = substr($telephone, 0, 3) . '-' .  substr($telephone, 3, 3) . '-' .  substr($telephone, -4);
	$contacts[$j]['Email'] = strval($xml->person[$i]->email);
	$attributes = $xml->person[$i]->{'website-url'}->attributes();
	$contacts[$j]['Website']['text'] = strval($xml->person[$i]->{'website-url'});
	$contacts[$j]['Website']['url'] = strval($attributes['src']);
	$contacts[$j]['Job title'] = strval($xml->person[$i]->{'job-title'});
	$j++;
}

$page_title = 'Contacts';

include 'header.inc';

echo '<h1>' . $page_title . '</h1>

<div class="row">' . PHP_EOL;

$i = 1;
foreach ($contacts as $v) {
	echo '	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">' . $v['Name'] . '</h2>
			</div>
			<div class="panel-body">
				<strong>' . $v['Job title'] . '</strong><br>
				<span class="sr-only">Telephone: </span><span class="glyphicon glyphicon-phone" aria-hidden="true"></span> <a href="tel:' . $v['Telephone'] . '">' . $v['Telephone'] . '</a><br>
				<span class="sr-only">Email: </span><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> <a href="mailto:' . $v['Email'] . '">' . $v['Email'] . '</a><br>
				<span class="sr-only">Website: </span><span class="glyphicon glyphicon-globe" aria-hidden="true"></span> <a href="' . $v['Website']['url'] . '" target="_blank">' . $v['Website']['text'] . '</a>
			</div>
		</div>
	</div>' . PHP_EOL;
	if (($i % 3) == 0) {
		echo '</div><!-- /.row -->
<div class="row">' . PHP_EOL;
	}
	$i++;
}

include 'footer.inc';
