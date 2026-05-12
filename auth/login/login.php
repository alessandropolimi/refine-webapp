<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /auth/login/login.php
 * ROLE: Authentication Processor & Session Initializer
 * COMPONENT: Authentication Manager
 * * DESCRIPTION:
 * This script serves as the backend controller for user authentication.
 * It performs a multi-step verification process:
 * 1. Sanitization: Normalizes input and enforces character limits.
 * 2. Lookup: Queries the 'user' table using prepared statements to prevent SQLi.
 * 3. Verification: Uses PHP's password_verify() to check Bcrypt/Argon2 hashes.
 * 4. Escalation: Grants administrative privileges if the user is the superuser.
 */

include_once "../../init.php";

/* * ========================================================================
 * PHASE 1: INPUT VALIDATION & INTEGRITY
 * ======================================================================== */

if(empty($_POST['username']) || empty($_POST['password'])) {
    header("Location: /?form=login&username=".urlencode($_POST['username'] ?? "")."&msg="."Empty credentials."); 
    exit();
}

/* Normalize username and capture raw password */
$username = strtolower(trim(strval($_POST["username"])));
$password = $_POST["password"];

/* Security Constraints */
if(strlen($username) > 64) { 
    header("Location: /?form=login&username=".urlencode($username)."&msg="."Username is too long."); exit(); 
}
if(!preg_match("/^[a-z0-9._]+$/", $username)) { 
    header("Location: /?form=login&username=".urlencode($username)."&msg="."Username has invalid characters."); exit(); 
}
if(strlen($password) > 64) { 
    header("Location: /?form=login&username=".urlencode($username)."&msg="."Password is too long."); exit(); 
}

/* * ========================================================================
 * PHASE 2: DATABASE LOOKUP & PASSWORD VERIFICATION
 * ======================================================================== */



try {
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* Fetch user data using a prepared statement to block SQL Injection */
    $stmt = $pdo->prepare("SELECT * FROM `user` WHERE `username` = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /* Case A: User record does not exist */
    if(!$user) {
        header("Location: /?form=login&username=".urlencode($username)."&msg="."No user found."); 
        exit();
    }

    /**
     * PASSWORD VERIFICATION:
     * password_verify() retrieves the salt from the stored hash and performs 
     * a secure, timing-attack-resistant comparison.
     */
    if(!password_verify($password, $user["password"])) {
        header("Location: /?form=login&username=".urlencode($username)."&msg="."Password is wrong."); 
        exit();
    }

    /* * ========================================================================
     * PHASE 3: SESSION INITIALIZATION & PRIVILEGE MANAGEMENT
     * ======================================================================== */

    /* Establish identity in the session */
    $_SESSION["user_username"] = $username;

    /* Check for Administrative Escalation */
    if($username === "admin") {
        $_SESSION["is_admin"] = true;
    } else {
        /* Preemptive security: ensure standard users do not inherit admin flags */
        unset($_SESSION["is_admin"]);
    }

    /* Redirect to user dashboard */
    header("Location: /user"); 
    exit();

} catch(PDOException $e) {
    /* Generic error to prevent database schema leaks */
    header("Location: /?form=login&username=".urlencode($username)."&msg="."System error. Please try again later."); 
    exit();
}

?>