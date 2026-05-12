<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/submit/save_graph.php
 * ROLE: Asynchronous Graph Data Receiver & Persistence
 * COMPONENT: Form Submission Manager
 * * DESCRIPTION:
 * This script processes the raw JSON payload sent by the Cytoscape.js interface.
 * It handles the persistence of graph modifications (node/edge renaming or deletions).
 * * Key features:
 * 1. Raw input stream processing (php://input).
 * 2. Bulk "Upsert" logic (INSERT ... ON DUPLICATE KEY UPDATE) to maintain data integrity.
 * 3. Atomic processing of multiple graph changes in a single database round-trip.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION & SECURITY
 * ======================================================================== */
include_once "../../../init.php";

/* * Session Guard: Ensure the user is authenticated and the survey context is valid. */
if(empty($_SESSION["user_username"]) || empty($_SESSION["current_survey_id"])) {
    header('Location: /user'); 
    exit();
}

/**
 * STEP 1: JSON PAYLOAD CAPTURE
 * Since the data is sent via fetch() with a JSON body, we cannot use $_POST.
 * We must read the raw request body directly from the php://input stream.
 */
$json_data = file_get_contents('php://input');
$modifiedLabels = json_decode($json_data, TRUE); 

/* * Integrity Check: Verify that the JSON is valid and formatted as an array. */
if ($modifiedLabels === NULL || !is_array($modifiedLabels)) {
    exit;
}

/**
 * STEP 2: BATCH PREPARATION
 * To optimize performance, we build a single multi-row query.
 */
$Q = "INSERT INTO `survey_graph` (user_username, survey_id, video_id, video_frame, graph_element_id, graph_element_type, graph_element_newlabel) VALUES ";
$valuesPlaceholders = []; 
$values = [];

/* * Loop through the received modifications and sanitize/validate each entry. */
foreach ($modifiedLabels as $label) {
    $frame     = intval($label['video_frame']);
    $id        = strval($label['graph_element_id']);
    $type      = strtolower(strval($label['graph_element_type']));
    $new_label = strval($label['graph_element_newlabel']);

    /* Basic validation: frame must be non-negative and elements must have IDs and valid types. */
    if($frame >= 0 && !empty($id) && ($type === "node" || $type === "edge")) {
        $valuesPlaceholders[] = "(?,?,?,?,?,?,?)";
        $values[] = [
            $_SESSION["user_username"], 
            $_SESSION["current_survey_id"], 
            $_SESSION["current_survey_video_id"], 
            $frame, 
            $id, 
            $type, 
            $new_label
        ];
    }
}

/**
 * STEP 3: DATABASE EXECUTION
 * We use the "ON DUPLICATE KEY UPDATE" clause. If a record already exists for 
 * this specific frame/element, the existing entry is updated instead of creating a duplicate.
 */
if(!empty($values)) {

    $Q .= implode(", ", $valuesPlaceholders) . " 
          ON DUPLICATE KEY UPDATE 
          graph_element_type = VALUES(graph_element_type), 
          graph_element_newlabel = VALUES(graph_element_newlabel);";

    try {
        $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
        $stmt = $pdo->prepare($Q);
        
        /* * Parameter Binding:
         * We iterate through our values array to bind each placeholder to its corresponding value.
         */
        $i = 1;
        foreach($values as $v) {
            $stmt->bindValue($i,     $v[0], PDO::PARAM_STR); // username
            $stmt->bindValue($i + 1, $v[1], PDO::PARAM_INT); // survey_id
            $stmt->bindValue($i + 2, $v[2], PDO::PARAM_STR); // video_id
            $stmt->bindValue($i + 3, $v[3], PDO::PARAM_INT); // frame
            $stmt->bindValue($i + 4, $v[4], PDO::PARAM_STR); // element_id
            $stmt->bindValue($i + 5, $v[5], PDO::PARAM_STR); // element_type
            $stmt->bindValue($i + 6, $v[6], PDO::PARAM_STR); // new_label

            $i += 7; // Increment by the number of columns per row
        }
        
        $stmt->execute();
    }
    catch(PDOException $e) {
        /* Error handled silently to avoid disrupting the UI flow. */
        exit();
    }
}

/* * Success: The script exits, and the client-side Fetch API receives a 200 OK response. */

?>