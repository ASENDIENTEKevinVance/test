<?php
    session_start();
    include_once('dbcon.php');

    $winner = '';
    if (!isset($_SESSION['winner'])) {
        if (isset($_POST['reveal'])) {
            $stmt = $con->prepare('SELECT * FROM ict.registration ORDER BY RAND() LIMIT 1');
            $stmt->execute();
            $results = $stmt->get_result();
            $winner = $results->fetch_assoc();

            $_SESSION['winner'] = $winner;
        }
    } else {
        $winner = $_SESSION['winner'];
    }

    unset($_SESSION['winner']);
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raffle</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="card p-5 w-100 text-center">
            <div class="text-end">
                <a href="index.php">Back to Menu</a>
            </div>
            <form method="post">
                <button type="submit" name="reveal" class="btn btn-block border border-1 mb-3 fs-5" >Reveal The Lucky Winner</button>
            </form>

            <?php if ($winner): ?>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>Name</td>
                            <td>Campus</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($winner['idNum']) ?></td>
                            <td><?= htmlspecialchars($winner['studFName']) ?>, <?= htmlspecialchars($winner['studLName']) ?></td>
                            <td><?= htmlspecialchars($winner['campus']) ?></td>
                        </tr>
                    </tbody>
                </table>
                <h1>CONRATULATIONS!!! </h1>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>