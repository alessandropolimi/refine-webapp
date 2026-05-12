<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /init.php
 * ROLE: Global Initialization
 * COMPONENT: Webapp Root
 * * DESCRIPTION:
 * This file serves as the core orchestrator for the entire application.
 * It defines database credentials, global constants for video management,
 * and provides the structural HTML wrapper functions used across all pages.
 * Being included in every script, it ensures environmental consistency.
 */

/* * ========================================================================
ADMIN USER:
username: admin
psw: 0z9#7ElM.ktzQ!8x

TEST USER:
username: test
psw: 4gEl09Hkls!dj#1
==========================================================================* */

/* * ========================================================================
 * DATABASE CONFIGURATION
 * ========================================================================
 * These constants define the connection parameters for the MySQL database.
 */
define("_DB_NAME_", "refine");
define("_DB_HOST_", "localhost");
define("_DB_USERNAME_", "refine");
define("_DB_PASSWORD_", "u1nDb3jS1ZqsLYY!"); // PROD: u1nDb3jS1ZqsLYY! | TEST: kMktzQ8xc213HHt!

/* * ========================================================================
 * MEDIA & VIDEO PARAMETERS
 * ========================================================================
 * Constants used to manage the pool of video assets used within surveys.
 */
define("_NUM_VIDEOS_", 15);
define("_MIN_INDEX_VIDEO_", 0);
define("_MAX_INDEX_VIDEO_", 499);
define("_TOTAL_VIDEOS_", _MAX_INDEX_VIDEO_ - _MIN_INDEX_VIDEO_ + 1);

/**
 * FUNCTION: html_start()
 * * Generates the standard HTML5 boilerplate, including metadata, SEO tags,
 * external library inclusions (Cytoscape.js), and global CSS styling.
 * * NOTE ON CSS: Styles are embedded directly to prevent browser caching issues 
 * during development, ensuring that every page refresh reflects the latest 
 * visual changes without requiring a hard cache reset (CMD+SHIFT+R).
 */
function html_start() {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="/assets/icon/favicon.png" type="image/x-icon">
        <title>ReFiNe Project</title>
        <meta name="description" content="Do you trust AI? Take the survey!">
        
        <script src="/assets/cytoscape.js"></script>
        
        <style>
            /* CUSTOM FONT LOADING */
            @font-face {
                font-family: "regular"; 
                src: local("regular"), url("/assets/regular.ttf") format("truetype");
            }
            @font-face {
                font-family: "bold"; 
                src: local("bold"), url("/assets/bold.ttf") format("truetype");
            }

            /* RESET & GLOBAL STYLES */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "regular";
            }
            
            a {
                text-decoration: none; 
                cursor: pointer;
            } 

            input, select {
                padding: 10px; 
                border: solid 1px #ccc; 
                border-radius: 8px; 
                background: transparent;
            }

            button, .button {
                cursor: pointer; 
                background: #ff3b65; 
                padding: 15px 30px; 
                border: none; 
                border-radius: 5px; 
                outline: none; 
                color: #fff;
                display: inline-block;
            }

            body {
                height: 100vh; 
                width: 100%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                overflow: hidden; 
                background: #fff; 
                color: #444; 
                font-family: "regular", Helvetica, system-ui;
            }

            .fontregular { font-family: "regular"; font-weight: 400; }
            .fontbold { font-family: "bold"; font-weight: 600; }

            /* CYTOSCAPE UI COMPONENTS */
            .cy-resizable-box {
                transition: all 0.05s ease; 
                cursor: pointer; 
                flex-shrink: 0; 
                width: 200px; 
                height: 100px; 
                border: solid 1px #eee; 
                border-radius: 10px; 
                position: relative; 
                background: #fff;
            }
            .cy-resizable-box p {
                position: absolute; 
                z-index: 10; 
                top: 5px; 
                left: 10px; 
                font-family: "bold"; 
                font-weight: 600; 
                font-size: 18px; 
                background: rgba(255, 255, 255, 0.53); 
                padding: 10px;
            }
            .cy-resizable-box.magnified {
                width: 70% !important; 
                height: 500px !important; 
                border: 4px solid #ff3b65; 
                z-index: 100; 
                position: fixed; 
                bottom: 100px; 
                right: 20px;
            }

            /* INTERACTIVE ELEMENTS */
            .vote img {
                opacity: .3; 
                cursor: pointer; 
                transition: .15s;
            }
            .vote img:hover {
                opacity: 1;
            }
        </style>
    </head>
    <body>
        <div style="height: 100vh; width: 100%; overflow: hidden; position: relative;">
    ';
}

/**
 * FUNCTION: html_end()
 * * Closes the main application container and terminates the HTML document tags.
 */
function html_end() {
    echo '
        </div>
    </body>
    </html>
    ';
}

/* * ========================================================================
 * SESSION MANAGEMENT
 * ========================================================================
 * Start or resume the PHP session to track user authentication and survey state.
 */
session_start();

?>
