<?php


session_start();

if(empty($_SESSION['id_company'])) {
  header("Location: ../index.php");
  exit();
}

require_once("../db.php");

if(isset($_POST)) {
	$to  = $_POST['to'];

	$subject = $conn->real_escape_string( $_POST['subject']);
	$message = $conn->real_escape_string( $_POST['description']);

	$sql = "INSERT INTO mailbox (id_fromuser, fromuser, id_touser, subject, message) VALUES ('$_SESSION[id_company]', 'company', '$to', '$subject', '$message')";

	if($conn->query($sql) == TRUE) {
		header("Location: mailbox.php");
		exit();
	} else {
		echo $conn->error;
	}
} else {
	header("Location: mailbox.php");
	exit();
}