<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/graph.php
 * ROLE: Cytoscape.js Orchestrator & State Initializer
 * COMPONENT: Survey Form Manager (Graph Sub-component)
 * * DESCRIPTION:
 * This script serves as the primary entry point for the interactive graph 
 * visualization system. It initializes the global JavaScript environment 
 * required to render, track, and modify knowledge graphs. 
 * * Key responsibilities:
 * 1. Restricting graph logic execution to "Page 2" of the survey.
 * 2. Initializing data structures for nodes, edges, and user modifications.
 * 3. Incorporating sub-modules for UI interaction and data persistence.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

/**
 * CONDITIONAL EXECUTION:
 * To optimize performance and prevent script errors, this logic only runs 
 * when the $PAGE variable equals 2, which is the designated interaction stage 
 * for graph validation.
 */
if($PAGE == 2) {
?>
    <script>
    /* * ========================================================================
     * JAVASCRIPT STATE MANAGEMENT
     * ======================================================================== */

    /**
     * GLOBAL CYTOSCAPE INSTANCES
     * Since a single video may result in multiple frames (and thus multiple graphs),
     * this array stores each Cytoscape.js core object for independent manipulation.
     */
    let cy = []; 

    /**
     * INTERACTION POINTER
     * Stores a reference to the currently selected or active graph element 
     * (node or edge) to facilitate labeling and deletion operations.
     */
    let currentElement = null; 

    /**
     * GRAPH ARCHITECTURE ARRAYS
     * graph_nodes and graph_edges act as the client-side "Source of Truth" 
     * for the visual model. They are structured as nested arrays indexed by 
     * the video frame number.
     */
    var graph_nodes = [], graph_edges = []; 

    /**
     * MODIFICATION TRACKER (DELTA ARRAY)
     * This collection records every change a user makes (label edits, deletions).
     * This array is serialized and sent to the server upon form submission 
     * to populate the 'survey_graph' table in the database.
     */
    var modifiedLabels = [];

    </script>

    

    <?php
        /**
         * SUB-COMPONENT: INTERACTION LOGIC
         * Handles the listeners for clicking, dragging, renaming labels, 
         * and removing elements from the Cytoscape canvas.
         */
        include_once "graph/change.php";

        /**
         * SUB-COMPONENT: DATA LOADER
         * Fetches the baseline graph data from the project JSON and merges 
         * it with any previously saved user modifications from the SQL database.
         */
        include_once "graph/load.php";
    ?>

<?php
}
/** * If $PAGE != 2, the script remains silent, ensuring no unnecessary 
 * overhead for the browser.
 */
?>