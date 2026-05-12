<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /user/admin/admin.php
 * ROLE: Administrative Dashboard Logic
 * COMPONENT: User Manager (Admin Sub-component)
 * * DESCRIPTION:
 * This component handles the administrative interface within the user dashboard.
 * It provides tools for user impersonation (viewing other users' surveys) 
 * and global data management, such as exporting graph modification data.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

/* * * SECURITY CHECK:
 * Verify if the current session holds administrative privileges.
 */
if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {

    /**
     * CONTEXTUAL VIEW LOGIC:
     * We check if the admin is currently viewing their own dashboard ('admin') 
     * or if they are impersonating another user.
     */
    if($_SESSION["user_username"] === "admin") {
        
        /* * ========================================================================
         * USER MANAGEMENT SECTION
         * ======================================================================== 
         * Displays a list of all registered users, allowing the admin to 
         * switch context and view their specific survey progress.
         */
        echo '
        <p style="font-size:28px;" class="fontbold">Users:</p>
        <br>
        <div style="width:100%; display:flex; gap:20px; overflow-x:scroll; padding:30px;">
        ';

        $DBRESULTS_USERS = [];
        try {
            /* * Fetch all registered usernames from the database */
            $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
            $stmt = $pdo->prepare("SELECT username FROM `user`");
            
            if($stmt->execute()) {
                $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(!empty($dbRes)) {
                    $DBRESULTS_USERS = $dbRes;
                }
            }
        }
        catch(PDOException $e) {
            /* Silent fail: Errors result in an empty list display */
        }
        
        /* * Iterate through users and generate UI cards (excluding the admin itself) */
        foreach($DBRESULTS_USERS as $dbu) {
            if($dbu["username"] !== "admin") {
                echo '
                    <div style="flex-shrink:0; height:150px; width:300px; box-shadow:0px 0px 30px #00000022; border-radius:10px; padding:20px; position:relative;">
                        <div style="display:flex; gap:5px; align-items:center;">
                            <p style="font-size:24px;" class="fontbold">'.$dbu["username"].'</p>
                        </div>
                        <br><br>
                        <div style="width:100%; display:flex; justify-content:center;">
                            <a href="/user?user='.$dbu["username"].'" style="padding:15px; background:#ff3b65; color:#fff; border-radius:5px; cursor:pointer; width:80%; position:absolute; bottom:30px; display:flex; justify-content:center;">
                                View Surveys
                            </a>
                        </div>
                    </div>
                    ';
            }
        }    
        echo '</div><br><br>'; 


        /* * ========================================================================
         * GLOBAL GRAPH DATA MANAGEMENT
         * ======================================================================== 
         * Displays all unique video IDs that have associated graph modifications.
         * Allows the admin to download the aggregated JSON data for each video.
         */
        echo '
        <p style="font-size:28px;" class="fontbold">Graphs:</p>
        <br>
        <div style="width:100%; display:flex; gap:20px; overflow-x:scroll; padding:30px;">
        ';

        $DBRESULTS_GRAPHS = [];
        try {
            $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
            
            /* * Select unique video IDs from the graph survey results */
            $stmt = $pdo->prepare("SELECT DISTINCT video_id FROM `survey_graph`");
            if($stmt->execute()) {
                $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(!empty($dbRes)) {
                    $DBRESULTS_GRAPHS = $dbRes;
                }
            }
        }
        catch(PDOException $e) {
            /* Silent fail */
        }
        
        /* * Iterate through unique graph data points and generate Download links */
        foreach($DBRESULTS_GRAPHS as $dbu) {
            echo '
                <div style="flex-shrink:0; height:150px; width:300px; box-shadow:0px 0px 30px #00000022; border-radius:10px; padding:20px; position:relative;">
                    <div style="display:flex; gap:5px; align-items:center;">
                        <p style="font-size:24px;" class="fontbold">'.$dbu["video_id"].'</p>
                    </div>
                    <br><br>
                    <div style="width:100%; display:flex; justify-content:center;">
                        <a href="/download/json.php?graph='.$dbu["video_id"].'" style="padding:15px; background:green; color:#fff; border-radius:5px; cursor:pointer; width:80%; position:absolute; bottom:30px; display:flex; justify-content:center;">
                            Download JSON
                        </a>
                    </div>
                </div>
                ';
        }    
        echo '</div><br><br>'; 

    } else {
        
        /**
         * IMPERSONATION MODE OVERLAY:
         * If the admin is currently viewing another user's page, provide 
         * a quick "Return Admin" button to switch the context back to the 
         * main administrative view.
         */
        echo '
        <a href="/user?user=admin" style="position:absolute; right:60px; top:120px; padding:10px; background:green; color:#fff; border-radius:15px;">Return Admin</a>
        <br><br>
        ';
    }
}

/* * Final check: if not an admin, this file produces no output, maintaining security. */

?>