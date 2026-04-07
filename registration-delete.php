<?php
    session_start();
    include_once('dbcon.php');

    if (isset($_GET['idNum'])) {
        $idNum = $_GET['idNum'];

        $stmt = $con->prepare('DELETE FROM ict.registration WHERE idNum = ?');
        $stmt->bind_param('s', $idNum);
        $stmt->execute();

        $_SESSION['msg'] = 'Student deleted successfully.';
        header('Location: registration.php');
    }
?>