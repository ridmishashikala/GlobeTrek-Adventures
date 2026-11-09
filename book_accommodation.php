<?php
session_start();
require_once 'db_connection.php';

$user_id = $_SESSION['user_id'];

$accommodation_id = $_GET['id'];

$stmt = $pdo->prepare("
INSERT INTO accommodation_bookings
(user_id, accommodation_id)
VALUES (?, ?)
");

$stmt->execute([$user_id, $accommodation_id]);

header("Location: dashboard.php");
exit;
?>