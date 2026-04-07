<?php
session_start();
include_once('dbcon.php');

if (isset($_GET['idNum'])) {
    $idNum = $_GET['idNum'];

    $stmt = $con->prepare('SELECT * FROM registration WHERE idNum = ? AND attended = "No"');
    $stmt->bind_param('s', $idNum);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt = $con->prepare('UPDATE registration SET attended = "Yes" WHERE idNum = ?');
        $stmt->bind_param('s', $idNum);
        $stmt->execute();

        $_SESSION['msg'] = 'ATTENDANCE SUCCESSFULLY RECORDED';
        header('Location: attendance.php');
    } else {
        $_SESSION['msg'] = 'ATTENDANCE ALREADY RECORDED';
        header('Location: attendance.php');
    }

    $stmt->close();
}
