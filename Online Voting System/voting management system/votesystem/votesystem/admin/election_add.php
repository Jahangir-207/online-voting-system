<?php
include 'includes/session.php';

if (!isset($_POST['add'])) {
    $_SESSION['error'] = 'Fill in the election form first.';
    header('location: elections.php');
    exit;
}

$name = trim($_POST['name']);
$description = trim($_POST['description']);
$start = strtotime($_POST['start_at']);
$end = strtotime($_POST['end_at']);
$electionTypeId = (int) ($_POST['election_type_id'] ?? 0);
$stateId = (int) ($_POST['state_id'] ?? 0);
$districtId = (int) ($_POST['district_id'] ?? 0);
$constituencyId = (int) ($_POST['constituency_id'] ?? 0);

if ($name === '' || !$start || !$end || $end <= $start || !$electionTypeId || !$stateId || !$districtId || !$constituencyId) {
    $_SESSION['error'] = 'Enter valid election details, type, state, district, constituency, and dates.';
} else {
    $name = $conn->real_escape_string($name);
    $description = $conn->real_escape_string($description);
    $startAt = date('Y-m-d H:i:s', $start);
    $endAt = date('Y-m-d H:i:s', $end);
    $validArea = $conn->query("SELECT 1 FROM districts WHERE id = $districtId AND state_id = $stateId")
        && $conn->query("SELECT 1 FROM constituencies c INNER JOIN districts d ON d.id = c.district_id WHERE c.id = $constituencyId AND d.id = $districtId");
    if (!$validArea) {
        $_SESSION['error'] = 'The selected state, district, and constituency do not match.';
        header('location: elections.php');
        exit;
    }
    $sql = "INSERT INTO elections (name, description, start_at, end_at, status, state_id, district_id, constituency_id, election_type_id)
            VALUES ('$name', '$description', '$startAt', '$endAt', 'Scheduled', $stateId, $districtId, $constituencyId, $electionTypeId)";
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Election schedule created successfully.';
    } else {
        $_SESSION['error'] = 'Unable to create election: '.$conn->error;
    }
}

header('location: elections.php');
