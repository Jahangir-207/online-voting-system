<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$id = $_POST['id'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$state = (int) $_POST['state_id'];
		$district = (int) $_POST['district_id'];
		$constituency = (int) $_POST['constituency_id'];
		$password = $_POST['password'];

		$sql = "SELECT * FROM voters WHERE id = $id";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();

		if($password == $row['password']){
			$password = $row['password'];
		}
		else{
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		$validArea = $conn->query("SELECT 1 FROM districts WHERE id = $district AND state_id = $state")
			&& $conn->query("SELECT 1 FROM constituencies c INNER JOIN districts d ON d.id = c.district_id WHERE c.id = $constituency AND d.id = $district");
		$sql = "UPDATE voters SET firstname = '$firstname', lastname = '$lastname', password = '$password', state_id = $state, district_id = $district, constituency_id = $constituency WHERE id = '$id'";
		if(!$validArea){
			$_SESSION['error'] = 'The selected state, district, and constituency do not match.';
		}
		elseif($conn->query($sql)){
			$_SESSION['success'] = 'Voter updated successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up edit form first';
	}

	header('location: voters.php');

?>