<!DOCTYPE html>
<html>
<head>
	<title>Json Mocker</title>
</head>
<body>
Welcome to Json Mocker
<ul>
	<li><a href="/db">db</a></li>
<?php //var_dump($this->data);
foreach ($this->data as $key => $value) {
	echo '<li><a href="/'.$key.'">'.$key.'</a></li>';
}
?>
</ul>
</body>
</html>