<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/votes/load.php
 * ROLE: UI Data Restoration (State Hydration)
 * COMPONENT: Votes Manager (Loading Sub-component)
 * * DESCRIPTION:
 * This script bridges the gap between the PHP server-side data (fetched in 
 * getdata.php) and the client-side JavaScript UI. It iterates through the 
 * $DBRESULTS object and generates JavaScript commands to:
 * 1. Populate "precedent" variables for delta-checking.
 * 2. Programmatically trigger vote selections to update the UI visuals.
 * 3. Restore saved comments into their respective text areas.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../../init.php";

?>

<script>
<?php 
/**
 * DATA HYDRATION LOGIC
 * We check each possible database field. If a value exists, we echo the 
 * necessary JavaScript to restore that state.
 */

/* Restore Vote 1: Initial Reliance */
if($DBRESULTS["answer_rely_before"] !== null) {
    echo 'vote1_prec = '.$DBRESULTS["answer_rely_before"].';';
    echo 'vote_select(vote1_imgs['.$DBRESULTS["answer_rely_before"].'], 1, '.$DBRESULTS["answer_rely_before"].');';
}

/* Restore Vote 2: Initial Trust Meter */
if($DBRESULTS["answer_trustmeter_before"] !== null) {
    echo 'vote2_prec = '.$DBRESULTS["answer_trustmeter_before"].';';
    echo 'vote_select(vote2_imgs['.$DBRESULTS["answer_trustmeter_before"].'], 2, '.$DBRESULTS["answer_trustmeter_before"].');';
}

/* Restore Vote 3: Post-Textual Reliance */
if($DBRESULTS["answer_rely_answer"] !== null) {
    echo 'vote3_prec = '.$DBRESULTS["answer_rely_answer"].';';
    echo 'vote_select(vote3_imgs['.$DBRESULTS["answer_rely_answer"].'], 3, '.$DBRESULTS["answer_rely_answer"].');';
}

/* Restore Vote 4: Post-Graph Reliance */
if($DBRESULTS["answer_rely_graph"] !== null) {
    echo 'vote4_prec = '.$DBRESULTS["answer_rely_graph"].';';
    echo 'vote_select(vote4_imgs['.$DBRESULTS["answer_rely_graph"].'], 4, '.$DBRESULTS["answer_rely_graph"].');';
}

/* Restore Vote 5: Final Trust Meter */
if($DBRESULTS["answer_trustmeter_after"] !== null) {
    echo 'vote5_prec = '.$DBRESULTS["answer_trustmeter_after"].';';
    echo 'vote_select(vote5_imgs['.$DBRESULTS["answer_trustmeter_after"].'], 5, '.$DBRESULTS["answer_trustmeter_after"].');';
}

/**
 * COMMENT RESTORATION
 * For text areas, we restore the saved string and update the 'prec' 
 * (precedent) variable to ensure we don't re-save identical text.
 */

if($DBRESULTS["answer_rely_before_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_rely_before_comment"]), ENT_QUOTES);
    echo 'vote1_comment_text_prec = "'.$clean_comment.'";';
    echo 'vote1_comment_text.value = "'.$clean_comment.'";';
}

if($DBRESULTS["answer_trustmeter_before_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_trustmeter_before_comment"]), ENT_QUOTES);
    echo 'vote2_comment_text_prec = "'.$clean_comment.'";';
    echo 'vote2_comment_text.value = "'.$clean_comment.'";';
}

if($DBRESULTS["answer_rely_answer_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_rely_answer_comment"]), ENT_QUOTES);
    echo 'vote3_comment_text_prec = "'.$clean_comment.'";';
    echo 'vote3_comment_text.value = "'.$clean_comment.'";';
}

if($DBRESULTS["answer_rely_graph_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_rely_graph_comment"]), ENT_QUOTES);
    echo 'vote4_comment_text_prec = "'.$clean_comment.'";';
    echo 'vote4_comment_text.value = "'.$clean_comment.'";';
}

if($DBRESULTS["answer_trustmeter_after_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_trustmeter_after_comment"]), ENT_QUOTES);
    echo 'vote5_comment_text_prec = "'.$clean_comment.'";';
    echo 'vote5_comment_text.value = "'.$clean_comment.'";';
}

/* Final qualitative feedback restoration */
if($DBRESULTS["answer_comment"] !== null) {
    $clean_comment = htmlspecialchars(addslashes($DBRESULTS["answer_comment"]), ENT_QUOTES);
    echo 'answer_comment_text_prec = "'.$clean_comment.'";';
    echo 'answer_comment_text.value = "'.$clean_comment.'";';
}
?>
</script>
