<?php
    session_start();
    include_once('dbcon.php');

    $campus = '';
    if (isset($_GET['campus'])) {
        $campus = $_GET['campus'];
    }

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $stmt = $con->prepare('SELECT * FROM ict.registration WHERE campus = ?');
        $stmt->bind_param('s', $campus);
        $stmt->execute();
        $records = $stmt->get_result();

        $stmtCampusTotal = $con->prepare('SELECT SUM(amountPaid) AS total FROM ict.registration WHERE campus = ?');
        $stmtCampusTotal->bind_param('s', $campus);
        $stmtCampusTotal->execute();
        $campusTotalResult = $stmtCampusTotal->get_result();
        $campusTotalRow = $campusTotalResult->fetch_assoc();
        $campusTotalAmount = $campusTotalRow['total'] ?? 0;

        $stmtTotal = $con->prepare('SELECT SUM(amountPaid) AS total FROM ict.registration');
        $stmtTotal->execute();
        $totalResult = $stmtTotal->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $registrantsTotal = $totalRow['total'];
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Report</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            <?= $_SESSION['msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
        <?php unset($_SESSION['msg']); endif; ?>
        <div class="card p-5 w-100">
            <div class="text-end">
                <a href="index.php">Back to Menu</a>
            </div>
            <div class="text-center mb-5">
                <form action="campus-report.php" method="GET">
                    <div class="mb-3">
                        <input type="checkbox" name="campus" value="Banilad" <?= ($campus === "Banilad") ? 'checked' : '' ?>>
                        <label>Banilad</label>
                        <input type="checkbox" name="campus" value="Main Campus" <?= ($campus === "Main Campus") ? 'checked' : '' ?>>
                        <label>Main Campus</label>
                    </div>

                    <div>
                        <button class="btn btn-block border border-1 fs-5" type="submit">GENERATE REPORT</button>
                    </div>
                </form>
            </div>

            <?php if ($records->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <td>ID #</td>
                        <td>Name</td>
                        <td>Campus</td>
                        <td>Amount</td>
                        <td>Attended</td>
                    </tr>
                </thead>
                <?php while($record = $records->fetch_assoc()): ?>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($record['idNum']) ?? ''; ?></td>
                        <td><?= htmlspecialchars($record['studLName']) ?? '' ?>, <?= htmlspecialchars($record['studFName']) ?? '' ?></td>
                        <td><?= htmlspecialchars($record['campus']) ?? '' ?></td>
                        <td><?= $record['amountPaid'] ?? '' ?></td>
                        <td><?= $record['attended'] ?? '' ?></td>
                    </tr>
                </tbody>
                <?php endwhile; ?>
            </table>
            <div>
                <p># of Registrants | Total Collection: <?= $registrantsTotal ?></p>
                <p># of Attendees | Total Generated: <?= $campusTotalAmount ?></p>
            </div>
            <?php else: ?>
            <?php endif; ?>
            </div>
    </div>
</body>
</html>