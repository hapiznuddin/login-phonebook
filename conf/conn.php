<?php
//conf/conn.php
session_start();

$user = "root";
$pass = "";
$db = "hapiz";
$host = "localhost";

$db = new PDO('mysql:host=localhost;dbname='.$db.';charset=utf8',$user,$pass);

require_once("conn/library.php");
require_once("conn/ClassLogin.php");

$login = new Login();