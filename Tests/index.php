<?php

function find_tests($dir = '.') {
	
	$ds = DIRECTORY_SEPARATOR;
	$tests = array();
	
	foreach(array_filter(scandir($dir), function($i) use ($dir, $ds) {return $i[0] != '.' and is_dir("$dir$ds$i");}) as $d) {
		if(file_exists("$dir$ds$d{$ds}index.php")) $tests[$d] = "$dir$ds$d{$ds}index.php";
		elseif(is_dir("$dir$ds$d")) $tests = array_merge($tests, find_tests("$dir$ds$d"));
	}
	ksort($tests);
	return $tests;
	
}

$tests = find_tests('.');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD xhtml 1.0 frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<head>
	<title>MVCWebComponents Tests</title>
	<style type="text/css">
		body {
			font-family: Meiryo;
			width: 100%;
			padding: 0;
			margin: 10px 0px 0px 25px;
			overflow: hidden;
		}
		
		div {
			float: left;
			clear: both;
			width: 100%;
		}
		
		#header {
			height: 20%;
		}
		
		#tests {
			width: 19%;
			clear: left;
		}
		
		#tests ul {
			list-style-type: none;
			padding-left: 10px;
		}
		
		#tests a {
			text-decoration: none;
			color: #00f;
		}
		
		#tests a:hover {
			text-decoration: underline;
		}
		
		#test {
			 float: left;
			 clear: right;
			 width: 70%;
			 height: 80%;
			 border: 1px solid #666;
		}
	</style>
</head>
<body>
	<div id="header"><h1>MVCWebComponents Tests</h1></div>
	<div id="tests">
		<ul>
			<?php foreach($tests as $test => $dir): ?>
				<li>
					<a href="<?php echo $dir ?>" target="test"><?php echo $test ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<iframe id="test" src="<?php echo reset($tests) ?>" />
</body>