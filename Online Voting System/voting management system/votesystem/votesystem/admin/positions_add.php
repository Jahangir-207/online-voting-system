<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$description = $conn->real_escape_string($_POST['description']);
		$max_vote = (int) $_POST['max_vote'];

		$sql = "SELECT * FROM positions ORDER BY priority DESC LIMIT 1";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();

		$priority = $row ? ((int) $row['priority'] + 1) : 1;

		$sql = "SELECT id FROM elections
				ORDER BY FIELD(status, 'Active', 'Scheduled', 'Draft', 'Closed'),
				start_at DESC, id DESC
				LIMIT 1";
		$query = $conn->query($sql);
		$election = $query ? $query->fetch_assoc() : false;

		if(!$election){
			$_SESSION['error'] = 'Create an election before adding a position.';
		}
		elseif($conn->query("INSERT INTO positions (election_id, description, max_vote, priority)
			VALUES ('".(int) $election['id']."', '$description', '$max_vote', '$priority')")){
			$_SESSION['success'] = 'Position added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}

	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: positions.php');
?>