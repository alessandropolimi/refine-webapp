<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/votes.php
 * ROLE: JavaScript Variable Initialization for Voting Logic
 * COMPONENT: Survey Form Manager (Votes Sub-component)
 * * DESCRIPTION:
 * This file serves as the bridge between PHP and the client-side voting system. 
 * It initializes JavaScript variables that track current and previous states 
 * of user ratings and comments. This "state tracking" is essential for 
 * optimizing database performance, as it allows the system to only save 
 * data that has actually changed.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

?>

<script>
/**
 * UI STATE VARIABLES
 * We initialize variables to store the current vote values (ranging from 0-4).
 * -1 indicates that no selection has been made yet.
 */
let vote1 = -1, vote2 = -1, vote3 = -1, vote4 = -1, vote5 = -1;

/* * DOM References: 
 * Capturing all vote images and comment textareas to manipulate their 
 * appearance and content dynamically via JavaScript.
 */
let vote1_imgs = document.querySelectorAll(".vote1_img");
let vote2_imgs = document.querySelectorAll(".vote2_img");
let vote3_imgs = document.querySelectorAll(".vote3_img");
let vote4_imgs = document.querySelectorAll(".vote4_img");
let vote5_imgs = document.querySelectorAll(".vote5_img");

let vote1_comment_text = document.getElementById("vote1_comment_text");
let vote2_comment_text = document.getElementById("vote2_comment_text");
let vote3_comment_text = document.getElementById("vote3_comment_text");
let vote4_comment_text = document.getElementById("vote4_comment_text");
let vote5_comment_text = document.getElementById("vote5_comment_text");

/**
 * DELTA TRACKING (PREVIOUS STATE)
 * These variables store the values fetched from the database upon page load.
 * During the submission process, the "current" values are compared against 
 * these "prec" (precedent) values. If they match, the update query is skipped 
 * to reduce server load.
 */
let vote1_prec = -1, vote2_prec = -1, vote3_prec = -1, vote4_prec = -1, vote5_prec = -1;
let vote1_comment_text_prec = "", 
    vote2_comment_text_prec = "", 
    vote3_comment_text_prec = "", 
    vote4_comment_text_prec = "", 
    vote5_comment_text_prec = "";

</script>

<?php 
/**
 * SUB-COMPONENT: VOTE INTERACTION LOGIC
 * Includes the functions responsible for highlighting selected icons 
 * and toggling the visibility of comment boxes based on specific ratings.
 */
include_once "votes/change.php"; 

/**
 * SUB-COMPONENT: DATA SYNCHRONIZATION
 * Includes the logic to populate the variables above with real data 
 * retrieved from the database during the page initialization phase.
 */
include_once "votes/load.php"; 
?>