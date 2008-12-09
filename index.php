<?php
	// yahoo! application id.  get your own at http://developer.yahoo.com/wsregapp/
	$appid = "GET_YOUR_OWN_AT_URL_ABOVE";

	// number of results per page
	$count = 10;

	$term = rawurlencode(stripslashes($_GET['q']));
	$start = stripslashes($_GET['s']);

	if (!$start) $start = 0;

function searchFor($term,$start,$count,$appid) {

	$url = "http://boss.yahooapis.com/ysearch/web/v1/$term?appid=$appid&format=json&start=$start&count=$count";

	$session = curl_init();
	curl_setopt ( $session, CURLOPT_URL, $url );
	curl_setopt ( $session, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt ( $session, CURLOPT_CONNECTTIMEOUT, 2 );
	$result = curl_exec ( $session );
	curl_close( $session );

	$out = json_decode($result,true);

	$num = $out['ysearchresponse']['count'];
	$totalhits = $out['ysearchresponse']['totalhits'];

	for($i=0;$i<$num;$i++) {
		$abstract = $out['ysearchresponse']['resultset_web'][$i]['abstract'];
		$url = $out['ysearchresponse']['resultset_web'][$i]['url'];
		$title = $out['ysearchresponse']['resultset_web'][$i]['title'];
		$dispurl = $out['ysearchresponse']['resultset_web'][$i]['dispurl'];
		
		echo "<p><b><a href=\"$url\">$title</a></b> (<a href=\"$url\" target=\"_blank\">n</a>)<br />$abstract<br />$dispurl";
	}

	$prev = $start - $count;
	$start = $start + $count;

	if(($count <= $totalhits) && ($prev >= 0)) {
		echo "<div class=\"nav\"><a href=\"index.php?q=$term&s=$prev\"><< prev</a> (<a href=\"index.php\">home</a>) <a href=\"index.php?q=$term&s=$start\">next >></a></div>";
	} else if ($count <= $totalhits) {
		echo "<div class=\"nav\">(<a href=\"index.php\">home</a>) <a href=\"index.php?q=$term&s=$start\">next >></a></div>";
	}

	printFoot();

}

function printForm($term) {
	echo "<form method=\"get\" action=\"index.php\">";
	echo "<input type=\"text\" name=\"q\" value=\"" . rawurldecode($term). "\" />";
	echo "<input type=\"hidden\" name=\"s\" value=\"0\" />";
	echo "<input type=\"submit\" value=\"find\" />";
	echo "</form>";
}

function printFoot() {
	echo "<hr /><div class=\"footer\">";
	echo "written by <a href=\"http://ultramookie.com\">ultramookie</a> | powered by <a href=\"http://developer.yahoo.com/search/boss/\">yahoo! search boss</a> | " . date("Y");
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
<div class="main">

<?php
	printForm($term);

	if ($term) {
		searchFor($term,$start,$count,$appid);
	} else {
		print "all the goodness of <a href=\"http://ysearch.com\">yahoo! search</a>, none of the fat.";
	}
?>
</div>
</script>
</body>
</html>
