<?php

//To Handle Session Variables on This Page
session_start();

if (empty($_SESSION['id_company'])) {
	header("Location: ../index.php");
	exit();
}

//Including Database Connection From db.php file to avoid rewriting in all files
require_once("../db.php");

//if user Actually clicked Add Post Button
if (isset($_POST)) {

	$stmt = $conn->prepare("INSERT INTO job_post(id_company, jobtitle, description, minimumsalary, maximumsalary, experience, qualification) VALUES (?,?, ?, ?, ?, ?, ?)");

	$stmt->bind_param("issssss", $_SESSION['id_company'], $jobtitle, $description, $minimumsalary, $maximumsalary, $experience, $qualification);

	$jobtitle = $conn->real_escape_string( $_POST['jobtitle']);
	$description = $conn->real_escape_string( $_POST['description']);
	$minimumsalary = $conn->real_escape_string( $_POST['minimumsalary']);
	$maximumsalary = $conn->real_escape_string( $_POST['maximumsalary']);
	$experience = $conn->real_escape_string( $_POST['experience']);
	$qualification = $conn->real_escape_string( $_POST['qualification']);


	if ($stmt->execute()) {
		//If data Inserted successfully then redirect to dashboard
		$_SESSION['jobPostSuccess'] = true;
		include 'sendmail.php';
		header("Location: index.php");
		exit();
	} else {
		//If data failed to insert then show that error. Note: This condition should not come unless we as a developer make mistake or someone tries to hack their way in and mess up :D
		echo "Error ";
	}

	$stmt->close();

	$conn->close();
} else {
	//redirect them back to dashboard page if they didn't click Add Post button
	header("Location: create-job-post.php");
	exit();
}
