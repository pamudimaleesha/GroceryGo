<?php

session_start();

include_once '../config/db.php';

$email = $_POST['email'];
$password = $_POST['password'];


$sql = "SELECT * FROM users WHERE email = :email";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'email' => $email
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if($user){

    if($password == $user['password']){

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];


        if($user['role'] == 'admin'){

            header("Location: ../views/admin/dashboard.php");
            exit();

        }else{

            header("Location: ../index.php");
            exit();

        }


    }else{

        header("Location: ../views/signin.php?login=error");
        exit();

    }


}else{

    header("Location: ../views/signin.php?login=error");
    exit();

}

?>