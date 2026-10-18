<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$state = (int) $_POST['state_id'];
		$district = (int) $_POST['district_id'];
		$constituency = (int) $_POST['constituency_id'];
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$filename = $_FILES['photo']['name'];
		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);	
		}
		//generate voters id
		$set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$voter = substr(str_shuffle($set), 0, 15);

		$validArea = $conn->query("SELECT 1 FROM districts WHERE id = $district AND state_id = $state")
			&& $conn->query("SELECT 1 FROM constituencies c INNER JOIN districts d ON d.id = c.district_id WHERE c.id = $constituency AND d.id = $district");
		$sql = $validArea ? "INSERT INTO voters (voters_id, password, firstname, lastname, photo, state_id, district_id, constituency_id) VALUES ('$voter', '$password', '$firstname', '$lastname', '$filename', $state, $district, $constituency)" : '';
		if(!$validArea){
			$_SESSION['error'] = 'The selected state, district, and constituency do not match.';
		}
		elseif($conn->query($sql)){
			$_SESSION['success'] = 'Voter added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}

	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: voters.php');
?>