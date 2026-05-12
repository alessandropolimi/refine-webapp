<?php

/**
 * PROJECT: ReFiNe Project
 * FILE: /survey/form/graph/load.php
 * ROLE: Cytoscape Environment Initializer & State Hydrator
 * COMPONENT: Graphs Manager
 * * DESCRIPTION:
 * This script manages the complex lifecycle of the survey's knowledge graphs.
 * It performs three critical operations:
 * 1. Loads the base graph structure (nodes/edges) from a static JSON file.
 * 2. Dynamically generates HTML containers and Cytoscape.js instances for each frame.
 * 3. Overlays user-specific modifications (label changes/deletions) fetched from 
 * the SQL database to ensure session continuity.
 */

/* * ========================================================================
 * GLOBAL INITIALIZATION
 * ======================================================================== */
include_once "../../../init.php";

?>

<script>

    /**
     * STAGE 1: UI GENERATION & DATA TRANSFORMATION
     * This function iterates through the global graph_nodes and graph_edges arrays
     * to prepare the data format required by the Cytoscape API. It also 
     * injects the necessary DOM elements into the 'cy_container'.
     */
    function loadAndProcessGraphData() {
        const cy_container = document.getElementById('cy_container');
        let numFrames = 0; 
        let elements = []; // Formatted as: [ [ { data: { id: 'A', ... } }, ... ], ... ]
        
        /* Process Nodes and create frame containers */
        graph_nodes.forEach((elem, frameIndex) => { 

            /* Create a label and a resizable DIV for each individual graph frame */
            let newCyText = document.createElement('p');
            newCyText.innerHTML = "Graph " + String(frameIndex + 1);
            
            let newCyDiv = document.createElement('div');
            newCyDiv.id = "cy_" + frameIndex;
            newCyDiv.classList.add('cy-resizable-box');
            
            /* Enable enlargement/focus interaction on click */
            newCyDiv.setAttribute('onclick', 'enlargeElement(this, event)'); 
            
            newCyDiv.appendChild(newCyText);
            cy_container.appendChild(newCyDiv);
            
            numFrames += 1; 
            elements.push([]);
            
            /* Map raw node data to Cytoscape schema */
            elem.forEach(node => { 
                if (node.id && node.label) {
                    elements[frameIndex].push({ 
                        data: { id: node.id, label: node.label, frame: frameIndex } 
                    }); 
                }
            });
        });

        /* Map raw edge data to Cytoscape schema */
        graph_edges.forEach((elem, frameIndex) => {
            elem.forEach(edge => {
                if (edge.id && edge.source && edge.target && edge.label) {
                    elements[frameIndex].push({
                        data: { id: edge.id, source: edge.source, target: edge.target, label: edge.label, frame: frameIndex }
                    });
                }
            });
        });

        return { 'numFrames': numFrames, 'elements': elements }; 
    }

    /**
     * STAGE 2: ASSET RETRIEVAL
     * Fetches the central graph JSON library and isolates the specific graph 
     * associated with the current video task.
     */
    async function load(video_id) {
        try {
            const response = await fetch("/assets/graph.min.json");
            if(response.ok) {
                const graph_library = await response.json();
                if (Array.isArray(graph_library) && graph_library.length !== 0) {
                    /* Identify the specific graph entry for this task */
                    const targetGraph = graph_library.find(g => g.id === video_id);
                    
                    graph_nodes = targetGraph.nodes ? targetGraph.nodes : [];
                    graph_edges = targetGraph.edges ? targetGraph.edges : [];

                    /* Hydrate the task question and AI generated answer text */
                    document.getElementById('video_question').innerHTML = targetGraph.question;
                    document.getElementById('video_answer').innerHTML = targetGraph.answer;
                }
            }
        } catch(e) {
            console.error("Graph Asset Loading Failed: ", e);
        }
    }

    /**
     * STAGE 3: CYTOSCAPE INSTANTIATION
     * Once assets are loaded, we initialize the Cytoscape instances with a 'COSE' 
     * force-directed layout and apply the project-standard styling.
     */
    load(<?php echo '"'.$_SESSION["current_survey_video_id"].'"'; ?>).then(function(){
        var res = loadAndProcessGraphData();
        
        for(let i = 0; i < res.numFrames; i++) {
            cy.push(cytoscape({
                container: document.getElementById('cy_'+i),
                elements: res.elements[i],
                layout: { 
                    name: 'cose', 
                    padding: 10, 
                    nodeRepulsion: function(node) { return 2048; } 
                },
                style: [
                    { 
                        selector: 'node',
                        style: {
                            'width': 120, 'height': 120, 'background-color': '#ccc', 
                            'label': 'data(label)', 'text-valign': 'center', 
                            'text-halign': 'center', 'color': '#000', 'font-size': '24px'
                        } 
                    },
                    { 
                        selector: 'edge',
                        style: {
                            'width': 3, 'label': 'data(label)', 'line-color': '#ddd', 
                            'target-arrow-color': '#ccc', 'target-arrow-shape': 'triangle', 
                            'curve-style': 'bezier', 'font-size': '18px'
                        } 
                    }
                ]
            }));

            

            /**
             * EVENT LISTENER: INTERACTIVE EDITOR
             * Triggers the label editor upon double-clicking any graph element.
             */
            cy[i].on('dblclick', 'node, edge', function(evt) {
                const ele = evt.target;
                currentElement = ele;
                
                editorInput.value = ele.data('label') || '';
                editorContainer.style.display = 'flex'; 
                editorInput.focus();

                /* Manage focus/blur lifecycle for auto-saving */
                editorInput.removeEventListener('blur', blurSave);
                setTimeout(() => { 
                    editorInput.addEventListener('blur', blurSave); 
                }, 100);
            });
        }

        function blurSave() { 
            saveChanges(currentElement, null); 
        }
        
        /**
         * STAGE 4: DATABASE SYNCHRONIZATION (HYDRATION)
         * If the user previously modified the graph, we fetch those changes 
         * and override the default JSON state.
         */
        <?php if($PAGE == 2 && !empty($DBGRAPHS)) { ?>
            
            const DBGRAPHS = <?php echo json_encode($DBGRAPHS, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;

            DBGRAPHS.forEach(change => {
                const frameIndex = change.video_frame;
                const elementId = change.graph_element_id;
                const newLabel = change.graph_element_newlabel;

                const cyInstance = cy[frameIndex];
                const currElem = cyInstance.getElementById(elementId);

                if (currElem && currElem.length > 0) {
                    if (newLabel === "") {
                        /* Empty label signifies a user-requested deletion */
                        execDelete(currElem, null, null, null, true);
                    } else {
                        /* Apply stored label modification */
                        saveChanges(currElem, newLabel);
                    }
                }
            });
        <?php } ?>
    });

</script>