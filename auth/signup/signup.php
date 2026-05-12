<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /auth/signup/signup.php
 * ROLE: Signup Logic & Data Validation
 * COMPONENT: Authentication Manager
 * * DESCRIPTION:
 * This script processes the raw POST data from the registration form.
 * It performs three main phases:
 * 1. Validation: Sanitizes and checks constraints (length, characters, types).
 * 2. Security: Hashes passwords using modern PHP standards.
 * 3. Persistence: Atomically inserts the user into the database and initializes the session.
 */

include_once "../../init.php";

/**
 * HELPER: build_header
 * Constructs a redirect URL that preserves user input (except password)
 * so the user doesn't have to re-type everything after a validation error.
 */
function build_header($msg) {
    return header("Location: /?form=signup".
    "&username=".urlencode($_POST['username'] ?? "").
    "&course=".urlencode($_POST['course'] ?? "").
    "&age=".urlencode($_POST['age'] ?? "").
    "&gender=".urlencode($_POST['gender'] ?? "").
    "&msg=".urlencode($msg));
}

/* * ========================================================================
 * PHASE 1: INTEGRITY CHECK & SANITIZATION
 * ======================================================================== */

if(empty($_POST['username']) || empty($_POST['course']) || empty($_POST['password'])) {
    build_header("Missing required credentials."); 
    exit();
}

/* Cast and normalize data types */
$username = strtolower(trim(strval($_POST["username"])));
$course   = trim(strval($_POST["course"]));
$age      = isset($_POST["age"]) ? intval($_POST["age"]) : 0;
$gender   = isset($_POST["gender"]) ? strtolower(strval($_POST["gender"])) : "0";
$password = $_POST["password"];

/* * ========================================================================
 * PHASE 2: BUSINESS LOGIC CONSTRAINTS
 * ======================================================================== */

/* Regex check for username: Alphanumeric + dots/underscores only */
if(strlen($username) > 64) { build_header("Username is too long."); exit(); }
if(!preg_match("/^[a-z0-9._]+$/", $username)) { build_header("Username has invalid characters."); exit(); }

if(strlen($password) > 64) { build_header("Password is too long."); exit(); }
if(strlen($course) > 128)  { build_header("Course name is too long."); exit(); }
if($age < 0 || $age > 120)  { build_header("Please enter a valid age."); exit(); }

/* Validate against allowed enum-like values for gender */
$valid_genders = ["0", "m", "f", "o"];
if(!in_array($gender, $valid_genders)) { build_header("Invalid gender selection."); exit(); }

/* * ========================================================================
 * PHASE 3: DATABASE PERSISTENCE (THE "SIGNUP" ACTION)
 * ======================================================================== */



try {
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* Prepare Statement: Prevents SQL Injection by separating query logic from data */
    $stmt = $pdo->prepare("INSERT INTO `user` (`username`, `course`, `age`, `gender`, `password`) VALUES (?, ?, ?, ?, ?)");

    /* Bind values with explicit types */
    $stmt->bindValue(1, $username, PDO::PARAM_STR);
    $stmt->bindValue(2, $course,   PDO::PARAM_STR);
    $stmt->bindValue(3, $age,      PDO::PARAM_INT);
    $stmt->bindValue(4, $gender,   PDO::PARAM_STR);

    /**
     * PASSWORD SECURITY:
     * password_hash() uses the current industry standard (Bcrypt by default).
     * This creates a salted hash that is secure even if the database is leaked.
     */
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bindValue(5, $hashed_password, PDO::PARAM_STR); 

    if($stmt->execute()) {
        /* Success: Establish login session and move to user area */
        $_SESSION["user_username"] = $username;
        header("Location: /user"); 
        exit();
    } else {
        build_header("Registration failed. Please try again."); 
        exit();
    }
}
catch(PDOException $e) {
    /* Handle Duplicate Username error (Primary Key Violation) */
    if($e->getCode() == 23000) {
        build_header("Username already exists.");
    } else {
        build_header("Database error occurred.");
    }
    exit();
}

?>