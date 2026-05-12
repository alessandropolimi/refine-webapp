<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /auth/login.php
 * ROLE: User Authentication Interface (Frontend)
 * COMPONENT: Login Manager
 * * DESCRIPTION:
 * This script displays the login form. It allows users to authenticate 
 * and access their personalized dashboard or administrative tools.
 * * Key UX Features:
 * 1. Feedback Loop: Displays success or error messages (e.g., "Invalid credentials") 
 * passed back from the authentication processor via URL parameters.
 * 2. Input Persistence: Automatically repopulates the username field if an 
 * attempt fails, reducing friction for the user.
 * 3. Security UI: Includes a JavaScript-driven toggle to show/hide the 
 * password for better accessibility.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../init.php";

?>

<div style="width:100%; height:80px; border-bottom:solid 2px #ccc; display:flex; align-items:center;">
    <p style="font-size:28px;" class="fontbold">Log In</p>
</div>

<br><br>

<?php 
/**
 * ERROR/INFO MESSAGING
 * Fetches status messages from the backend processor (login/login.php).
 */
if(isset($_GET["msg"])) {
    echo '<p style="color:red;">' . htmlspecialchars($_GET["msg"]) . '</p><br>'; 
}
?>



<form action="/auth/login/login.php" method="post">
    
    <label>Username:</label><br>
    <input type="text" name="username" style="width:300px;" 
           value="<?php echo (isset($_GET["username"]) ? htmlspecialchars($_GET["username"]) : ""); ?>" required>
    
    <br><br>

    <label>Password:</label><br>
    <div style="position:relative; display:inline-block;">
        <input type="password" name="password" style="width:300px;" required>
        <p onclick="password(this)" style="cursor:pointer; position:absolute; right:8px; top:10px; font-size:14px;">Show</p>
    </div>
    
    <br><br>

    <button type="submit">Log In</button>
</form>

<br><br>

<a href="?form=signup" style="cursor:pointer;">Don't have an account? Sign Up</a>