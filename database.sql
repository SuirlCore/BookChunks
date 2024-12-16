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

-- full texts uploaded to the database by the user, not parsed into chunks yet
CREATE TABLE fullTexts (
    textID INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    text LONGTEXT NOT NULL,
    owner int NOT NULL
);


-- ------------------------------------------------------------------------------------------
-- Foreign Keys------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


ALTER TABLE bookChunks
ADD FOREIGN KEY (bookID) REFERENCES fullTexts(textID);

ALTER TABLE feeds
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE userFeed
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE fullTexts
ADD FOREIGN KEY (owner) REFERENCES users(userID);


-- ------------------------------------------------------------------------------------------
-- Add Values--------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


INSERT INTO users (userName, pass, realFirstName, realLastName, email) VALUES ("public", "blank", "public", "user", "email@website.com");
