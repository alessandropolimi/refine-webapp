<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form.php
 * ROLE: Survey Interface & Interaction Manager
 * COMPONENT: Survey Manager
 * * DESCRIPTION:
 * This is the central interactive hub of the survey. It dynamically generates 
 * the User Interface based on the current step and page. It handles:
 * 1. Video playback of the target task.
 * 2. Multi-stage voting (Initial Trust, Reliance, and Post-AI feedback).
 * 3. Cytoscape.js graph rendering for AI model visualization.
 * 4. Comment management and conditional UI states.
 */

/* * ========================================================================
 * DATA INITIALIZATION
 * ======================================================================== */
include_once "../init.php";

/**
 * FETCH CURRENT DATA:
 * This sub-component retrieves existing votes, comments, and graph states 
 * from the database if the user is revisiting this step/page.
 */
include_once "form/getdata.php";

/**
 * FUNCTION: vote($id)
 * * Generates a standardized voting UI component.
 * * @param int $id - The unique identifier for the specific question.
 * Each star/icon has an 'onclick' trigger that updates the internal state 
 * and conditionally displays a comment box for negative feedback.
 */
function vote($id) {
    echo '
    <div class="vote" style="display:flex; align-items:center; justify-content:center;">
        <img class="vote'.$id.'_img" onclick="vote_select(this, '.$id.', 0)" src="/assets/icon/vote_0.svg" style="height:34px; border:solid 3px transparent; border-radius:100%;"> 
        <img class="vote'.$id.'_img" onclick="vote_select(this, '.$id.', 1)" src="/assets/icon/vote_1.svg" style="height:34px; border:solid 3px transparent; border-radius:100%;"> 
        <img class="vote'.$id.'_img" onclick="vote_select(this, '.$id.', 2)" src="/assets/icon/vote_2.svg" style="height:34px; border:solid 3px transparent; border-radius:100%;"> 
        <img class="vote'.$id.'_img" onclick="vote_select(this, '.$id.', 3)" src="/assets/icon/vote_3.svg" style="height:34px; border:solid 3px transparent; border-radius:100%;"> 
        <img class="vote'.$id.'_img" onclick="vote_select(this, '.$id.', 4)" src="/assets/icon/vote_4.svg" style="height:34px; border:solid 3px transparent; border-radius:100%;"> 
    </div>
    <div id="vote'.$id.'_comment" style="padding:10px; display:none;">
        <div style="padding:10px; border-top:solid 1px #eee;">
            <p style="opacity:.85; margin-bottom:5px; color:red;">Why is your vote so bad?</p>
            <textarea id="vote'.$id.'_comment_text" style="width:100%; min-height:120px; border:solid 1px #eee; border-radius:5px; padding:10px;"></textarea>
        </div>
    </div>
    ';
}

?>

