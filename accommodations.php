<?php
require_once 'db_connection.php';

$stmt = $pdo->query("SELECT * FROM accommodations");
$accommodations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Accommodation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-5">

    <h2 class="text-center mb-4">
        Accommodation Options
    </h2>

    <table class="table table-bordered table-hover shadow">

        <thead class="table-success">

            <tr>
                <th>Hotel</th>
                <th>Location</th>
                <th>Room Type</th>
                <th>Price Per Night</th>
                <th>Action</th>
            </tr>

        </thead>

        <tbody>

        <?php foreach($accommodations as $a): ?>

        <tr>

            <td><?= htmlspecialchars($a['hotel_name']) ?></td>

            <td><?= htmlspecialchars($a['location']) ?></td>

            <td><?= htmlspecialchars($a['room_type']) ?></td>

            <td>$<?= number_format($a['price_per_night'],2) ?></td>

            <td>

                <a href="book_accommodation.php?id=<?= $a['id'] ?>" class="btn btn-success btn-sm">
                    Book Room
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <div class="text-center mt-4">

        <a href="dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>
</html>