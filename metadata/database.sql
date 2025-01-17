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
    userID INT NOT NULL AUTO_INCREMENT,
    userName CHAR(255) NOT NULL,
    userLevel INT DEFAULT 0,
    pass CHAR(255) NOT NULL,
    realFirstName CHAR(255) NOT NULL,
    realLastName CHAR(255) NOT NULL,
    email CHAR(255) NOT NULL,
    numChunksSeen INT DEFAULT 0,
    PRIMARY KEY (userID)
);

-- the individual book chunks
CREATE TABLE IF NOT EXISTS bookChunks(
    chunkID INT NOT NULL AUTO_INCREMENT,
    bookID INT NOT NULL,
    chunkNum INT NOT NULL,
    chunkContent LONGTEXT NOT NULL,
    hasBeenSeen TINYINT(1) NOT NULL,
    PRIMARY KEY (chunkID)
);

-- different feeds that the user can choose from
CREATE TABLE IF NOT EXISTS feeds(
    feedID INT NOT NULL AUTO_INCREMENT,
    userID INT NOT NULL,
    feedName CHAR(255) NOT NULL,
    feedDescription char(255),
    PRIMARY KEY (feedID)
);

-- the collected chunks for each feed for each user
CREATE TABLE IF NOT EXISTS userFeed(
    feedID INT NOT NULL,
    numInFeed INT NOT NULL,
    chunkID INT NOT NULL,
    userID INT NOT NULL,
    PRIMARY KEY (feedID, numInFeed)
);

-- full texts that have been uploaded, names and owners
CREATE TABLE IF NOT EXISTS fullTexts(
    textID INT AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    owner INT NOT NULL,
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

CREATE TABLE IF NOT EXISTS userRecomendations (
    id INT AUTO_INCREMENT,
    userID INT NOT NULL,
    recomendationText LONGTEXT NOT NULL,
    dateTimeSubmitted DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
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

