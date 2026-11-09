<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;

$stmt = $pdo->prepare("
SELECT *
FROM bookings
WHERE id = ?
");

$stmt->execute([$booking_id]);

$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found");
}

$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $payment_method = $_POST['payment_method'];

    $stmt = $pdo->prepare("
    INSERT INTO payments
    (
        booking_id,
        amount,
        payment_method,
        payment_status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        'Paid'
    )
    ");

    $stmt->execute([
        $booking_id,
        $booking['total_price'],
        $payment_method
    ]);

    $success = "Payment Successful!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>Make Payment</h2>

<?php if($success): ?>
<div class="alert alert-success">
    <?php echo $success; ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">

<label>Booking ID</label>

<input
type="text"
class="form-control"
value="<?php echo $booking['id']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Total Amount</label>

<input
type="text"
class="form-control"
value="<?php echo $booking['total_price']; ?>"
readonly>

</div>

<div class="mb-3">

<label>Payment Method</label>

<select
name="payment_method"
class="form-control">

<option value="Card">Card</option>
<option value="Bank Transfer">Bank Transfer</option>
<option value="Cash">Cash</option>

</select>

</div>

<button
type="submit"
class="btn btn-success">

Pay Now

</button>

</form>

</div>

</body>
</html>