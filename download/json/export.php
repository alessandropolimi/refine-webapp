<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /download/json/export.php
 * ROLE: HTTP Header & Stream Manager
 * COMPONENT: JSON Manager
 * * DESCRIPTION:
 * This utility script provides the low-level logic for triggering binary 
 * file transfers over HTTP. It defines the `exportJSON` function, which 
 * converts a raw string into a downloadable asset by manipulating the 
 * HTTP response headers.
 * * Key functions:
 * 1. MIME type definition (application/json).
 * 2. Content disposition (forcing "Save As" behavior).
 * 3. Cache management for secure data delivery.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../init.php";

/**
 * FUNCTION: exportJSON
 * Configures the server response to be treated as a downloadable JSON file.
 * * @param string $filename    - The name of the file as it will appear to the user.
 * @param string $json_output - The actual JSON string to be transmitted.
 */
function exportJSON($filename, $json_output) {
    
    /* 1. Instruct the browser that this is a raw data transfer */
    header('Content-Description: File Transfer');
    
    /* 2. Set the MIME type so the OS knows how to open the file */
    header('Content-Type: application/json');

    /* 3. Force "Attachment" mode and define the target filename */
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    /* 4. Disable caching to ensure the admin always receives the latest data */
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    /* 5. Provide the exact file size to allow the browser to show a progress bar */
    header('Content-Length: ' . strlen($json_output));

    /* 6. Output the data and terminate the stream */
    echo $json_output;
    exit;
}

?>