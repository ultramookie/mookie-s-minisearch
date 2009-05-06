<?php

// mookie's minisearch
// steve "mookie" kong
// http://s.ultramookie.com
//
// licensed under gplv3
// http://www.gnu.org/licenses/gpl-3.0.html
//
// built using yahoo! boss technology

	include_once("config.php");

	$term = rawurlencode(stripslashes($_GET['q']));
	$start = stripslashes($_GET['s']);
	$type = stripslashes($_GET['type']);

	if (!$start) $start = 0;

function searchFor($term,$start,$count,$appid,$type) {

	$url = "http://boss.yahooapis.com/ysearch/web/v1/$term?appid=$appid&format=xml&abstract=long&start=$start&count=$count";

	$session = curl_init();
	curl_setopt ( $session, CURLOPT_URL, $url );
	curl_setopt ( $session, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt ( $session, CURLOPT_CONNECTTIMEOUT, 2 );
	$result = curl_exec ( $session );
	curl_close( $session );

        $xml = simplexml_load_string($result);

	$totalhits = $xml->resultset_web['totalhits'];

	if ($totalhits == 0) {
		echo "<p><b>no results for \"$term\"...</b></p>";
	} else {
		foreach ($xml->resultset_web->result as $result) {
			echo "<p><b><a href=\"$result->url\">$result->title</a></b> (<a href=\"$result->url\" target=\"_blank\">n</a>)<br />$result->abstract<br />$result->dispurl";			
		}

		$prev = $start - $count;
		$start = $start + $count;

		if(($count <= $totalhits) && ($prev >= 0)) {
			echo "<div id=\"nav\"><a href=\"index.php?q=$term&s=$prev&type=$type\"><< prev</a> (<a href=\"index.php\">home</a>) <a href=\"index.php?q=$term&s=$start&type=$type \">next >></a></div>";
		} else if ($count <= $totalhits) {
			echo "<div id=\"nav\">(<a href=\"index.php\">home</a>) <a href=\"index.php?q=$term&s=$start&type=$type \">next >></a></div>";
		}
	}
}
function searchTwitterFor($term,$start,$count,$type) {

	// yahoo! boss index starts at 0. twitter? at 1.
	// yahoo! boss takes last result as start,
	// twitter takes page number.
	if ($start == 0 ) {
		$start++;
	} else {
		$start = ($start / 10) + 1;
	}

	$count = $count * 1.5; // twitter results are shorter.

        $url = "http://search.twitter.com/search.atom?q=$term&page=$start&rpp=$count";

        $session = curl_init();
        curl_setopt ( $session, CURLOPT_URL, $url );
        curl_setopt ( $session, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt ( $session, CURLOPT_CONNECTTIMEOUT, 2 );
        $result = curl_exec ( $session );
        curl_close( $session );

        $xml = simplexml_load_string($result);

        foreach ($xml->entry as $result) {
                echo "<p><a href=\"" . $result->author->uri . "\">" . $result->author->name . "</a>: $result->content ( <a href=\"" . $result->link['href']. "\">tweet</a> )</p><hr />";
        }
}

function printForm($term) {
	echo "<form method=\"get\" action=\"index.php\">";
	echo "<input type=\"text\" name=\"q\" value=\"" . rawurldecode($term). "\" />";
	echo "<input type=\"submit\" value=\"find\" /><br />";
	echo "<input type=\"radio\" name=\"type\" value=\"yahoo\" checked/>yahoo";
	echo "<input type=\"radio\" name=\"type\" value=\"twitter\" />twitter";
	echo "<input type=\"radio\" name=\"type\" value=\"both\" />both";
	echo "<input type=\"hidden\" name=\"s\" value=\"0\" />";
	echo "</form>";
}

function printFoot() {
	echo "<hr /><div id=\"footer\">";
	echo "written by <a href=\"http://ultramookie.com\">ultramookie</a> | get the <a href=\"http://github.com/ultramookie/mookie-s-minisearch/tree/master\">code</a> | powered by <a href=\"http://developer.yahoo.com/search/boss/\">yahoo! search boss</a> and <a href=\"http://search.twitter.com\">twitter search</a> | " . date("Y");
	echo "</div>";
}

?>

<html>
<head>
<?php
	if ($term) {
		echo "<title>" . rawurldecode($term) . " - mookie's minisearch</title>";
	} else {
		echo "<title>mookie's minisearch</title>";
	}
?>
<link rel="stylesheet" type="text/css" media="screen" href="style.css"/>
<link rel="search" type="application/opensearchdescription+xml" title="moookie's minisearch" href="minisearch.xml">
</head>
<body>
<div id="wrap">
<?php
	printForm($term);

	if ( ($term) && ($type == 'both') ) {
		print "<div id=\"main\">";
		searchFor($term,$start,$count,$appid,$type);
		print "</div>";
		print "<div id=\"sidebar\">";
		print "<b>Results from <a href=\"http://www.twitter.com\">Twitter</a>...</b>";
		searchTwitterFor($term,$start,$count,$type);
		print "</div>";
		print "</div>";
		printFoot();
	} else if ( ($term) && ($type == 'twitter') ) {
		$count = $count * $twitMulti;
		print "<div id=\"single\">";
		searchTwitterFor($term,$start,$count,$type);
		print "</div>";
		print "</div>";
		printFoot();
	} else if ($term) {
		print "<div id=\"single\">";
		searchFor($term,$start,$count,$appid,$type);
		print "</div>";
		print "</div>";
		printFoot();
	} else {
		print "all the goodness of <a href=\"http://ysearch.com\">yahoo! search</a> and <a href=\"http://www.twitter.com\">twitter</a>, none of the fat.";
		print "</div>";
	}
?>

</body>
</html>
