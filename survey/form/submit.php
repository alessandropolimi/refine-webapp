<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/submit.php
 * ROLE: Asynchronous Form Submission Handler
 * COMPONENT: Survey Form Manager (Submission Sub-component)
 * * DESCRIPTION:
 * This file contains the JavaScript logic for intercepting the survey form 
 * submission. Instead of a traditional POST reload, it uses the Fetch API 
 * to send data to the server in the background. It manages:
 * 1. Conditional validation based on the current survey page.
 * 2. Optimization by only sending modified data (Delta-checks).
 * 3. Multi-endpoint routing (Votes/Comments vs. Graph modifications).
 * 4. Post-submission navigation logic.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

?>

<script>
/**
 * FORM INTERCEPTION
 * Reference the main survey form and attach an async listener to the submit event.
 */
const form = document.getElementById("survey_form");

form.addEventListener('submit', async (event) => {

    /* Prevent the default browser redirect to handle data via AJAX/Fetch */
    event.preventDefault(); 

    /**
     * CLIENT-SIDE VALIDATION
     * Ensure required votes are cast before proceeding.
     * Page 1: Requires initial reliance and trust meter.
     * Page 3: Requires post-interaction reliance (Text/Graph) and final trust.
     * Page 2: Handled by Cytoscape interaction (defaults to true).
     */
    if(<?php if($PAGE == 1) echo 'vote1 >= 0 && vote2 >= 0'; else if($PAGE == 3) echo 'vote3 >= 0 && vote4 >= 0 && vote5 >= 0'; else echo 'true'; ?>) {

        /* * ========================================================================
         * SUBMISSION PART A: VOTES & COMMENTS
         * ======================================================================== */
        <?php if($PAGE != 2) { ?>
            
            /* Prepare a FormData object for a multipart/form-data POST request */
            const formData = new FormData();
            formData.append("step", "<?php echo $STEP; ?>");

            /**
             * DELTA-CHECK LOGIC (Optimization)
             * We compare current UI values with the 'prec' (precedent) values 
             * loaded from the database. Data is only appended to the request 
             * if the user has actually made a change.
             */
            <?php if($PAGE == 1) {  ?>
                /* Page 1 Specific Data */
                if(vote1 >= 0 && vote1 !== vote1_prec) formData.append("answer_rely_before", String(vote1));
                if(vote2 >= 0 && vote2 !== vote2_prec) formData.append("answer_trustmeter_before", String(vote2));
                
                if(vote1_comment_text.value.length > 0 && vote1_comment_text.value !== vote1_comment_text_prec) 
                    formData.append("answer_rely_before_comment", vote1_comment_text.value);
                
                if(vote2_comment_text.value.length > 0 && vote2_comment_text.value !== vote2_comment_text_prec) 
                    formData.append("answer_trustmeter_before_comment", vote2_comment_text.value);
            <?php } else { ?>
                /* Page 3 Specific Data */
                if(vote3 >= 0 && vote3 !== vote3_prec) formData.append("answer_rely_answer", String(vote3));
                if(vote4 >= 0 && vote4 !== vote4_prec) formData.append("answer_rely_graph", String(vote4));
                if(vote5 >= 0 && vote5 !== vote5_prec) formData.append("answer_trustmeter_after", String(vote5));
                
                if(vote3_comment_text.value.length > 0 && vote3_comment_text.value !== vote3_comment_text_prec) 
                    formData.append("answer_rely_answer_comment", vote3_comment_text.value);
                
                if(vote4_comment_text.value.length > 0 && vote4_comment_text.value !== vote4_comment_text_prec) 
                    formData.append("answer_rely_graph_comment", vote4_comment_text.value);
                
                if(vote5_comment_text.value.length > 0 && vote5_comment_text.value !== vote5_comment_text_prec) 
                    formData.append("answer_trustmeter_after_comment", vote5_comment_text.value);
                
                if(answer_comment_text.value.length > 0 && answer_comment_text.value !== answer_comment_text_prec) 
                    formData.append("answer_comment", answer_comment_text.value);
            <?php } ?>

            /* Execute asynchronous request to save qualitative data */
            try {
                await fetch("/survey/form/submit/save_votes.php", {
                    method: "POST",
                    body: formData
                });
            } catch (error) {
                /* Error handled silently to prevent UX interruption */
            }

        <?php } ?>  

        /* * ========================================================================
         * SUBMISSION PART B: GRAPH MODIFICATIONS
         * ======================================================================== */
        <?php if($PAGE == 2 || $PAGE == 3) { ?>

            /**
             * GRAPH DATA PERSISTENCE
             * ModifiedLabels is a JSON array tracking edits to Cytoscape elements.
             * This is sent as an application/json payload to save_graph.php.
             */
           try {
                await fetch("/survey/form/submit/save_graph.php", {
                    method: "POST",
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(modifiedLabels)
                });
            } catch (error) {
                /* Graph sync failure handling */
            }
        <?php } ?>
        
        

        /**
         * NAVIGATION LOGIC
         * Once data synchronization is triggered, move the user to the next logical state:
         * 1. Next Page (within same Step)
         * 2. First Page of Next Step
         * 3. User Dashboard (if survey is complete)
         */
        window.location.href = <?php 
            if($PAGE == 1 || $PAGE == 2) {
                echo '"/survey?step='.strval($STEP).'&page='.strval($PAGE+1).'"'; 
            } else {
                echo $STEP < count($_SESSION["current_survey_video_ids"]) 
                    ? '"/survey?step='.strval($STEP+1).'&page=1"' 
                    : '"/user"';  
            }
        ?>;
    }
});
</script>