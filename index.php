<?php
//index.php
require("conf/conn.php");

$login_status = $login->cek_login();
if($login_status){
	//bawa ke halaman home
	header("location:home.php");
	exit();
}
else{
	//include form log in jika belum log in
	include "login.php";
}
?>