<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /auth/logout.php
 * ROLE: Session Termination & Identity Purge
 * COMPONENT: Authentication Manager
 * * DESCRIPTION:
 * This script handles the secure exit of a user from the platform. 
 * It performs a precise teardown of the session state by unsetting 
 * specific identifiers. This prevents session fixation or unauthorized 
 * access from shared devices after the user has finished their task.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../init.php";

/**
 * SESSION DECONSTRUCTION
 * We explicitly unset the core session keys used throughout the application.
 * This effectively logs the user out and clears the temporary survey cache.
 */

/* Identity Purge */
unset($_SESSION["user_username"]);           // Primary user identifier
unset($_SESSION["is_admin"]);                // Administrative privilege flag

/* Survey Context Purge */
unset($_SESSION["current_survey_id"]);       // The ID of the active survey session
unset($_SESSION["current_survey_video_ids"]);// The queue of videos for the current survey
unset($_SESSION["current_survey_video_id"]); // The specific video currently being evaluated

/**
 * REDIRECTION
 * Once the session state is cleared, the user is redirected back to the 
 * root landing page (login/signup view).
 */
header('Location: /'); 
exit;

?>