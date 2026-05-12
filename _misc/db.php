<?php

/*

-- table for users
CREATE TABLE IF NOT EXISTS `user` (
    `username` VARCHAR(64) NOT NULL PRIMARY KEY,
    `course` VARCHAR(128) NOT NULL,
    `age` INT NOT NULL DEFAULT 0,
    `gender` VARCHAR(1) NOT NULL DEFAULT "0", -- "m", "f", "o", "0"
    `password` TEXT NOT NULL
);

-- table for each survey
CREATE TABLE IF NOT EXISTS `survey` (
    `id` BIGINT UNSIGNED NOT NULL
        AUTO_INCREMENT PRIMARY KEY,
    `user_username` VARCHAR(64) NOT NULL
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION,
    `date` DATE
);

-- table for each answers (votes and comments) for each step of each survey
-- with this table we are flexible in the number of steps for each survey (not fixed)
CREATE TABLE IF NOT EXISTS `survey_step` (
    `user_username` VARCHAR(64) NOT NULL
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION,
    `survey_id` BIGINT UNSIGNED NOT NULL
    	REFERENCES `survey`(`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
    `step` INT UNSIGNED NOT NULL,
    `video_id` VARCHAR(128) NOT NULL,
    -- page 1 -------
    `answer_rely_before` INT UNSIGNED DEFAULT NULL,
    `answer_rely_before_comment` TEXT DEFAULT NULL,
    `answer_trustmeter_before` INT UNSIGNED DEFAULT NULL,
    `answer_trustmeter_before_comment` TEXT DEFAULT NULL,
    -- page 3 -------
    `answer_rely_answer` INT UNSIGNED DEFAULT NULL,
    `answer_rely_answer_comment` TEXT DEFAULT NULL,
    `answer_rely_graph` INT UNSIGNED DEFAULT NULL,
    `answer_rely_graph_comment` TEXT DEFAULT NULL,
    `answer_comment` TEXT DEFAULT NULL,
    `answer_trustmeter_after` INT UNSIGNED DEFAULT NULL,
    `answer_trustmeter_after_comment` TEXT DEFAULT NULL,
    -- ---------------
    PRIMARY KEY (`user_username`, `survey_id`, `step`)
);

-- table for each change in graphs (for each survey)
CREATE TABLE IF NOT EXISTS `survey_graph` (
    `user_username` VARCHAR(64) NOT NULL
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION,
    `survey_id` BIGINT UNSIGNED NOT NULL
    	REFERENCES `survey`(`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
    `video_id` VARCHAR(128) NOT NULL, 
    -- we take the id of the video and NOT the index in the graph.json because this list in the 
    -- json can change (so the video index too) while the id of the video no
    `video_frame` BIGINT UNSIGNED NOT NULL,
    `graph_element_id` VARCHAR(128) NOT NULL,
    `graph_element_type` VARCHAR(4) NOT NULL, -- "node", "edge"
    `graph_element_newlabel` VARCHAR(128), 
    -- if graph_element_newlabel NULL or empty means that the element id is removed
    PRIMARY KEY (`user_username`, `survey_id`, `video_id`, `video_frame`, `graph_element_id`)
);

*/

/*

**************************************************************************
***** THE PURPOUSE OF THIS FILE IS JUST TO INSPECT THE SQL DATABASE  *****
***** AND QUERING IT TO ADD, REMOVE, CHANGE TABLES.                  *****
**************************************************************************

*/

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);



include_once "init.php";

//if(!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {header("Location: /"); exit();}


//$dbname = "...";
$dbname = _DB_NAME_;

/*
// CREATE DATABASE IF IT DOES NOT EXIST
$conn = new mysqli(_DB_HOST_, _DB_USERNAME_, _DB_PASSWORD_);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database **$dbname** successfully created!";
} else {
    echo "Error in database creation: " . $conn->error;
}
$conn->close();
*/

// -------------------------------------------------------------------------

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//try {
//    $conn = new mysqli(_DB_HOST_, _DB_USERNAME_, _DB_PASSWORD_, _DB_NAME_);
//    echo "Connessione riuscita!";
//} catch (mysqli_sql_exception $e) {
//    echo "Errore specifico: " . $e->getMessage();
//}



// --- 1. DATABASE CONNECTION ---
$conn = new mysqli(_DB_HOST_, _DB_USERNAME_, _DB_PASSWORD_, _DB_NAME_);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}



echo '<style>
    *{margin:0;padding:0;box-sizing:border-box;}
</style>

<body style="margin:20px;">
';



echo "<h1>Database Details: **$dbname**</h1>";

