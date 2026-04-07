<?php
    session_start();
    include_once('dbcon.php');

    $stmt = $con->prepare('SELECT 
            campus AS Campus,
            COUNT(*) AS Registered,
            SUM(CASE WHEN attended = "Yes" THEN 1 ELSE 0 END) AS Attended,
            SUM(amountPaid) AS TotalCollection
        FROM 
            ict.registration
        GROUP BY 
            campus

        UNION ALL

        SELECT
            "TOTALS" AS Campus,
            COUNT(*) AS Registered,
            SUM(CASE WHEN attended = "Yes" THEN 1 ELSE 0 END) AS Attended,
            SUM(amountPaid) AS TotalCollection
        FROM 
            ict.registration');

    $stmt->execute();
    $summary = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="card p-5 w-100">
            <div class="text-end">
                <a href="index.php">Return to Menu</a>
            </div>
            <div class="text-center mb-3">

                <h1>Summary Report</h1>
                <h2>(All Campuses)</h2>
            </div>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Campus</th>
                        <th>Registered</th>
                        <th>Attended</th>
                        <th>Total Collection</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $summary->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Campus']) ?></td>
                            <td><?= htmlspecialchars($row['Registered']) ?></td>
                            <td><?= htmlspecialchars($row['Attended']) ?></td>
                            <td><?= number_format($row['TotalCollection'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php
                function generateDate() {
                    date_default_timezone_set('Asia/Manila');
                    $date = date('m/d/Y');
                    return $date;
                }
            ?>
            <div class="text-end">
                <p>Date Generated: <?= $generated_at = generateDate() ?></p>
            </div>
        </div>
    </div>
</body>
</html>