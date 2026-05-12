<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/votes/change.php
 * ROLE: Client-Side Vote Interaction Handler
 * COMPONENT: Votes Manager (Interaction Sub-component)
 * * DESCRIPTION:
 * This file contains the JavaScript logic for handling user clicks on the 
 * rating icons (Likert scale). It manages the visual feedback, 
 * conditional display of comment fields for low ratings, and enables 
 * the "Next" button once all required questions are answered.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../../init.php";

?>

<script>

/**
 * FUNCTION: vote_select
 * * Handles the selection logic for all survey rating questions.
 * * @param {HTMLElement} t - The clicked image element.
 * * @param {number} question - The ID of the question being answered (1-5).
 * * @param {number} vote - The numerical value of the vote (0-4).
 */
function vote_select(t, question, vote) {
    
    /* Determine target image array and state variable based on question ID */
    let targetImgs;
    let commentBoxId = "vote" + question + "_comment";

    /**
     * STATE UPDATE & VISUAL RESET
     * We reset the opacity and borders for all icons in the current question 
     * group before highlighting the selected one.
     */
    if(question == 1) { 
        targetImgs = vote1_imgs; 
        vote1 = vote; 
    }
    else if(question == 2) { 
        targetImgs = vote2_imgs; 
        vote2 = vote; 
    }
    else if(question == 3) { 
        targetImgs = vote3_imgs; 
        vote3 = vote; 
    }
    else if(question == 4) { 
        targetImgs = vote4_imgs; 
        vote4 = vote; 
    }
    else if(question == 5) { 
        targetImgs = vote5_imgs; 
        vote5 = vote; 
    }

    /* Apply visual reset to the group */
    targetImgs.forEach(i => {
        i.style.border = "solid 3px transparent";
        i.style.opacity = ".3";
    });

    /**
     * CONDITIONAL COMMENT BOX
     * If the user provides a '0' rating (extreme negative), a hidden 
     * textarea appears to request further qualitative feedback.
     */
    if(vote == 0) {
        document.getElementById(commentBoxId).style.display = "block";
    } else {
        document.getElementById(commentBoxId).style.display = "none";
    }

    /**
     * SELECTION HIGHLIGHTing
     * Highlight the chosen icon with a green border and full opacity.
     */
    t.style.border = "solid 3px green";
    t.style.opacity = "1";

    /**
     * NAVIGATION ENABLER (UI Barrier)
     * The 'Next' or 'Continue' button remains locked (low opacity/no cursor) 
     * until the specific requirements for the current page are met.
     */
    <?php if($PAGE == 1) { ?>
        /* Page 1 requires both initial reliance and trust meter votes */
        if(vote1 >= 0 && vote2 >= 0) {
            submit_next.style.opacity = "1";
            submit_next.style.cursor = "pointer";
        }
    <?php } else if($PAGE == 3) { ?>
        /* Page 3 requires evaluation of Text, Graph, and Final Trust */
        if(vote3 >= 0 && vote4 >= 0 && vote5 >= 0) {
            submit_next.style.opacity = "1";
            submit_next.style.cursor = "pointer";
        }
    <?php } ?>        
}

</script>