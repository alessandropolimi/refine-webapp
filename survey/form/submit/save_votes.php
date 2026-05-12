<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/submit/save_votes.php
 * ROLE: Asynchronous Vote and Comment Persistence
 * COMPONENT: Form Submission Manager
 * * DESCRIPTION:
 * This script processes qualitative survey data (ratings and text comments) 
 * sent via POST from the survey form. It performs:
 * 1. Security and session validation.
 * 2. Strict type-casting and range validation for Likert-scale votes (0-4).
 * 3. Dynamic SQL construction to update only the fields provided in the request.
 * 4. Atomic update of the 'survey_step' record for the specific user and session.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION & SECURITY
 * ======================================================================== */
include_once "../../../init.php";

/* * Session and Payload Guard: Validate presence of user, survey ID, and step. */
if(
    empty($_SESSION["user_username"]) || 
    empty($_SESSION["current_survey_id"]) || 
    !isset($_POST["step"])
) {
    header('Location: /user'); 
    exit();
}

$step = intval($_POST["step"]);
if($step < 1 || $step > _NUM_VIDEOS_) exit();

/* * ========================================================================
 * DATA SANITIZATION & VALIDATION
 * ======================================================================== */

/**
 * RATINGS (Likert Scale 0-4)
 * We retrieve the votes. If a vote is outside the valid range [0, 4], 
 * it is set to null to avoid database corruption or invalid data points.
 */
$answer_rely_before      = (isset($_POST["answer_rely_before"])      ? intval($_POST["answer_rely_before"])      : -1);
$answer_trustmeter_before = (isset($_POST["answer_trustmeter_before"]) ? intval($_POST["answer_trustmeter_before"]) : -1);
$answer_rely_answer      = (isset($_POST["answer_rely_answer"])      ? intval($_POST["answer_rely_answer"])      : -1);
$answer_rely_graph       = (isset($_POST["answer_rely_graph"])       ? intval($_POST["answer_rely_graph"])       : -1);
$answer_trustmeter_after  = (isset($_POST["answer_trustmeter_after"])  ? intval($_POST["answer_trustmeter_after"])  : -1);

if($answer_rely_before < 0      || $answer_rely_before > 4)      $answer_rely_before = null;
if($answer_trustmeter_before < 0 || $answer_trustmeter_before > 4) $answer_trustmeter_before = null;
if($answer_rely_answer < 0      || $answer_rely_answer > 4)      $answer_rely_answer = null;
if($answer_rely_graph < 0       || $answer_rely_graph > 4)       $answer_rely_graph = null;
if($answer_trustmeter_after < 0  || $answer_trustmeter_after > 4)  $answer_trustmeter_after = null;

/**
 * COMMENTS (Qualitative Feedback)
 * Comments are cast to strings. Empty or missing comments are treated as null.
 */
$answer_rely_before_comment      = (isset($_POST["answer_rely_before_comment"])      ? strval($_POST["answer_rely_before_comment"])      : null);
$answer_trustmeter_before_comment = (isset($_POST["answer_trustmeter_before_comment"]) ? strval($_POST["answer_trustmeter_before_comment"]) : null);
$answer_rely_answer_comment      = (isset($_POST["answer_rely_answer_comment"])      ? strval($_POST["answer_rely_answer_comment"])      : null);
$answer_rely_graph_comment       = (isset($_POST["answer_rely_graph_comment"])       ? strval($_POST["answer_rely_graph_comment"])       : null);
$answer_trustmeter_after_comment  = (isset($_POST["answer_trustmeter_after_comment"])  ? strval($_POST["answer_trustmeter_after_comment"])  : null);
$answer_comment                  = (isset($_POST["answer_comment"])                  ? strval($_POST["answer_comment"])                  : null);

