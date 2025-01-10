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
    numChunksSeen int,
    PRIMARY KEY (userID)
);

ALTER TABLE users MODIFY COLUMN numChunksSeen INT DEFAULT 0;

-- the individual book chunks
CREATE TABLE IF NOT EXISTS bookChunks(
    chunkID int NOT NULL AUTO_INCREMENT,
    bookID int NOT NULL,
    chunkNum int NOT NULL,
    chunkContent longtext NOT NULL,
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

-- full texts that have been uploaded, names and owners
CREATE TABLE IF NOT EXISTS fullTexts(
    textID INT AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    owner int NOT NULL,
    PRIMARY KEY (textID)
);

CREATE TABLE IF NOT EXISTS booksInFeed (
    id INT AUTO_INCREMENT,
    feedID INT NOT NULL,
    bookID INT NOT NULL,
    position INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS userFeedProgress (
    userID INT NOT NULL,
    feedID INT NOT NULL,
    lastSeenChunkID INT NOT NULL,
    dateTimeLastSeen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userID, feedID)
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

ALTER TABLE booksInFeed
ADD FOREIGN KEY (feedID) REFERENCES feeds(feedID);

ALTER TABLE booksInFeed
ADD FOREIGN KEY (bookID) REFERENCES fullTexts(textID);

ALTER TABLE userFeedProgress
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE userFeedProgress
ADD FOREIGN KEY (feedID) REFERENCES feeds(feedID);

ALTER TABLE userFeedProgress
ADD FOREIGN KEY (lastSeenChunkID) REFERENCES bookChunks(chunkID);


-- ------------------------------------------------------------------------------------------
-- Add Values--------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------

