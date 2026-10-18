<?php
include 'includes/session.php';

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($id < 1 || !in_array($status, array('Active', 'Closed'), true)) {
    $_SESSION['error'] = 'Invalid election status request.';
} else {
    $status = $conn->real_escape_string($status);
    if ($conn->query("UPDATE elections SET status = '$status' WHERE id = $id")) {
        $_SESSION['success'] = 'Election status updated.';
    } else {
        $_SESSION['error'] = 'Unable to update election status: '.$conn->error;
    }
}

header('location: elections.php');
