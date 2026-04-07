<?php
    session_start();
    include_once('dbcon.php');

    $stmt = $con->prepare('SELECT * FROM ict.registration');
    $stmt->execute();
    
    $registrations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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
            <table class="table table-bordered">
                <div class="text-center mb-5">
                    <a href="registration-create.php">Register a Student Here</a> |
                    <a href="index.php">Back to Menu</a>
                </div>

                <?php if ($registrations->num_rows > 0): ?>
                <thead>
                    <tr>
                        <td>ID #</td>
                        <td>Name</td>
                        <td>Campus</td>
                        <td>Amount</td>
                        <td>Actions</td>
                    </tr>
                </thead>
                <?php while($registration = $registrations->fetch_assoc()): ?>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($registration['idNum']) ?? ''; ?></td>
                        <td><?= htmlspecialchars($registration['studLName']) ?? '' ?>, <?= htmlspecialchars($registration['studFName']) ?? '' ?></td>
                        <td><?= htmlspecialchars($registration['campus']) ?? '' ?></td>
                        <td><?= $registration['amountPaid'] ?? '' ?></td>
                        <td>
                            <a href="">Edit</a>
                            <a href="registration-delete.php?idNum=<?= $registration['idNum'] ?>">Delete</a>
                        </td>
                    </tr>
                </tbody>
                <?php endwhile; ?>
                <?php else: ?>
                
                <?php endif; ?>
            </table>
        </div>
    </div>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>