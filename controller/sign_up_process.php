<?php

include_once '../config/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];


if ($password === $confirmPassword) {

    // Password hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Default role for new users
    $role = "user";


    $sql = "INSERT INTO users (name, email, password, role) 
            VALUES (:name, :email, :password, :role)";


    $stmt = $conn->prepare($sql);


    $result = $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role
    ]);


    if ($result) {

        header("Location: ../views/signin.php?success=Account created successfully");
        exit();

    } else {

        echo "<script>alert('Error creating account. Please try again.');</script>";
        echo "<script>window.location.href='../views/signup.php';</script>";

    }


} else {

    echo "<script>alert('Passwords do not match!');</script>";
    echo "<script>window.location.href='../views/signup.php';</script>";

}

?>