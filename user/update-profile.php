<?php

//To Handle Session Variables on This Page
session_start();

if (empty($_SESSION['id_user'])) {
	header("Location: ../index.php");
	exit();
}

//Including Database Connection From db.php file to avoid rewriting in all files
require_once("../db.php");

//if user Actually clicked update profile button
if (isset($_POST)) {

	//Escape Special Characters
	$firstname = $conn->real_escape_string( $_POST['fname']);
	$lastname = $conn->real_escape_string( $_POST['lname']);
	$address = $conn->real_escape_string( $_POST['address']);
	$city = $conn->real_escape_string( $_POST['city']);
	$state = $conn->real_escape_string( $_POST['state']);
	$contactno = $conn->real_escape_string( $_POST['contactno']);
	$qualification = $conn->real_escape_string( $_POST['qualification']);
	$stream = $conn->real_escape_string( $_POST['stream']);
	$skills = $conn->real_escape_string( $_POST['skills']);
	$aboutme = $conn->real_escape_string( $_POST['aboutme']);
	$Hsc = $conn->real_escape_string( $_POST['hsc']);
	$Ssc = $conn->real_escape_string( $_POST['ssc']);
	$UG = $conn->real_escape_string( $_POST['ug']);
	$PG = $conn->real_escape_string( $_POST['pg']);

	$uploadOk = false;
	$file = "";

	if (isset($_FILES['resume']) && !empty($_FILES['resume']['name']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {

		$folder_dir = "../uploads/resume/";
		if (!file_exists($folder_dir)) {
			mkdir($folder_dir, 0777, true);
		}

		$base = basename($_FILES['resume']['name']);
		$resumeFileType = strtolower(pathinfo($base, PATHINFO_EXTENSION));

		if ($resumeFileType === "pdf") {

			if ($_FILES['resume']['size'] <= 5242880) { // Max 5MB

				$file = uniqid() . "." . $resumeFileType;
				$filename = $folder_dir . $file;

				if (move_uploaded_file($_FILES["resume"]["tmp_name"], $filename)) {
					$uploadOk = true;
				} else {
					$_SESSION['uploadError'] = "Failed to upload file to server. Please check folder permissions.";
					header("Location: edit-profile.php");
					exit();
				}
			} else {
				$_SESSION['uploadError'] = "File size exceeds 5MB limit.";
				header("Location: edit-profile.php");
				exit();
			}
		} else {
			$_SESSION['uploadError'] = "Invalid file format. Only PDF files are allowed.";
			header("Location: edit-profile.php");
			exit();
		}
	}



	//Update User Details Query
	$sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', address='$address', city='$city', state='$state', contactno='$contactno', qualification='$qualification', stream='$stream', skills='$skills', aboutme='$aboutme', Hsc='$Hsc', Ssc='$Ssc', UG='$UG', PG='$PG'";

	if ($uploadOk == true) {
		$sql .= ", resume='$file'";
	}

	$sql .= " WHERE id_user='$_SESSION[id_user]'";

	if ($conn->query($sql) === TRUE) {
		$_SESSION['name'] = $firstname . ' ' . $lastname;
		$_SESSION['profile_updated_success'] = true;
		// Redirect to AI Resume ATS Review & Score page
		header("Location: resume-review.php");
		exit();
	} else {
		echo "Error " . $sql . "<br>" . $conn->error;
	}
	//Close database connection. Not compulsory but good practice.
	$conn->close();
} else {
	//redirect them back to dashboard page if they didn't click update button
	header("Location: edit-profile.php");
	exit();
}
