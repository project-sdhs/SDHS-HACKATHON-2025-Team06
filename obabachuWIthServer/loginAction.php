<?php
require_once "db.php";

$type = $_POST["action"] ?? '';

if ($type == "login") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if the user exists
    $user = DB::fetch("SELECT * FROM user WHERE name = '$username' AND psw = '$password'");

    if ($user) {

        $_SESSION["ss"] = $user;

        echo "<script>alert('로그인 성공!'); window.location.href = '../index.php';</script>";
    } else {
        echo "<script>alert('아이디 또는 비밀번호가 잘못되었습니다.'); history.back();</script>";
    }
} elseif ($type == "register") {

    if (isset($_POST['username']) && isset($_POST['password'])) {

        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        // Check if the user already exists
        $existingUser = DB::fetch("SELECT * FROM user WHERE name = '$username'");

        if ($existingUser) {
            echo "<script>alert('이미 존재하는 아이디입니다.'); history.back();</script>";
            exit;
        }

        // Insert new user into the database
        $query = "INSERT INTO user (name, psw, email, phone) VALUES ('$username', '$password', '$email', '$phone')";

        if (DB::exec($query)) {
            echo "<script>alert('회원가입이 완료되었습니다.'); window.location.href = '../Survey/survery.html';</script>";
        } else {
            echo "<script>alert('회원가입에 실패했습니다. 다시 시도해주세요.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('모든 필드를 입력해주세요.'); history.back();</script>";

    }
}