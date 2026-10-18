<?php
	include 'includes/session.php';
	include 'includes/slugify.php';

	if(isset($_POST['vote'])){
		$activeElectionQuery = $conn->query(
			"SELECT e.id FROM elections e
			 INNER JOIN voters v ON v.state_id = e.state_id
			   AND v.district_id = e.district_id
			   AND v.constituency_id = e.constituency_id
			 WHERE e.status NOT IN ('Draft', 'Closed')
			 AND e.start_at <= NOW() AND e.end_at >= NOW()
			 AND v.id = ".(int) $voter['id']."
			 ORDER BY e.start_at DESC, e.id DESC LIMIT 1"
		);
		$activeElection = $activeElectionQuery ? $activeElectionQuery->fetch_assoc() : false;
		if(!$activeElection){
			$_SESSION['error'][] = 'Voting is not currently open.';
			header('location: home.php');
			exit;
		}
		$activeElectionId = (int) $activeElection['id'];
		$ballotCheck = $conn->query("SELECT id FROM election_ballots WHERE election_id = $activeElectionId AND voter_id = ".(int) $voter['id']." LIMIT 1");
		if($ballotCheck && $ballotCheck->num_rows > 0){
			$_SESSION['error'][] = 'You have already voted in this election.';
			header('location: home.php');
			exit;
		}
		if(count($_POST) == 1){
			$_SESSION['error'][] = 'Please vote atleast one candidate';
		}
		else{
			$_SESSION['post'] = $_POST;
			$sql = "SELECT * FROM positions WHERE election_id = $activeElectionId";
			$query = $conn->query($sql);
			$error = false;
			$database_error = '';
			$sql_array = array();
			while($row = $query->fetch_assoc()){
				$position = slugify($row['description']);
				$pos_id = $row['id'];
				$election_id = (int) $row['election_id'];
				if(isset($_POST[$position])){
					if($row['max_vote'] > 1){
						if(count($_POST[$position]) > $row['max_vote']){
							$error = true;
							$_SESSION['error'][] = 'You can only choose '.$row['max_vote'].' candidates for '.$row['description'];
						}
						else{
							foreach($_POST[$position] as $key => $values){
								$sql_array[] = "INSERT INTO votes (election_id, voters_id, candidate_id, position_id) VALUES ('$election_id', '".$voter['id']."', '".(int) $values."', '$pos_id')";
							}

						}
						
					}
					else{
						$candidate = $_POST[$position];
						$sql_array[] = "INSERT INTO votes (election_id, voters_id, candidate_id, position_id) VALUES ('$election_id', '".$voter['id']."', '".(int) $candidate."', '$pos_id')";
					}

				}
				
			}

			if(!$error){
				$conn->begin_transaction();
				foreach($sql_array as $sql_row){
					if(!$conn->query($sql_row)){
						$database_error = $conn->error;
						break;
					}
				}
				if($database_error === '' && !$conn->query("INSERT INTO election_ballots (election_id, voter_id) VALUES ($activeElectionId, ".(int) $voter['id'].")")){
					$database_error = $conn->error;
				}

				if($database_error === ''){
					$conn->commit();
					unset($_SESSION['post']);
					$_SESSION['success'] = 'Ballot Submitted';
				}
				else{
					$conn->rollback();
					$_SESSION['error'][] = 'Unable to submit ballot: '.$database_error;
				}

			}

		}

	}
	else{
		$_SESSION['error'][] = 'Select candidates to vote first';
	}

	header('location: home.php');

?>