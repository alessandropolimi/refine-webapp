<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/index.php
 * ROLE: Survey Controller & Router
 * COMPONENT: Survey Manager
 * * DESCRIPTION:
 * This file acts as the primary controller for the survey experience. It handles:
 * 1. User authentication and Admin impersonation checks.
 * 2. Routing between survey initialization (Step 0) and active survey steps.
 * 3. Validation of Step and Page parameters to ensure session integrity.
 * 4. Database synchronization for loading specific survey instances.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION & ACCESS CONTROL
 * ======================================================================== */
include_once "../init.php";

/* * Security Guard: Ensure the user is authenticated before accessing surveys. */
if(empty($_SESSION["user_username"])) {
    header("Location: /"); 
    exit();
}

/* * Parameter Validation: The 'step' parameter is mandatory for routing. */
if(!isset($_GET['step'])) {
    header("Location: /"); 
    exit();
}

/**
 * STATE VARIABLES
 * $STEP: The current progress in the sequence of videos (0 = New Survey).
 * $PAGE: The current view within a single step (1 = Initial, 2 = Graph/Interaction, 3 = Final).
 */
$STEP = intval($_GET['step']);

/* * Integrity Check: If not a new survey (Step > 0), a page number must be provided. */
if($STEP != 0 && !isset($_GET['page'])) {
    header("Location: /"); 
    exit();
}

$PAGE = intval($_GET['page']);
if($PAGE < 1 || $PAGE > 3) $PAGE = 1;

/* * ========================================================================
 * ADMINISTRATIVE OVERRIDE
 * ======================================================================== */
if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
    if(!empty($_GET["user"])) {
        /* Allow admin to view the survey through the lens of a specific user */
        $_SESSION["user_username"] = strtolower(htmlspecialchars(urldecode($_GET["user"]), ENT_QUOTES, "UTF-8"));
    }
}

/* * ========================================================================
 * SURVEY INITIALIZATION & DATA RETRIEVAL
 * ======================================================================== */

/* * Load a specific survey by ID if provided in the URL */
if(!empty($_GET["id"])) {
    $currSurveyId = intval($_GET["id"]);

    try {
        /**
         * FETCH SURVEY STRUCTURE
         * We retrieve the ordered list of video IDs associated with this survey.
         * This sequence defines the "Steps" for the current session.
         */
        $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
        $stmt = $pdo->prepare("SELECT video_id FROM `survey_step` WHERE `user_username` = ? AND `survey_id` = ?");
        $stmt->bindValue(1, $_SESSION["user_username"], PDO::PARAM_STR);
        $stmt->bindValue(2, $currSurveyId, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($result)) {
            /* If no data exists for this ID, force the creation of a new survey */
            header("Location: /survey?step=0"); 
            exit();
        } else {
            /**
             * SESSION PERSISTENCE
             * Store the survey ID and the full sequence of video IDs in the session
             * to avoid redundant database hits during step navigation.
             */
            $_SESSION["current_survey_id"] = $currSurveyId;
            $_SESSION["current_survey_video_ids"] = array_column($result, 'video_id');
            
            /* If an ID was provided but step was 0, default to the first step/page */
            if($STEP == 0) { $STEP = 1; $PAGE = 1; }
        }
    }
    catch(PDOException $e) {
        header("Location: /survey?step=0"); 
        exit();
    } 
}

/* * ========================================================================
 * ROUTING LOGIC
 * ======================================================================== */

if($STEP == 0) {
    /**
     * CASE: NEW SURVEY CREATION
     * Includes the logic to generate a new survey entry and randomize video selection.
     */
    include_once "new.php";

} else if(
    /* Validate that session data exists and requested step is within bounds */
    empty($_SESSION["current_survey_id"]) || 
    empty($_SESSION["current_survey_video_ids"]) || 
    $STEP < 0 || 
    $STEP > count($_SESSION["current_survey_video_ids"])
) {
    /* Critical Error: Session mismatch or out-of-bounds step; restart survey process */
    header("Location: /survey?step=0"); 
    exit();
}

/**
 * CONTEXT SETTING
 * Identify the specific video ID for the current step.
 * Index is calculated as $STEP - 1 because UI steps are 1-based, 
 * while the video array is 0-based.
 */
$_SESSION["current_survey_video_id"] = $_SESSION["current_survey_video_ids"][$STEP-1];

/* * ========================================================================
 * PAGE RENDERING
 * ======================================================================== */

/* Start the HTML document (Head, CSS, Meta) */
html_start();

/**
 * SURVEY INTERFACE (CORE COMPONENT)
 * Loads the form logic, including Cytoscape.js for graph interaction,
 * based on the current $STEP and $PAGE context.
 */
include_once "form.php";

/* Close the HTML document */
html_end(); 

?>