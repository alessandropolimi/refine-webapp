<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /download/json/answer.php
 * ROLE: Survey Data Aggregator & Export Logic
 * COMPONENT: JSON Manager
 * * DESCRIPTION:
 * This script handles the retrieval of a comprehensive dataset for a specific 
 * user and survey session. It performs a dual-query operation to capture:
 * 1. Step-by-step answers (votes and comments).
 * 2. Graph modifications (label edits and deletions).
 * The result is a consolidated JSON object delivered as a downloadable file.
 */

/* * ========================================================================
 * PARAMETER EXTRACTION & VALIDATION
 * ======================================================================== */
include_once "../../init.php";

/* * Sanitize inputs: Normalize username and ensure Survey ID is an integer */
$username  = strtolower(htmlspecialchars(urldecode($_GET["username"]), ENT_QUOTES, "UTF-8"));
$survey_id = intval($_GET['survey_id']);

/* * Integrity Check: Ensure parameters are present and valid */
if(empty($username) || $survey_id <= 0) {
    header("Location: /"); 
    exit();
}

/* * Filename Construction: Format: {username}-{survey_id}.json */
$filename = $username . "-" . $survey_id . ".json";

/* * ========================================================================
 * DATABASE INTERACTION
 * ======================================================================== */

try {
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    /* * Error Handling: If DB connection fails, return a JSON error object */
    exportJSON($filename, json_encode(['error' => true, 'message' => 'Connection failed']));
    exit();
}



try {
    /**
     * QUERY 1: SURVEY STEPS
     * Fetches all qualitative data, ratings, and step progress.
     * Results are ordered by 'step' to maintain the chronological sequence 
     * of the user's experience.
     */
    $sql_steps = "SELECT * FROM `survey_step` 
                  WHERE user_username = :username 
                  AND survey_id = :survey_id 
                  ORDER BY step ASC";
    
    $stmt_steps = $pdo->prepare($sql_steps);
    $stmt_steps->execute(['username' => $username, 'survey_id' => $survey_id]);
    $answers = $stmt_steps->fetchAll(PDO::FETCH_ASSOC);

    /**
     * QUERY 2: GRAPH MODIFICATIONS
     * Fetches all structural and label changes made to graphs across 
     * all frames for this specific survey session.
     */
    $sql_graphs = "SELECT * FROM `survey_graph` 
                   WHERE user_username = :username 
                   AND survey_id = :survey_id";
    
    $stmt_graphs = $pdo->prepare($sql_graphs);
    $stmt_graphs->execute(['username' => $username, 'survey_id' => $survey_id]);
    $graphs = $stmt_graphs->fetchAll(PDO::FETCH_ASSOC);

    /* * ========================================================================
     * DATA CONSOLIDATION & EXPORT
     * ======================================================================== */

    /**
     * The final JSON schema wraps both datasets in a top-level object:
     * {
     * "answers": [...],
     * "graphs":  [...]
     * }
     */
    exportJSON($filename, json_encode([
        'answers' => $answers, 
        'graphs'  => $graphs
    ], JSON_PRETTY_PRINT));
    
    exit();

} catch (\PDOException $e) {
    /* Handle query execution errors */
    exportJSON($filename, json_encode(['error' => true, 'message' => 'Query failed']));
    exit();
}