/* * Integrity Check: If the payload contains no valid data, terminate execution. */
if(
    $answer_rely_before === null && $answer_trustmeter_before === null &&
    $answer_rely_answer === null && $answer_rely_graph === null &&
    $answer_trustmeter_after === null && $answer_rely_before_comment === null &&
    $answer_trustmeter_before_comment === null && $answer_rely_answer_comment === null &&
    $answer_rely_graph_comment === null && $answer_trustmeter_after_comment === null &&
    $answer_comment === null
) exit();

/* * ========================================================================
 * DYNAMIC QUERY CONSTRUCTION
 * ======================================================================== */

$username  = $_SESSION["user_username"];
$survey_id = $_SESSION["current_survey_id"];

$Q = "UPDATE `survey_step` SET ";
$set = [];          // Holds actual values
$setStr = [];       // Holds SQL syntax (e.g., "`column` = ?")
$pdoParamType = []; // Holds PDO data types

/* * Build the SET clause dynamically based on which fields are populated. */
if($answer_rely_before !== null) { 
    $set[] = $answer_rely_before; $setStr[] = "`answer_rely_before` = ?"; $pdoParamType[] = PDO::PARAM_INT; 
}
if($answer_rely_before_comment !== null) { 
    $set[] = $answer_rely_before_comment; $setStr[] = "`answer_rely_before_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}
if($answer_trustmeter_before !== null) { 
    $set[] = $answer_trustmeter_before; $setStr[] = "`answer_trustmeter_before` = ?"; $pdoParamType[] = PDO::PARAM_INT; 
}
if($answer_trustmeter_before_comment !== null) { 
    $set[] = $answer_trustmeter_before_comment; $setStr[] = "`answer_trustmeter_before_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}
if($answer_rely_answer !== null) { 
    $set[] = $answer_rely_answer; $setStr[] = "`answer_rely_answer` = ?"; $pdoParamType[] = PDO::PARAM_INT; 
}
if($answer_rely_answer_comment !== null) { 
    $set[] = $answer_rely_answer_comment; $setStr[] = "`answer_rely_answer_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}
if($answer_rely_graph !== null) { 
    $set[] = $answer_rely_graph; $setStr[] = "`answer_rely_graph` = ?"; $pdoParamType[] = PDO::PARAM_INT; 
}
if($answer_rely_graph_comment !== null) { 
    $set[] = $answer_rely_graph_comment; $setStr[] = "`answer_rely_graph_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}
if($answer_comment !== null) { 
    $set[] = $answer_comment; $setStr[] = "`answer_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}
if($answer_trustmeter_after !== null) { 
    $set[] = $answer_trustmeter_after; $setStr[] = "`answer_trustmeter_after` = ?"; $pdoParamType[] = PDO::PARAM_INT; 
}
if($answer_trustmeter_after_comment !== null) { 
    $set[] = $answer_trustmeter_after_comment; $setStr[] = "`answer_trustmeter_after_comment` = ?"; $pdoParamType[] = PDO::PARAM_STR; 
}

/* * Finalize the query with the WHERE constraints. */
$Q .= implode(", ", $setStr)." WHERE `user_username` = ? AND `survey_id` = ? AND `step` = ?";



/* * ========================================================================
 * DATABASE PERSISTENCE
 * ======================================================================== */

try {
    $pdo = new PDO("mysql:host="._DB_HOST_.";dbname="._DB_NAME_, _DB_USERNAME_, _DB_PASSWORD_);
    $stmt = $pdo->prepare($Q);
    
    /* * Parameter Binding Loop for Dynamic Values */
    $i = 0;
    foreach($set as $s) {
        $stmt->bindValue($i + 1, $s, $pdoParamType[$i]);
        $i++;
    }
    
    /* * Bind WHERE Clause Constraints */
    $stmt->bindValue($i + 1, $username,  PDO::PARAM_STR);
    $stmt->bindValue($i + 2, $survey_id, PDO::PARAM_INT);
    $stmt->bindValue($i + 3, $step,      PDO::PARAM_INT);

    $stmt->execute();
}
catch(PDOException $e) {
    /* Silent termination on database error to prevent leaking system info. */
    exit();
}

?>