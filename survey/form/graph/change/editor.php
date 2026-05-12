<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/graph/change/editor.php
 * ROLE: UI Extension for Graph Magnification & Focus
 * COMPONENT: Graph Changes Manager
 * * DESCRIPTION:
 * Cytoscape.js provides a powerful canvas but lacks native "edit mode" 
 * transitions. This file implements a custom "Magnification" system that:
 * 1. Focuses a specific graph frame when clicked, expanding its dimensions.
 * 2. Resizes and re-fits the graph viewport to ensure visibility.
 * 3. Manages global click listeners to handle "de-magnification" when 
 * the user clicks outside the interaction area.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../../../init.php";

?>

<script>
    /**
     * FUNCTION: enlargeElement
     * Handles the transition of a graph container into "Magnified Mode."
     * * @param {HTMLElement} element - The .cy-resizable-box div that was clicked.
     * @param {Event} event - The click event used to stop propagation.
     */
    function enlargeElement(element, event) {
        /* Stop event bubbling to prevent the global "click-outside" listener from firing */
        event.stopPropagation(); 

        const cyIndex = element.id.replace('cy_', ''); 
        const isCurrentlyMagnified = element.classList.contains('magnified'); 

        /* * LOGIC: Focus Management
         * If the box is already magnified, we do nothing. This allows users to 
         * interact with nodes/edges (drag, double-click) without triggering 
         * resizing logic repeatedly.
         */
        if (isCurrentlyMagnified) return;

        /* Reset all other graph containers to their thumbnail state */
        document.querySelectorAll('.cy-resizable-box.magnified').forEach(div => {
            if (div !== element) { 
                div.classList.remove('magnified');
                const otherCyIndex = div.id.replace('cy_', '');
                const otherCy = cy[otherCyIndex]; 
                
                /* Recalculate Cytoscape viewport after CSS transition */
                if (otherCy) {
                    setTimeout(() => { 
                        otherCy.resize(); 
                        otherCy.fit(null, 10); 
                    }, 100);
                }
            }
        });

        /* Enable magnified mode for the target element */
        element.classList.add('magnified'); 
        const currentCy = cy[cyIndex]; 

        /* * VIEWPORT RE-FIT:
         * Cytoscape needs an explicit resize() call when its parent container 
         * dimensions change via CSS to prevent coordinate misalignment.
         */
        if (currentCy) { 
            setTimeout(() => { 
                currentCy.resize(); 
                currentCy.fit(null, 10); 
            }, 100); 
        }
    }

    /**
     * GLOBAL LISTENER: DE-MAGNIFICATION
     * Listens for clicks on the document body. If the click lands outside 
     * the graph or editor interface, it shrinks all magnified containers.
     */
    document.addEventListener('click', function(event) {
        
        /* * BOUNDARY CHECK:
         * Ensure the user isn't clicking the graph itself, the label input, 
         * or the specific survey layout containers.
         */
        if (!event.target.closest('.cy-resizable-box') && 
            event.target.id !== 'label-editor-input' && 
            event.target.id !== 'screen_survey2' && 
            event.target.id !== 'graph_container' && 
            event.target.id !== 'graph_container_external') {
            
            document.querySelectorAll('.cy-resizable-box.magnified').forEach(div => {
                div.classList.remove('magnified');
                
                const cyIndex = div.id.replace('cy_', ''); 
                const currentCy = cy[cyIndex]; 
                
                /* Restore the graph viewport to thumbnail size */
                if (currentCy) { 
                    setTimeout(() => { 
                        currentCy.resize(); 
                        currentCy.fit(null, 10); 
                    }, 100); 
                }
            });
        }
    });

</script>