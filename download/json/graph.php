<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /download/json/graph.php
 * ROLE: Global Graph Modification Exporter
 * COMPONENT: JSON Manager
 * * DESCRIPTION:
 * Unlike the 'answer' export which focuses on a single user session, this 
 * script aggregates all modifications for a specific Video/Graph ID from 
 * every participant in the database. 
 * * Purpose:
 * This data is essential for researchers to identify common "errors" or 
 * points of confusion in AI-generated graphs by comparing the correction 
 * patterns of multiple users on the same visual task.
 */

/* * ========================================================================
 * PARAMETER EXTRACTION & VALIDATION
 * ======================================================================== */
include_once "../../init.php";

/* * Sanitize inputs: Normalize the Video/Graph ID from the 'graph' GET parameter */
$video_id = htmlspecialchars(urldecode($_GET["graph"]), ENT_QUOTES, "UTF-8");

/* * Integrity Check: Terminate if the Video ID is missing */
if(empty($video_id)) {
    header("Location: /"); 
    exit();
}

/* * Filename Construction: Format: {video_id}.json */
$filename = $video_id . ".json";

/* * ========================================================================
 * DATABASE INTERACTION
 * ======================================================================== */

try {
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
} catch (\PDOException $e) {
    /* Handle connection failure */
    exportJSON($filename, json_encode(['error' => true, 'message' => 'Database connection failed']));
    exit();
}



try {
    /**
     * QUERY: GLOBAL GRAPH CORRECTIONS
     * Fetches every row in the `survey_graph` table matching the specific video.
     * We order by 'video_frame' to ensure the resulting JSON follows the 
     * chronological progression of the video clips.
     */
    $sql_graphs = "SELECT * FROM `survey_graph` 
                   WHERE video_id = :video_id 
                   ORDER BY video_frame ASC";
    
    $stmt_graphs = $pdo->prepare($sql_graphs);
    $stmt_graphs->execute(['video_id' => $video_id]);
    $graphs = $stmt_graphs->fetchAll(PDO::FETCH_ASSOC);

    /* * ========================================================================
     * DATA TRANSMISSION
     * ======================================================================== */

    /**
     * Returns a flat array of objects, where each object represents a single 
     * modification (renaming/deletion) by a specific user on a specific frame.
     */
    exportJSON($filename, json_encode($graphs, JSON_PRETTY_PRINT));
    exit();

} catch (\PDOException $e) {
    /* Handle query-specific exceptions */
    exportJSON($filename, json_encode(['error' => true, 'message' => 'Query execution failed']));
    exit();
}

?>