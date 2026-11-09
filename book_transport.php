<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user'])) {
    die("Please login first.");
}

$user_id = $_SESSION['user']['id'];

$transport_id = intval($_GET['id']);

$stmt = $pdo->prepare("
    INSERT INTO transportation_bookings
    (user_id, transport_id)
    VALUES (?, ?)
");

$stmt->execute([$user_id, $transport_id]);

header("Location: dashboard.php");
exit;
?>