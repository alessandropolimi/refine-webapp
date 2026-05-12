<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/graph/change.php
 * ROLE: Interactive Graph Manipulation & State Tracking
 * COMPONENT: Graphs Manager
 * * DESCRIPTION:
 * This script provides the interaction layer for the Cytoscape.js environment.
 * It manages the floating editor interface that allows users to:
 * 1. Modify element labels (Renaming).
 * 2. Remove elements (Deletion).
 * 3. Track all changes in a "delta" array (`modifiedLabels`) for bulk 
 * synchronization with the SQL database upon form submission.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../../init.php";

?>

<script>
/**
 * UI COMPONENT: FLOATING EDITOR
 * Dynamically creates the input and control elements for the graph editor.
 * This container follows the user's selection on the canvas.
 */
const editorContainer = document.getElementById("cy_label-editor-container");

/* Label Input Configuration */
const editorInput = document.createElement('input');
editorInput.type = 'text';
editorInput.id = 'label-editor-input'; 
editorInput.style.width = '300px'; 
editorInput.style.height = '40px';

/* Delete Button Configuration */
const deleteButton = document.createElement('button');
deleteButton.textContent = 'DELETE';
deleteButton.id = 'delete-button';
deleteButton.style.marginLeft = '5px'; 
deleteButton.style.backgroundColor = 'red'; 
deleteButton.style.color = 'white'; 
deleteButton.style.border = 'none'; 
deleteButton.style.padding = '3px 8px'; 
deleteButton.style.cursor = 'pointer';

/* Assemble Editor UI */
editorContainer.appendChild(editorInput);
editorContainer.appendChild(deleteButton);

/* * ========================================================================
 * CORE ACTION FUNCTIONS
 * ======================================================================== */

/**
 * FUNCTION: trackLabelChange
 * Registers a modification in the global `modifiedLabels` array.
 * It ensures only the latest change for a specific element/frame is kept.
 * @param {string} id - The unique ID of the graph element.
 * @param {string|null} newLabel - The updated label (null represents deletion).
 * @param {string} type - 'node' or 'edge'.
 * @param {number} frame - The index of the video frame.
 */
function trackLabelChange(id, newLabel, type, frame) {
    /* Filter out previous entries for the same element to prevent duplicate payloads */
    modifiedLabels = modifiedLabels.filter(e => { 
        return !(e.graph_element_id === id && e.graph_element_type === type && e.video_frame === frame); 
    });
    
    /* Append the new state to the delta array */
    modifiedLabels.push({
        'graph_element_id': id, 
        'graph_element_type': type, 
        'graph_element_newlabel': newLabel, 
        'video_frame': frame
    });
}

/**
 * FUNCTION: execDelete
 * Removes an element from the active Cytoscape instance and registers the deletion.
 * If a node is deleted, it recursively handles the removal of associated edges.
 */
function execDelete(currElem = currentElement, type = null, id = null, frame = null, deleteJustThis = false) {
    if(currElem !== null) {
        /* Capture data before DOM removal */
        if(id === null) id = currElem.id();
        if(frame === null) frame = currElem.data('frame');
        if(type === null) type = currElem.isNode() ? 'node' : 'edge';
        
        currElem.remove(); 
    }

    /* Cascade Deletion: Remove edges connected to the deleted node */
    if(!deleteJustThis && type === 'node' && id !== null && frame !== null) {
        graph_edges[frame].forEach(edge => {
            if(edge.source === id || edge.target === id) {
                execDelete(null, 'edge', edge.id, frame);
            }
        });
    }

    /* Register the deletion (null label indicates removal in the DB) */
    trackLabelChange(id, null, type, frame); 
    editorContainer.style.display = 'none'; 
    if(currElem === currentElement) currentElement = null;
}

/**
 * FUNCTION: saveChanges
 * Commits a label change from the editor input to the Cytoscape element data.
 */
function saveChanges(currElem = currentElement, newLab = null) {
    if (!currElem) return; 
    
    let newLabel = (newLab == null) ? editorInput.value.trim() : newLab;
    const originalLabel = currElem.data('label');
    
    /* Update Cytoscape display */
    currElem.data('label', newLabel); 

    /* Log change if the new label differs from the original */
    if (newLabel !== originalLabel) { 
        trackLabelChange(
            currElem.id(), 
            newLabel, 
            currElem.isNode() ? 'node' : 'edge', 
            currElem.data('frame')
        );
    }
    
    editorContainer.style.display = 'none'; 
    if(currElem === currentElement) currentElement = null;
}

/**
 * FUNCTION: cancelChanges
 * Closes the editor without committing any data.
 */
function cancelChanges() { 
    editorContainer.style.display = 'none'; 
    currentElement = null; 
}

/* * ========================================================================
 * EVENT LISTENERS
 * ======================================================================== */

/* Keyboard Shortcuts: Enter to Save, Escape to Cancel */
editorInput.addEventListener('keydown', (e) => { 
    if (e.key === 'Enter') { e.preventDefault(); saveChanges(); } 
    if (e.key === 'Escape') cancelChanges(); 
});

/* Delete Button: Uses 'mousedown' to preempt the 'blur' event of the input */
deleteButton.addEventListener('mousedown', (e) => { 
    e.preventDefault(); 
    execDelete(currentElement); 
});

</script>



<?php
    /**
     * SUB-COMPONENT: EDITOR UI
     * Includes the HTML structure and CSS for the floating editor overlay.
     */
    include_once "change/editor.php";
?>