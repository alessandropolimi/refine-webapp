<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/new.php
 * ROLE: New Survey Generator & Database Seeder
 * COMPONENT: Survey Manager
 * * DESCRIPTION:
 * This script is responsible for initializing a new survey session. It:
 * 1. Creates a main entry in the 'survey' table.
 * 2. Implements a stratified sampling algorithm to pick random videos from a JSON dataset.
 * 3. Populates the 'survey_step' table with the generated sequence.
 * 4. Initializes session variables and redirects the user to the first step.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../init.php";

try {
    /* Establish Database Connection */
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    
    /**
     * STEP 1: CREATE SURVEY HEADER
     * Insert the primary survey record linked to the current user and date.
     */
    $stmt = $pdo->prepare("INSERT INTO survey (user_username, date) VALUES (:user_username, :date)");
    $stmt->bindValue(':user_username', $_SESSION["user_username"], PDO::PARAM_STR);
    $stmt->bindValue(':date', date('Y-m-d'), PDO::PARAM_STR);
    $stmt->execute();
    
    /* Retrieve the unique ID of the newly created survey */
    $survey_id = $pdo->lastInsertId();
    
    /**
     * STEP 2: STRATIFIED RANDOM SAMPLING LOGIC
     * To ensure a diverse selection of videos, we divide the total dataset 
     * into equal segments (strata) and pick one random video from each.
     */
    $segment_size = floor(_TOTAL_VIDEOS_ / _NUM_VIDEOS_);
    $ranges = [];
    $current_start = _MIN_INDEX_VIDEO_;
    
    /* Calculate the numerical boundaries for each segment */
    for ($i = 0; $i < _NUM_VIDEOS_; $i++) {
        $current_end = $current_start + $segment_size - 1; 
        
        /* Ensure the last segment covers any remaining videos due to rounding */
        if ($i == _NUM_VIDEOS_ - 1) {
            $current_end = _MAX_INDEX_VIDEO_;
        }
        
        $ranges[] = [$current_start, $current_end];
        $current_start = $current_end + 1;
    }

    /**
     * STEP 3: VIDEO SELECTION
     * Loading the master graph dataset from a optimized JSON file.
     * We select one random 'id' from each of the previously defined ranges.
     */
    $dataArray = json_decode(file_get_contents('../assets/graph.min.json'), true);
    $video_ids = [];
    
    for($i = 0; $i < _NUM_VIDEOS_; $i++) {
        /* Generate a cryptographically secure-ish random index within the strata */
        $randomIndex = mt_rand($ranges[$i][0], $ranges[$i][1]);
        $video_ids[] = $dataArray[$randomIndex]['id'];
    }

    /**
     * STEP 4: BULK INSERTION OF STEPS
     * Prepare a multi-row INSERT query to seed all survey steps in a single execution.
     * This improves performance by reducing database round-trips.
     */
    $q = "INSERT INTO survey_step (user_username, survey_id, step, video_id) VALUES ";
    for ($i = 0; $i < _NUM_VIDEOS_; $i++) {
        $q .= "(?, ?, ?, ?)";
        if($i < _NUM_VIDEOS_-1) $q .= ", "; else $q .= ";";
    }

    $stmt = $pdo->prepare($q);

    /* * Parameter Binding Loop:
     * We map four variables per step (username, survey_id, step_index, video_id).
     */
    $i = 1; 
    $j = 0;
    while($i <= _NUM_VIDEOS_*4 && $j < _NUM_VIDEOS_) {
        $stmt->bindValue($i, $_SESSION["user_username"], PDO::PARAM_STR);
        $stmt->bindValue($i+1, $survey_id, PDO::PARAM_INT);
        $stmt->bindValue($i+2, $j+1, PDO::PARAM_INT);
        $stmt->bindValue($i+3, $video_ids[$j], PDO::PARAM_STR);
        $i += 4;
        $j += 1;
    }

    $stmt->execute();

    /**
     * STEP 5: SESSION SYNCHRONIZATION
     * Update the session with the new survey context before starting.
     */
    $_SESSION["current_survey_id"] = $survey_id;
    $_SESSION["current_survey_video_ids"] = $video_ids;
    
    /* Redirect to the starting page of the newly generated survey */
    header("Location: /survey?step=1&page=1"); 
    exit();

} catch (\PDOException $e) {
    /**
     * ERROR HANDLING
     * If the database transaction fails, clear potential partial state 
     * and return the user to the dashboard.
     */
    header("Location: /user"); 
    exit();
}

?>