<?php
    session_start();
    include_once('dbcon.php');

    $idNum = '';
    if (isset($_GET['idNum'])) {
        $idNum = $_GET['idNum'];
    }
    
    if($_SERVER["REQUEST_METHOD"] === "GET") {
        $stmt = $con->prepare('SELECT * FROM registration WHERE idNum = ?');
        $stmt->bind_param('s', $idNum);
        $stmt->execute();
        $records = $stmt->get_result();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
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
            <form action="" method="GET">
                <label>Input ID #:</label>
                <input type="text" name="idNum" value="<?= $idNum ?>">
                <a href="index.php">Back to Menu</a>
            </form>

            <?php if ($records->num_rows > 0): ?>
            <div class="mt-5">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td>ID #</td>
                            <td>Name</td>
                            <td>Campus</td>
                            <td>Amount</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($record = $records->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['idNum']); ?></td>
                                <td><?= htmlspecialchars($record['studLName']); ?>, <?= htmlspecialchars($record['studFName']); ?></td>
                                <td><?= htmlspecialchars($record['campus']); ?></td>
                                <td><?= htmlspecialchars($record['amountPaid']); ?></td>
                                <td><a href="set-attendance.php?idNum=<?= htmlspecialchars($record['idNum']); ?>">Attended</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif (!empty($idNum)): ?>
            <div class="text-center mt-5">
                <h4>ID # IS NOT YET REGISTERED</h4>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>