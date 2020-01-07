<?php
require_once("conf/conn.php");
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Form Login</title>
</head>
<body>

<form action="login-proses.php" method="post">
	<h1>Admin Log In</h1>
	<?=show_alert();?>
	Username : <input type="text" name="username">
	<br>
	Password : <input type="password" name="password">
	<br>
	<label for="rmb">
		<input type="checkbox" name="remember" value="1" id="rmb">
		Remember Me
	</label>
	<br>
	<button type="submit" name="btn">Log In</button>
</form>

</body>
</html>