// ------------------------------------------------------------------
// --- 2. HANDLE AND EXECUTE USER'S SQL QUERY FROM THE FORM ---
// ------------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sql_query'])) {
    
    $user_query = trim($_POST['sql_query']);
    
    echo "<hr><h2>Execution Result for Query:</h2>";
    echo "<pre style='background-color: #ffe0b2; padding: 10px; border: 1px solid #ff9800;'>$user_query</pre>";

    if (!empty($user_query)) {
        
        $query_start_time = microtime(true);
        $result = $conn->query($user_query);
        $query_end_time = microtime(true);
        $execution_time = round(($query_end_time - $query_start_time) * 1000, 2); // Time in ms

        if ($result === TRUE) {
            // Case 1: Successful non-SELECT query (INSERT, UPDATE, DELETE, CREATE, DROP)
            $affected_rows = $conn->affected_rows;
            echo "<p style='color: green; font-weight: bold;'>Query executed successfully in $execution_time ms.</p>";
            echo "<p>Affected Rows: **$affected_rows**</p>";
            
        } elseif ($result !== FALSE) {
            // Case 2: Successful SELECT query with results
            $num_rows = $result->num_rows;
            echo "<p style='color: blue; font-weight: bold;'>Query executed successfully in $execution_time ms. ($num_rows rows returned)</p>";
            
            if ($num_rows > 0) {
                // Retrieve column names for the header
                $field_info = $result->fetch_fields();
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
                
                // Print headers
                echo "<tr>";
                foreach ($field_info as $field) {
                    echo "<th style='padding: 10px; background-color: #f2f2f2;'>{$field->name}</th>";
                }
                echo "</tr>";
                
                // Print data rows
                while($data_row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($data_row as $cell_value) {
                        // Limit display length for readability
                        $display_value = (strlen($cell_value) > 50) ? substr($cell_value, 0, 50) . '...' : $cell_value;
                        echo "<td style='padding: 8px; vertical-align: top;'>$display_value</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } else {
            // Case 3: Execution error
            echo "<p style='color: red; font-weight: bold;'>Query execution failed.</p>";
            echo "<p>Error: " . htmlspecialchars($conn->error) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>Please enter an SQL query to execute.</p>";
    }
}


// ------------------------------------------------------------------
// --- 3. SQL QUERY INPUT FORM ---
// ------------------------------------------------------------------
echo '
<hr>
<h2>✍️ Execute Custom SQL Query</h2>
<form method="POST" action="">
    <textarea name="sql_query" rows="5" cols="80" 
              placeholder="Example: SELECT * FROM users WHERE age > 30;"></textarea>
    <br><br>
    <button type="submit" 
            style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
        Execute Query
    </button>
</form>

<hr>
';

// ------------------------------------------------------------------
// --- 4. DISPLAY EXISTING TABLES, SCHEMA, AND DATA ---
// ------------------------------------------------------------------

$tables_query = "SHOW TABLES";
$tables_result = $conn->query($tables_query);

if ($tables_result->num_rows > 0) {
    
    // Loop through every table
    while($tables_row = $tables_result->fetch_array()) {
        $tableName = $tables_row[0]; 

        echo "<hr>";
        echo '<h2 style="margin-top:10px; border-top:solid 1px #ccc;">📦 Table: '.$tableName.'</h2>';
        
        // --- DISPLAY SCHEMA (DESCRIBE) ---
        echo '<div style="opacity:.5;">';
        echo "<h3>Schema (Fields and Types)</h3>";
        
        $schema_query = "DESCRIBE $tableName";
        $schema_result = $conn->query($schema_query);
        
        if ($schema_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin-bottom:10px;'>";
            echo "<tr>";
            echo "<th style='padding: 5px; background-color: #e9e9e9;'>Field</th>";
            echo "<th style='padding: 5px; background-color: #e9e9e9;'>Type</th>";
            echo "<th style='padding: 5px; background-color: #e9e9e9;'>Null</th>";
            echo "<th style='padding: 5px; background-color: #e9e9e9;'>Key</th>";
            echo "</tr>";
            
            while($column = $schema_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td style='padding: 2px;'>{$column['Field']}</td>";
                echo "<td style='padding: 2px;'>{$column['Type']}</td>";
                echo "<td style='padding: 2px;'>{$column['Null']}</td>";
                echo "<td style='padding: 2px;'>{$column['Key']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Could not retrieve the schema for table $tableName.</p>";
        }
        echo '</div>';

        // --- DISPLAY ALL INSERTED ROWS (SELECT *) ---
        echo "<h3>Contained Data</h3>";
        
        $data_query = "SELECT * FROM $tableName";
        $data_result = $conn->query($data_query);

        if ($data_result === FALSE) {
            echo "<p style='color: red;'>Error executing SELECT query: " . $conn->error . "</p>";
        } elseif ($data_result->num_rows > 0) {
            
            $field_info = $data_result->fetch_fields();
            echo "<table border='1' style='border-collapse: collapse;'>";
            
            // Print table header
            echo "<tr>";
            foreach ($field_info as $field) {
                echo "<th style='padding: 5px; background-color: #f2f2f2;'>{$field->name}</th>";
            }
            echo "</tr>";
            
            // Print data rows
            while($data_row = $data_result->fetch_assoc()) {
                echo "<tr>";
                foreach ($data_row as $cell_value) {
                    $display_value = (strlen($cell_value) > 50) ? substr($cell_value, 0, 50) . '...' : $cell_value;
                    echo "<td style='padding: 2px; vertical-align: top;'>$display_value</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p>No data rows found in table $tableName.</p>";
        }
    }
    
} else {
    echo "<p>No tables found in database **$dbname**.</p>";
}

// Close the connection
$conn->close();

echo '</body>';



?>