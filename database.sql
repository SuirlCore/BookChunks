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
    isInFeed bool NOT NULL,
    PRIMARY KEY (bookID)
);

-- the individual book chunks
CREATE TABLE IF NOT EXISTS bookChunks(
    chunkID int NOT NULL AUTO_INCREMENT,
    bookID int NOT NULL,
    chunkNum int NOT NULL,
    chunkContent char(255) NOT NULL,
    hasBeenSeen bool NOT NULL,
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

ALTER TABLE bookchunks
ADD FOREIGN KEY (bookID) REFERENCES books(bookID);

ALTER TABLE feeds
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE userFeed
ADD FOREIGN KEY (userID) REFERENCES users(userID);