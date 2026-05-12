<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /user/index.php
 * ROLE: User Dashboard & Survey Browser
 * COMPONENT: User Manager
 * * DESCRIPTION:
 * This page serves as the main hub for authenticated users. It lists all 
 * existing surveys associated with the account, determines the completion 
 * progress of each, and allows the user to start new sessions. 
 * It also includes administrative overrides for impersonation and data export.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION & ACCESS CONTROL
 * ======================================================================== */
include_once "../init.php";

/* * Security Guard: Redirect to home if the session is not active.
 */
if(empty($_SESSION["user_username"])) {
    header("Location: /"); 
    exit();
}

/**
 * ADMINISTRATIVE PRIVILEGE: USER IMPERSONATION
 * If the logged-in user has admin rights, they can "impersonate" another user
 * by passing a 'user' GET parameter. This allows the admin to view and 
 * manage surveys belonging to specific participants.
 */
if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
    if(!empty($_GET["user"])) {
        /* * Sanitize the username from the URL:
         * 1. urldecode: Decode URL-encoded characters.
         * 2. htmlspecialchars: Prevent XSS by neutralising script tags.
         * 3. strtolower: Maintain case consistency in the session.
         */
        $_SESSION["user_username"] = strtolower(htmlspecialchars(urldecode($_GET["user"]), ENT_QUOTES, "UTF-8")); 
    }
}

/* * ========================================================================
 * DATA FETCHING: USER SURVEYS
 * ======================================================================== */
$DBRESULTS_SURVEY = [];

try {
    /* Establish a PDO connection to fetch all surveys for the current session user */
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    $stmt = $pdo->prepare("SELECT * FROM `survey` WHERE `user_username` = ?");
    $stmt->bindValue(1, $_SESSION["user_username"], PDO::PARAM_STR);
    
    if($stmt->execute()) {
        $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($dbRes)) {
            $DBRESULTS_SURVEY = $dbRes;
        }
    }
}
catch(PDOException $e) {
    /* Silent fail: In case of DB error, the list remains empty and user stays on page */
}

/* * ========================================================================
 * PAGE RENDERING (UI START)
 * ======================================================================== */
html_start();
?>

<?php /* Main Dashboard Layout */ ?>
<div style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center;">
    <div style="overflow:scroll; padding:50px; position:relative; width:100%; max-width:1200px; border:solid 1px #e8e8e8; border-radius:10px;">

        <?php /* Logout Button */ ?>
        <a href="/auth/logout.php" style="position:absolute; right:60px; top:60px; padding:10px; background:red; color:#fff; border-radius:15px;">Log Out</a>

        <br><br>

        <?php
        /**
         * ADMIN MODULE INCLUSION
         * Includes administrative tools (like user search/selection) if applicable.
         */
        include_once "admin/admin.php";
        ?>

        <?php /* Current User Identity Display */ ?>
        <p style="font-size:42px;" class="fontbold"><?php echo $_SESSION["user_username"]; ?></p>

        <br><br>

        <p style="font-size:28px;" class="fontbold">Your surveys:</p>

        <br>

        <?php /* Survey Cards Container */ ?>
        <div style="width:100%; display:flex; gap:20px; overflow-x:scroll; padding:30px;">

            <?php 
            /**
             * SURVEY PROGRESS LOGIC
             * We iterate through the list of surveys to determine if they are 
             * 'Complete' or 'In Progress'. We check the sub-steps in `survey_step` 
             * to find the first null answer and calculate the resume point.
             */
            $i = 1;
            foreach($DBRESULTS_SURVEY as $r) {

                $start_step = 1; 
                $start_page = 1;
                $found_break = false;

                try {
                    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
                    $stmt = $pdo->prepare("SELECT * FROM `survey_step` WHERE `user_username` = ? AND `survey_id` = ?");
                    
                    $stmt->bindValue(1, $_SESSION["user_username"], PDO::PARAM_STR);
                    $stmt->bindValue(2, $r["id"], PDO::PARAM_INT);
                    $res = [];

                    if($stmt->execute()) {
                        $dbRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if(!empty($dbRes)) $res = $dbRes;
                    }
                    
                    if(!empty($res)) {
                        foreach($res as $rr) {
                            /* * Logic Check:
                             * Page 1: Initial reliance/trust check.
                             * Page 2: Graph interaction and final reliance check.
                             */
                            if($rr["answer_rely_before"] === null || $rr["answer_trustmeter_before"] === null) {
                                $start_step = $rr["step"]; $start_page = 1;
                                $found_break = true;
                                break;
                            } else if($rr["answer_rely_answer"] === null || $rr["answer_rely_graph"] === null || $rr["answer_trustmeter_after"] === null) {
                                $start_step = $rr["step"]; $start_page = 2;
                                $found_break = true;
                                break;
                            }
                        }
                    }
                }
                catch(PDOException $e) {
                    $found_break = true;
                }

                /* UI Component: Individual Survey Card */
                echo '
                <div style="flex-shrink:0; height:300px; width:300px; box-shadow:0px 0px 30px #00000022; border-radius:10px; padding:20px; position:relative;">
                    <div style="display:flex; gap:5px; align-items:center; justify-content:center; height:60px;">
                        <p style="font-size:24px;" class="fontbold">Survey '.$i.'</p>
                    </div>
                    <br><br>
                    <div style="width:100%; display:flex; justify-content:center;">
                        <p style="font-size:18px;">Date: '.$r["date"].'</p>
                    </div>';
                
                /*
                 * Call to Action:
                 * If a break was found, offer "Continue".
                 * If the loop finished without finding a break, the survey is complete: offer "View".
                 */
                if($found_break) { 
                    echo '
                        <div style="width:100%; display:flex; justify-content:center;">
                            <a href="/survey?id='.$r["id"].'&step='.$start_step.'&page='.$start_page.'" style="padding:15px; background:#ff3b65; color:#fff; border-radius:5px; cursor:pointer; width:80%; position:absolute; bottom:30px; display:flex; justify-content:center;">
                                Continue
                            </a>
                        </div>
                    ';
                } else {
                    echo '
                        <div style="width:100%; display:flex; justify-content:center;">
                            <a href="/survey?id='.$r["id"].'&step=1&page=1" style="padding:15px; border:solid 1px #ff3b65; color:#ff3b65; border-radius:5px; cursor:pointer; width:80%; position:absolute; bottom:30px; display:flex; justify-content:center;">
                                View
                            </a>
                        </div>
                    ';
                }

                /**
                 * ADMIN OVERLAY: DATA EXPORT
                 * Allows administrators to trigger a JSON export for the specific survey.
                 */
                if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
                    echo '<a href="/download/json.php?username='.$_SESSION["user_username"].'&survey_id='.$r["id"].'" style="position:absolute; top:0; right:0px; color:#fff; background:green; padding:10px; border-radius:0 0 0 20px; font-size:12px; box-shadow:-5px 10px 20px #00000033;">Download JSON</a>';
                }

                echo '</div>';

                $i += 1;
            }
            ?>
            
        </div>

        <br>

        <?php 
        /**
         * ACTION: CREATE NEW SURVEY
         * Passing step=0 triggers the initialization logic in the survey manager.
         */
        ?>
        <a href="/survey?step=0" style="padding:20px; background:#ff3b65; color:#fff; border-radius:15px; position:relative; cursor:pointer;">
            New Survey
        </a>

    </div>
</div>

<?php
/* End of HTML document */
html_end();
?>