<?php
include "conf/conn.php";
$login->login_redir(); //ini method untuk mencegah user membypass halaman dengan akses langsung ke URL
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Contoh Halaman</title>
</head>
<body>
	<ul>
		<li><a href="menu1.php">Menu 1</a></li>
		<li><a href="menu2.php">Menu 2</a></li>
		<li><a href="menu3.php">Menu 3</a></li>
		<li><a href="logout.php">Logout</a></li>
	</ul>


	<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Explicabo, autem, dolor, vitae inventore natus consectetur accusamus officiis veritatis illo quasi molestiae iusto nesciunt officia rem omnis culpa reiciendis odio harum eaque assumenda at iure dolorum!</p>
</body>
</html>