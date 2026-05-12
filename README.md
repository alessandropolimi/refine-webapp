# PROJECT: Trust Building in Users’ Interaction with Vision-Language Models

# ReFiNe: Interactive Survey & Knowledge Graph Platform

ReFiNe is an advanced web-based platform designed to bridge the gap between traditional qualitative surveys and interactive data visualization. Built on the **LAMP** stack, it allows users to evaluate, provide feedback, and physically manipulate knowledge graphs in real-time.

---

## The Technology Stack: LAMP (XAMPP)

For this project, I chose the **LAMP** architecture, an industry standard for web development, utilizing **XAMPP** as the local server environment for development and testing.

* **Apache (Web Server):** Handles HTTP requests and manages the application's routing. [Apache Official Site](https://httpd.apache.org/).
* **MySQL (Database):** A relational database system used to store user profiles, survey steps, qualitative votes, and the specific "delta" changes made to graphs. [MySQL Official Site](https://www.mysql.com/).
* **PHP (Backend):** The core engine of the application. It handles the modular logic, session management, and secure database interaction via PDO (PHP Data Objects). [PHP Documentation](https://www.php.net/).
* **XAMPP:** Used as a cross-platform solution to integrate Apache, MariaDB/MySQL, and PHP into a unified development environment. [XAMPP Project](https://www.apachefriends.org/).

---

## Interactive Graph Management: Cytoscape.js

The standout feature of this application is the integration of **Cytoscape.js**, a professional-grade graph theory library for visualization and analysis.

* **Why Cytoscape.js?** It provides a powerful API for rendering complex networks and supports rich user interactions like dragging, zooming, and automated layouts.
* **Custom Graph Editor Extension:** Since Cytoscape.js is primarily a visualization tool, I developed a custom **Graph Editor Extension** for this project. This allows users to:
    * **Rename nodes and edges** through an intuitive floating editor UI.
    * **Delete elements** to refine or correct the knowledge graph structure.
    * **Real-time State Tracking:** Every modification is captured and synchronized with the MySQL backend without reloading the page.

🔗 [Official Cytoscape.js Documentation](https://js.cytoscape.org/)

---

## Database Architecture

The system utilizes a relational schema designed for maximum flexibility, supporting an unlimited number of steps per survey and detailed tracking of user interactions.



### Table Structure:
1.  **`user`**: Stores demographic data and hashed credentials.
    * *Fields:* `username` (PK), `age`, `gender`, `password`, `variant`.
2.  **`survey`**: Records each unique survey session.
    * *Fields:* `id` (PK), `user_username` (FK), `date`.
3.  **`survey_step`**: A flexible table that stores Likert-scale votes and text comments for every stage of the survey. It tracks metrics like "Trust" and "Reliance" both before and after user interaction with the graph.
4.  **`survey_graph`**: A specialized table for **structural changes**. Instead of saving a copy of the entire graph, it records only the "deltas" (new labels or deletions) for each element, optimized for data analysis.

---

## System Architecture & Modular Breakdown

The application follows a modular PHP architecture to ensure a clean separation of concerns.

### 1. Webapp Root (The Core)
* `index.php`: The primary entry point and home page.
* `init.php`: The backbone of the app, containing global initializations (DB credentials, session start) included in every module.

### 2. Authentication Manager
Manages the security layer and user lifecycle.
* **Login & Signup Managers:** Facilitate secure access and account creation.
* **Logout Manager:** Ensures complete session termination.

### 3. User & Admin Manager
* **User Dashboard:** Personalized hub for registered participants.
* **Admin Manager:** Restricted interface for authorized users to monitor global survey trends.

### 4. Survey & Graph Engine (Core Logic)
* **New Survey Manager:** Handles the generation of new datasets.
* **Survey Form Manager:** Orchestrates the interactive session using JavaScript and Cytoscape.
    * **Graphs Manager:** Manages the loading and visual editing of nodes and edges.
    * **Votes Manager:** Collects quantitative and qualitative feedback.
* **Form Submission Manager:** Synchronizes UI states with the database.

### 5. Download & Export Manager
Exclusively for administrative use.
* **JSON Manager:** Transforms complex database records into structured JSON files for external research and data analysis.

---

## Installation & Setup

1.  Download and install **XAMPP**.
2.  Clone this repository into the `htdocs` directory.
3.  Create a new database in **phpMyAdmin** and import the SQL schema.
4.  Update your database credentials in `init.php`.
5.  Launch your browser and navigate to `http://localhost/`.

---

## '_misc' folder

Here there are some files not directly connected to the webapp. \
In particular in the "Parse_JSON.ipynb" file there are all the steps to convert AI-generated STAR outputs into a JSON file for the Cytoscape library. \
Run both notebooks on Google Colab because they rely on environment functions like "google.colab.files.upload()". \
The "db.sql" file contains the definitions of the SQL tables of the database. \
The "db.php" is a file that I've used to inspect the database without accessing to the phpMyAdmin (because in the Politecnico DB the access is done by VPN and with this file has been easier).

---

## File tree

```text
/
├── init.php                         Global initialization, dataset paths, variant logic
├── index.php                        Landing page
├── README.md                        Main project documentation
├── _misc/
│   ├── Parse_JSON.ipynb             Colab notebook for AI-generated STAR outputs
│   ├── db.sql                       SQL schema
│   └── db.php                       DB inspection helper
├── assets/
│   ├── graph.min.json               Active AI dataset used by the survey frontend
│   ├── icon/                        UI icons and assets
│   └── video/                       Survey videos
├── auth/
│   ├── login.php                    Login page
│   ├── signup.php                   Signup page
│   ├── logout.php                   Logout handler
│   ├── login/
│   └── signup/
├── user/
│   ├── index.php                    User dashboard
│   └── admin/
│       └── admin.php                Admin dashboard
├── survey/
│   ├── index.php                    Survey controller/router
│   ├── new.php                      New survey generation and sampling
│   ├── form.php                     Main survey interface
│   └── form/
│       ├── getdata.php              Survey-state loader
│       ├── submit.php               Frontend submission orchestrator
│       ├── graph.php                Graph UI manager
│       ├── votes.php                Voting UI manager
│       ├── graph/
│       │   ├── change.php
│       │   ├── load.php
│       │   └── change/editor.php
│       ├── submit/
│       │   ├── save_graph.php
│       │   └── save_votes.php
│       └── votes/
│           ├── change.php
│           └── load.php
├── download/
│   ├── json.php                     Admin JSON export entry point
│   └── json/
│       ├── export.php
│       ├── answer.php
│       └── graph.php
```

---

## SQL Table definitions

Run the next SQL queries to initialize the tables. \
\
-- table for users \
CREATE TABLE IF NOT EXISTS `user` ( \
    `username` VARCHAR(64) NOT NULL PRIMARY KEY, \
    `age` INT NOT NULL DEFAULT 0, \
    `gender` VARCHAR(1) NOT NULL DEFAULT "0", -- "m", "f", "o", "0" \
    `password` TEXT NOT NULL \
); \
\
-- table for each survey \
CREATE TABLE IF NOT EXISTS `survey` ( \
    `id` BIGINT UNSIGNED NOT NULL \
        AUTO_INCREMENT PRIMARY KEY, \
    `user_username` VARCHAR(64) NOT NULL \
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION, \
    `date` DATE \
); \
\
-- table for each answers (votes and comments) for each step of each survey \
-- with this table we are flexible in the number of steps for each survey (not fixed) \
CREATE TABLE IF NOT EXISTS `survey_step` ( \
    `user_username` VARCHAR(64) NOT NULL \
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION, \
    `survey_id` BIGINT UNSIGNED NOT NULL \
    	REFERENCES `survey`(`id`) ON UPDATE CASCADE ON DELETE NO ACTION, \
    `step` INT UNSIGNED NOT NULL, \
    `video_id` VARCHAR(128) NOT NULL, \
    -- page 1 ------- \
    `answer_rely_before` INT UNSIGNED DEFAULT NULL, \
    `answer_rely_before_comment` TEXT DEFAULT NULL, \
    `answer_user_before` VARCHAR(50) DEFAULT NULL, \
    `answer_trustmeter_before` INT UNSIGNED DEFAULT NULL, \
    `answer_trustmeter_before_comment` TEXT DEFAULT NULL, \
    -- page 3 ------- \
    `answer_rely_answer` INT UNSIGNED DEFAULT NULL, \
    `answer_rely_answer_comment` TEXT DEFAULT NULL, \
    `answer_rely_graph` INT UNSIGNED DEFAULT NULL, \
    `answer_rely_graph_comment` TEXT DEFAULT NULL, \
    `answer_comment` TEXT DEFAULT NULL, \
    `answer_trustmeter_after` INT UNSIGNED DEFAULT NULL, \
    `answer_trustmeter_after_comment` TEXT DEFAULT NULL, \
    -- --------------- \
    PRIMARY KEY (`user_username`, `survey_id`, `step`) \
); \
\
-- table for each change in graphs (for each survey) \
CREATE TABLE IF NOT EXISTS `survey_graph` ( \
    `user_username` VARCHAR(64) NOT NULL \
        REFERENCES `user`(`username`) ON UPDATE CASCADE ON DELETE NO ACTION, \
    `survey_id` BIGINT UNSIGNED NOT NULL \
    	REFERENCES `survey`(`id`) ON UPDATE CASCADE ON DELETE NO ACTION, \
    `video_id` VARCHAR(128) NOT NULL, \
    -- we take the id of the video and NOT the index in the graph.json because this list in the \
    -- json can change (so the video index too) while the id of the video no \
    `video_frame` BIGINT UNSIGNED NOT NULL, \
    `graph_element_id` VARCHAR(128) NOT NULL, \
    `graph_element_type` VARCHAR(4) NOT NULL, -- "node", "edge" \
    `graph_element_newlabel` VARCHAR(128), \
    -- if graph_element_newlabel NULL or empty means that the element id is removed \
    PRIMARY KEY (`user_username`, `survey_id`, `video_id`, `video_frame`, `graph_element_id`) \
); \

### Dataset and data processing flow

The survey content is based on the **STAR** benchmark. Credits for the dataset, benchmark design, and original annotations go to the STAR project and repository: <https://github.com/csbobby/STAR_Benchmark>.

---

## Credits
This project was developed by Alessandro Valente at Politecnico of Milan AIRLab laboratory in the field of Knowledge Representation and Human-Computer Interaction (HCI).

