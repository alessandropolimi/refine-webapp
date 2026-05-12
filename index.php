<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /index.php
 * ROLE: Main Entry Point / Landing Page
 * COMPONENT: Webapp Root
 * * DESCRIPTION:
 * This is the primary gateway of the application. It serves as a dynamic 
 * toggle between the Login and Signup interfaces. It also performs 
 * an initial session check: if a user is already authenticated, 
 * they are automatically redirected to their dashboard.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ========================================================================
 * Inclusion of global constants, database credentials, and HTML wrappers.
 */
include_once "init.php";

/* * ========================================================================
 * AUTHENTICATION GUARD & ROUTING
 * ========================================================================
 * This block checks if a session variable 'user_username' exists.
 * - IF EMPTY: The user is a guest; proceed to display the auth forms.
 * - IF NOT EMPTY: The user is logged in; redirect to the User Manager module.
 */
if(empty($_SESSION["user_username"])) {
    
    /* Initialize the HTML document structure (Head, Styles, Cytoscape) */
    html_start();
?>

<?php 
/* Main layout container: full-screen centered flexbox */
?>
<div style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center;">    

    <?php 
    /* Authentication Box: White container with soft shadow for UI depth */
    ?>
    <div class="sbn" style="border-radius:10px; padding:40px; box-shadow:0px 0px 40px #00000033;">

        <?php 
        /**
         * FORM ROUTING LOGIC
         * We check the URL "form" parameter to decide which sub-component to load.
         * Default behavior: Show Login Manager.
         */
        if(!isset($_GET["form"]) || $_GET["form"] !== "signup") { 
            
            /* Load the Login Manager component */
            include_once "auth/login.php";
            
        } else {
            
            /* Load the Signup Manager component if ?form=signup is detected */
            include_once "auth/signup.php";
        } 
        ?>
        
        <?php 
        /**
         * CLIENT-SIDE INTERFACE LOGIC
         * This script toggles the visibility of the password field.
         * It interacts with the parent node of the 'Show/Hide' button 
         * to switch the input type between 'password' and 'text'.
         */
        ?>
        <script>
            function password(t) {
                if(t.innerHTML === "Hide") {
                    t.innerHTML = "Show"; 
                    t.parentNode.firstElementChild.type = "password";
                } else {
                    t.innerHTML = "Hide"; 
                    t.parentNode.firstElementChild.type = "text";
                }
            }
        </script>

    </div>
    
    <?php /* Spacer for visual balance at the bottom of the page */ ?>
    <div style="height:100px;"></div>
</div>

<?php
    /* Close HTML tags and container */
    html_end();

} else {
    
    /**
     * AUTHENTICATED REDIRECTION
     * If the session is active, the script terminates here and sends the 
     * browser header to the /user/ directory.
     */
    header("Location: /user"); 
    exit();
}
?>