<?php /* SURVEY INTERFACE START */ ?>
<div style="height:100%; width:100%; display:flex; justify-content:center;">
    <form id="survey_form" style="width:100%; max-width:1400px; height:100%; position:relative;">

        <div class="sbn" style="width:100%; max-height:calc(100vh - 80px); padding:30px; padding-bottom:0px; display:flex; gap:20px;">

            <?php /* LEFT COLUMN: Task Information & Media */ ?>
            <div style="width:50%; max-height:calc(100vh - 80px);">
                <p style="font-size:32px;" class="fontbold">Task <?php echo $STEP."/".count($_SESSION["current_survey_video_ids"]); ?></p>
                <br><br>
                <p style="font-size:22px;">Here is a video showing a task that one day we want to train a robot to perform:</p>
                <br>
                <video id="video_element" controls style="width:100%; max-height:50vh; border-radius:10px; border:solid 1px #ccc;">
                    <source id="video_source" type="video/mp4" <?php echo 'src="/assets/video/'.$_SESSION["current_survey_video_id"].'.mp4"'; ?>>
                    Your browser does not support the video tag.
                </video>
            </div>

            <?php /* RIGHT COLUMN: Survey Questions & Interaction */ ?>
            <div style="width:100%; max-height:calc(100vh - 80px); padding:10px; overflow-y:scroll;">
                <p style="font-size:28px;" class="fontbold">
                    <?php 
                        /* Dynamic Page Title */
                        if($PAGE == 1) echo '1. Start';
                        else if($PAGE == 2) echo '2. Ask Model';
                        else echo '3. Rate Answers';
                    ?>
                </p>
                <br><br>                

                <?php /* SCREEN 1: PRE-AI INTERACTION (EX-ANTE) */ ?>
                <div id="screen_survey1" <?php if($PAGE != 1) echo 'style="display:none;"'; ?>>
                    <p style="font-size:18px;">How likely would you be to use an AI model to summarise the content of this video for you?</p>
                    <br><br>
                    <div style="padding:15px; padding-bottom:8px;">
                        <?php vote(1); ?>
                    </div>
                    <br><br><br>
                    <p style="font-size:18px;">Before starting please set the <span style="color:#ff3b65">Trust Meter</span> below:</p>
                    <br><br>
                    <div style="padding:15px; padding-bottom:8px;">
                        <?php vote(2); ?>
                    </div>
                </div>

                <?php /* SCREEN 2 & 3: AI INTERACTION & EVALUATION */ ?>
                <div id="screen_survey2" <?php if($PAGE == 1) echo 'style="opacity:0; position:relative; z-index:-1;"'; ?>>
                    <div style="padding:15px; padding-bottom:8px;">
                        <?php if($PAGE == 2) { ?>
                            <p style="font-size:24px;">Answers given by the AI Model.</p>
                            <br><br>
                            <div style="display:flex; align-items:center; gap:10px; padding-bottom:10px;">
                                <p style="font-size:16px;">Question: </p>
                                <p id="video_question" style="font-size:16px;" class="fontbold"></p>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <p style="font-size:16px;">Answer: </p>
                                <p id="video_answer" style="font-size:18px;color:#ff3b65;" class="fontbold"></p>
                            </div>
                        <?php } ?>

                        <?php if($PAGE == 3) { ?>
                            <p style="font-size:18px;">After reading the textual response, how likely are you to rely on the model?</p>
                            <br>
                            <?php vote(3); ?>
                        <?php } ?>
                    </div>

                    <br><br>

                    <?php /* GRAPH SECTION: CYTOSCAPE RENDERING */ ?>
                    <div id="graph_container_external" style="padding:15px; padding-bottom:8px;">
                        <?php if($PAGE == 2) { ?>
                            <p style="font-size:18px;">These are the graphs generated by the AI Model describing objects and actions. You can modify them by changing and deleting nodes and edges.</p>
                            <br>
                            <div id="graph_container" style="width:100%;">
                                <div id="cy_container" style="width:100%; display:flex; gap:10px; overflow-x:scroll; align-items:flex-end; padding:20px;"></div>
                                <div id="cy_label-editor-container" style="position:absolute; left:45%; top:45%; border:1px solid #333; padding:5px; background-color:#f9f9f9; box-shadow:2px 2px 5px rgba(0,0,0,0.3); z-index:1000; display:none; white-space:nowrap;"></div>
                            </div>
                        <?php } ?>

                        <?php if($PAGE == 3) { ?>
                            <p style="font-size:18px;">After looking at the graph response, how likely are you to rely on the model for describing this video?</p>
                            <br>
                            <?php vote(4); ?>
                        <?php } ?>
                    </div>        

                    <?php if($PAGE == 3) { ?>
                        <div style="padding:15px; padding-bottom:8px;">
                            <br>
                            <p style="font-size:18px;">What did you notice about either of the model responses?</p>
                            <div style="padding:15px; padding-bottom:8px;">
                                <textarea id="answer_comment_text" style="width:100%; min-height:120px; border:solid 1px #ccc; border-radius:5px; padding:10px;" placeholder="Type here"></textarea>
                            </div>
                            <br><br>
                            <p style="font-size:18px;">You can reset the <span style="color:#ff3b65">Trust Meter</span> if you want:</p>
                            <br>
                            <div style="padding:15px; padding-bottom:8px;">
                                <?php vote(5); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php /* FOOTER NAVIGATION */ ?>
        <div style="position:absolute; bottom:0; display:flex; width:100%; padding:0 50px; height:80px; align-items:center; justify-content:space-between;">
            <?php
            /* Back Navigation Logic */
            if($PAGE == 2 || $PAGE == 3 || $STEP > 1) {
                if($PAGE == 1) echo '<a href="/survey?step='.strval($STEP-1).'&page=3" style="width:250px; font-size:18px; border:solid 1px #ff3b65; background:transparent; color:#ff3b65; text-align:center;" class="button">Back</a>';
                else echo '<a href="/survey?step='.strval($STEP).'&page='.strval($PAGE-1).'" style="width:250px; font-size:18px; border:solid 1px #ff3b65; background:transparent; color:#ff3b65; text-align:center;" class="button">Back</a>';
            } else {
                echo '<div style="width:250px;"></div>';
            }
            ?>
            <a href="/user" style="font-size:18px;">Exit</a>
            <?php
            /* Forward Navigation Logic / Submit Labeling */
            $total_steps = count($_SESSION["current_survey_video_ids"]);
            if($PAGE == 1 || $PAGE == 2 || $STEP < $total_steps) {
                $label = ($PAGE == 1) ? "Ask Model" : (($PAGE == 2) ? "Rate Answer" : "Continue");
                echo '<button id="submit_next" type="submit" style="opacity:.3; cursor:default; width:250px; font-size:18px; background:#ff3b65;" class="fontbold">'.$label.'</button>';
            } else {
                echo '<button id="submit_next" type="submit" style="opacity:.3; cursor:default; width:250px; font-size:18px; background:#ff3b65;" class="fontbold">Finish</button>';
            }
            ?>
        </div>
    </form>
</div>

<?php 
/**
 * UI STATE LOGIC (Client-Side)
 * Handles basic UI behavior such as button activation for Page 2.
 */
?>
<script>
    let submit_next = document.getElementById("submit_next");
    <?php if($PAGE == 2) { ?>
        submit_next.style.opacity = "1";
        submit_next.style.cursor = "pointer";
    <?php } ?> 
</script>

<script>
    let answer_comment_text = document.getElementById("answer_comment_text");
    let answer_comment_text_prec = "";
</script>

<?php 
/**
 * JAVASCRIPT MODULES
 * Injected directly to ensure instant updates and bypass browser caching.
 */
include_once "form/votes.php";   /* Vote selection and comment logic */
include_once "form/submit.php";  /* AJAX Form submission */
include_once "form/graph.php";   /* Cytoscape.js implementation */
?>