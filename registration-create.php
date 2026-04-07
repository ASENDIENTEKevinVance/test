<?php
    session_start();
    include_once('dbcon.php');

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $idNum = $_POST['idNum'];
        $campus = $_POST['campus'];
        $studFName = $_POST['studFName'];
        $studLName = $_POST['studLName'];
        $amountPaid = $_POST['amountPaid'];
        $attended = 'No'; 

        $stmt = $con->prepare('INSERT INTO ict.registration (idNum, campus, studFName, studLName, amountPaid, attended) VALUES(?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssds', $idNum, $campus, $studFName, $studLName, $amountPaid, $attended);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = 'Student registered successfully.';
            header('Location: registration.php');
        } else {
            $_SESSION['msg'] = 'Failed to register successfully.';
        }
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="vh-100 d-flex justify-content-center align-items-center">
            <form action="registration-create.php" method="POST">
                <div class="form-group">
                    <label>ID #</label>
                    <input type="text" class="form-control" name="idNum">
                </div>
                <div class="form-group">
                    <label>Campus</label>
                    <select class="form-select" name="campus" required>
                        <option value="" disabled selected>Select a campus...</option>
                        <option value="Main Campus">Main Campus</option>
                        <option value="Banilad">Banilad</option>
                        <option value="LM">LM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="studFName">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="studLName">
                </div>
                <div class="form-group">
                    <label>Amount Paid</label>
                    <input type="number" class="form-control" name="amountPaid">
                </div>
                <button type="submit" class="button mt-4">Register Student</button>
            </form>
            
        </div>
    </div>
</body>
</html>