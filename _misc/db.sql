

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