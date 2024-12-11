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
    PRIMARY KEY (userID)
    -- user settings
);

-- what books the user has saved in the database
CREATE TABLE IF NOT EXISTS books(
    bookID int,
    userID int,
    bookName char,
    isInFeed bool,
    PRIMARY KEY (bookID)
);

-- the individual book chunks
CREATE TABLE IF NOT EXISTS bookChunks(
    chunkID int,
    bookID int,
    chunkNum int,
    chunkContent char,
    hasBeenSeen bool,
    PRIMARY KEY (chunkID)
);


-- the collected feed for each user
CREATE TABLE IF NOT EXISTS userFeed(
    feedID int,
    numInFeed int,
    chunkID int,
    userID int,
    PRIMARY KEY (feedID, numInFeed)

);