<!DOCTYPE html>
<html>
<head>
	<title>Json Mocker</title>
	<style type="text/css">
		body {
			font-family : "Arial", Helvetica, sans-serif;
		}
	</style>
</head>
<body>
<h1>Welcome to Json Mocker</h1>
<ul>
	<li><a href="/db">db</a></li>
	<?php
	foreach ($this->data as $key => $value) {
		echo '<li><a href="/'.$key.'">'.$key.'</a></li>';
	}
	?>
</ul>
</body>
</html>