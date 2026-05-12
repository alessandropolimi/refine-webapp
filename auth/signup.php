<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /auth/signup.php
 * ROLE: User Registration Interface (Frontend)
 * COMPONENT: Signup Manager
 * * DESCRIPTION:
 * This script displays the registration form. It captures demographic 
 * and authentication data from new participants. 
 * * Key UX Features:
 * 1. Stateful Inputs: Uses $_GET parameters to repopulate fields if the 
 * registration fails (e.g., password mismatch or username taken).
 * 2. Security: Implements basic HTML5 validation and specific username constraints.
 * 3. Accessibility: Clear labels and "Show Password" toggles.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../init.php";

?>

<div style="width:100%; height:80px; border-bottom:solid 2px #ccc; display:flex; align-items:center;">
    <p style="font-size:28px;" class="fontbold">Sign Up</p>
</div>

<br><br>

<?php 
/**
 * ERROR MESSAGING
 * Displays feedback from the registration processor (signup/signup.php).
 */
if(isset($_GET["msg"])) {
    echo '<p style="color:red;">' . htmlspecialchars($_GET["msg"]) . '</p><br>'; 
}
?>



<form action="/auth/signup/signup.php" method="post">
    
    <label>Username (*):</label><br>
    <input type="text" name="username" style="width:300px;" 
           value="<?php echo (isset($_GET["username"]) ? htmlspecialchars($_GET["username"]) : ""); ?>" required>
    <br>
    <p style="font-size:12px;">Max 64 characters.<br>Only lowercase letters, numbers, "." and "_"</p>
    
    <br><br>

    <label>Course of study (*):</label><br>
    <input type="text" name="course" style="width:300px;" 
           value="<?php echo (isset($_GET["course"]) ? htmlspecialchars($_GET["course"]) : ""); ?>" required>
    
    <br><br>

    <label>Age:</label><br>
    <input type="text" name="age" style="width:100px;" 
           value="<?php echo (isset($_GET["age"]) ? htmlspecialchars($_GET["age"]) : ""); ?>">
    
    <br><br>

    <label>Gender:</label><br>
    <select name="gender">
        <option value="m" <?php echo (isset($_GET["gender"]) && $_GET["gender"] === "m" ? "selected" : ""); ?>>Male</option>
        <option value="f" <?php echo (isset($_GET["gender"]) && $_GET["gender"] === "f" ? "selected" : ""); ?>>Female</option>
        <option value="o" <?php echo (isset($_GET["gender"]) && $_GET["gender"] === "o" ? "selected" : ""); ?>>Other</option>
        <option value="0" <?php echo (!isset($_GET["gender"]) || ($_GET["gender"] !== "m" && $_GET["gender"] !== "f" && $_GET["gender"] !== "o") ? "selected" : ""); ?>>Prefer not to say</option>
    </select>
    
    <br><br>

    <label>Password (*):</label><br>
    <div style="position:relative; display:inline-block;">
        <input type="password" name="password" style="width:300px;" required>
        <p onclick="password(this)" style="cursor:pointer; position:absolute; right:8px; top:10px; font-size:14px;">Show</p>
    </div>
    
    <br><br>

    <?php /*
    <label>Repeat Password (*):</label><br>
    <div style="position:relative; display:inline-block;">
        <input type="password" name="password_repeat" style="width:300px;" required>
        <p onclick="password(this)" style="cursor:pointer; position:absolute; right:8px; top:10px; font-size:14px;">Show</p>
    </div>

    <br><br>
    */ ?>
    
    <a href="/legal/terms.php" target="_blank">Terms and conditions</a>
    
    <br><br>
    
    <button type="submit">Sign Up</button>
</form>

<br><br>

<a href="?form=login" style="cursor:pointer;">Already have an account? Log In</a>