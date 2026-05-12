<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/getdata.php
 * ROLE: Survey State Retrieval (Data Receiver)
 * COMPONENT: Survey Form Manager
 * * DESCRIPTION:
 * This script is responsible for synchronizing the UI with the database. 
 * When a user navigates between survey pages or returns to a previous step, 
 * this file fetches their existing votes, comments, and graph modifications.
 * This ensures a seamless "stateful" experience where no data is lost during 
 * navigation.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

/**
 * DATA STRUCTURE: $DBRESULTS
 * Initializing the container for qualitative data (ratings and text).
 * These fields correspond to Page 1 (Ex-ante) and Page 3 (Ex-post) feedback.
 */
$DBRESULTS = [
    /* Page 1: Initial Perception */
    "answer_rely_before"                => null,
    "answer_rely_before_comment"        => null,
    "answer_trustmeter_before"          => null,
    "answer_trustmeter_before_comment"  => null,
    
    /* Page 3: Post-Interaction Feedback */
    "answer_rely_answer"                => null,
    "answer_rely_answer_comment"        => null,
    "answer_rely_graph"                 => null,
    "answer_rely_graph_comment"         => null,
    "answer_comment"                    => null,
    "answer_trustmeter_after"           => null,
    "answer_trustmeter_after_comment"   => null
];

/**
 * DATA STRUCTURE: $DBGRAPHS
 * An array to hold specific modifications made to the Cytoscape graph elements.
 * Each entry tracks which element in which frame was changed or deleted.
 */
$DBGRAPHS = [];

try {
    /* Establish secure PDO connection */
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);

    /**
     * QUERY 1: FETCH STEP PROGRESS
     * Retrieves all recorded answers for the current user's specific survey step.
     */
    $stmt = $pdo->prepare("SELECT * FROM `survey_step` WHERE `user_username` = ? AND `survey_id` = ? AND `step` = ?");
    
    $stmt->bindValue(1, $_SESSION["user_username"], PDO::PARAM_STR);
    $stmt->bindValue(2, $_SESSION["current_survey_id"], PDO::PARAM_INT);
    $stmt->bindValue(3, $STEP, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        /* If data exists, overwrite the default null object with database values */
        if(!empty($dbRes) && !empty($dbRes[0])) {
            $DBRESULTS = $dbRes[0];
        }
    }

    /**
     * QUERY 2: FETCH GRAPH MODIFICATIONS
     * Retrieves any saved changes to the graph topology or labels associated 
     * with the current video ID.
     */
    $stmt = $pdo->prepare("SELECT video_frame, graph_element_id, graph_element_type, graph_element_newlabel 
                           FROM `survey_graph` 
                           WHERE `user_username` = ? AND `survey_id` = ? AND `video_id` = ?");
    
    $stmt->bindValue(1, $_SESSION["user_username"], PDO::PARAM_STR);
    $stmt->bindValue(2, $_SESSION["current_survey_id"], PDO::PARAM_INT);
    $stmt->bindValue(3, $_SESSION["current_survey_video_id"], PDO::PARAM_STR);
    
    if($stmt->execute()) {
        $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        /* * Resulting Schema for $DBGRAPHS:
         * [
         * { "video_frame": int, "graph_element_id": string, "graph_element_type": "node"|"edge", "graph_element_newlabel": string },
         * ...
         * ] 
         */
        if(!empty($dbRes)) {
            $DBGRAPHS = $dbRes;
        }
    }
}
catch(PDOException $e) {
    /* * Silent Catch: If the connection fails, the user simply sees a blank 
     * form. They can attempt to refresh or proceed to re-submit data.
     */
}

?>