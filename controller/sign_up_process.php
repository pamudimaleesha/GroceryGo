<?php

include_once '../config/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

if ($password === $confirmPassword) {
    // Securely hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashedPassword')";
    $result = $conn->query($sql);

    if ($result) {
        header("Location: ../views/signin.php?success=Account created successfully");
        exit();
    } else {
        echo "<script>alert('Error creating account. Please try again.');</script>";
        echo "<script>window.location.href = '../views/signup.php';</script>";
    }

} else {
    echo "<script>alert('Passwords do not match!');</script>";
    echo "<script>window.location.href = '../views/signup.php';</script>";
}
?>
