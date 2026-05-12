<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /download/json.php
 * ROLE: Admin Export Router (JSON)
 * COMPONENT: Download Manager
 * * DESCRIPTION:
 * This script serves as the primary administrative endpoint for data retrieval.
 * It provides two distinct export pathways:
 * 1. User-Specific Survey Answers: Qualitative feedback and Likert ratings.
 * 2. Graph Corrections: Structural changes and label edits made to the AI graphs.
 * * Security: 
 * Implementation of a strict session-based Admin check. Unauthorized 
 * requests are immediately redirected to the root.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION & SECURITY GATE
 * ======================================================================== */
include_once "../init.php";

/**
 * ACCESS CONTROL:
 * 1. Checks if the session 'is_admin' flag is set to true.
 * 2. Validates that required GET parameters are present:
 * - EITHER 'graph' (for structural graph exports)
 * - OR 'username' + 'survey_id' (for survey data exports)
 */
if(
    !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true ||
    ( empty($_GET['graph']) && (empty($_GET['username']) || empty($_GET['survey_id'])) )
) {
    header("Location: /"); 
    exit();
}

/* * ========================================================================
 * EXPORT ROUTING
 * ======================================================================== */

/**
 * CORE EXPORT ENGINE:
 * Includes the foundational function exportJSON() which handles headers 
 * and JSON encoding formatting.
 */
include_once "json/export.php";

if(!empty($_GET['graph'])) {
    /**
     * PATHWAY A: GRAPH MODIFICATIONS
     * Triggered when the 'graph' parameter is present.
     * Extracts row-level changes from the `survey_graph` table.
     */
    include_once "json/graph.php";
} else {
    /**
     * PATHWAY B: SURVEY DATA
     * Triggered when 'username' and 'survey_id' are present.
     * Extracts qualitative answers and metadata from the `survey_step` table.
     */
    include_once "json/answer.php";
}

?>