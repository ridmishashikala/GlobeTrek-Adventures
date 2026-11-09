<?php
require_once 'db_connection.php';

$stmt = $pdo->query("SELECT * FROM transportation");
$vehicles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transportation</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="text-center mb-4">Transportation Options</h2>

    <table class="table table-bordered table-hover shadow">
        <thead class="table-primary">
            <tr>
                <th>Type</th>
                <th>Vehicle</th>
                <th>Capacity</th>
                <th>Price</th>
                <th width="150">Action</th>
            </tr>
        </thead>

        <tbody>

        <?php if(!empty($vehicles)): ?>

            <?php foreach($vehicles as $v): ?>

            <tr>
                <td><?= htmlspecialchars($v['transport_type']) ?></td>
                <td><?= htmlspecialchars($v['vehicle_name']) ?></td>
                <td><?= htmlspecialchars($v['capacity']) ?></td>
                <td>$<?= number_format($v['price'],2) ?></td>

                <td>
                    <a href="book_transport.php?id=<?= $v['id'] ?>"
                       class="btn btn-primary btn-sm w-100">
                        Select Vehicle
                    </a>
                </td>

            </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="5" class="text-center">
                    No transportation available.
                </td>
            </tr>

        <?php endif; ?>

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