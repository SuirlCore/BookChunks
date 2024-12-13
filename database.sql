-- ------------------------------------------------------------------------------------------
-- create database---------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


DROP DATABASE IF EXISTS bookChunk;

CREATE DATABASE bookChunk;

USE bookChunk;


-- ------------------------------------------------------------------------------------------
-- bookChunk tables--------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------

-- the users in the system
CREATE TABLE IF NOT EXISTS users(
    userID int NOT NULL AUTO_INCREMENT,
    userName char(255) NOT NULL,
    pass char(255) NOT NULL,
    realFirstName char(255) NOT NULL,
    realLastName char(255) NOT NULL,
    email char(255) NOT NULL,
    -- user settings
    PRIMARY KEY (userID)
);

-- what books the user has saved in the database
CREATE TABLE IF NOT EXISTS books(
    bookID int NOT NULL AUTO_INCREMENT,
    userID int NOT NULL,
    bookName char(255) NOT NULL,
    isInFeed tinyint(1) NOT NULL,
    PRIMARY KEY (bookID)
);

-- the individual book chunks
CREATE TABLE IF NOT EXISTS bookChunks(
    chunkID int NOT NULL AUTO_INCREMENT,
    bookID int NOT NULL,
    chunkNum int NOT NULL,
    chunkContent char(255) NOT NULL,
    hasBeenSeen tinyint(1) NOT NULL,
    PRIMARY KEY (chunkID)
);

-- different feeds that the user can choose from
CREATE TABLE IF NOT EXISTS feeds(
    feedID int NOT NULL AUTO_INCREMENT,
    userID int NOT NULL,
    feedName char(255) NOT NULL,
    feedDescription char(255),
    PRIMARY KEY (feedID)
);

-- the collected chunks for each feed for each user
CREATE TABLE IF NOT EXISTS userFeed(
    feedID int NOT NULL,
    numInFeed int NOT NULL,
    chunkID int NOT NULL,
    userID int NOT NULL,
    PRIMARY KEY (feedID, numInFeed)
);


-- ------------------------------------------------------------------------------------------
-- Foreign Keys------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


ALTER TABLE books
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE bookChunks
ADD FOREIGN KEY (bookID) REFERENCES books(bookID);

ALTER TABLE feeds
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE userFeed
ADD FOREIGN KEY (userID) REFERENCES users(userID);

-- ------------------------------------------------------------------------------------------
-- Add Values--------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


INSERT INTO users (userName, pass, realFirstName, realLastName, email) VALUES ("public", "blank", "public", "user", "email@website.com");


-- ------------------------------------------------------------------------------------------
-- Stored Procedures-------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


-- procedure to check the database to see if a username already exists
-- returns either true or false
-- CALL checkUserName(stringIn, @result);
DELIMITER //
CREATE PROCEDURE checkUserName ( IN stringIn char(255) OUT @result char (8))
BEGIN
    IF (SELECT userName FROM users WHERE userName = stringIn) IS NULL THEN
		SET result = "False";
	ELSE
        SET result = "True";
    END IF;
END //
DELIMITER ;