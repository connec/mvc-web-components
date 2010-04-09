<html>
<head>
	<style type="text/css">
	
	.emphasis {
		font-style: italic;
	}
	
	.small, .exception-context {
		font-size: 0.8em;
	}
	
	.odd {
		background-color: #eee;
		margin: 5px 0;
	}
	
	</style>
</head>
<body>
<?php

require_once '../../mvc_exception.php';
require_once '../../debug.php';

function throwException() {
	
	throwException2();
	
}

function throwException2() {
	
	throw new MVCWebComponents\MVCException('Test');
	
}

try {
	throwException();
} catch(Exception $e) {
	echo $e->getFormattedMsg();
}

?>
</body>
</html>