-- ------------------------------------------------------------------------------------------
-- create database---------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


DROP DATABASE IF EXISTS bookChunk;

CREATE DATABASE bookChunk;

USE bookChunk;


-- ------------------------------------------------------------------------------------------
-- bookChunk tables--------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------

-- Create the users table with utf8mb4 character set for the pass field
CREATE TABLE IF NOT EXISTS users (
    userID INT NOT NULL AUTO_INCREMENT,
    userName CHAR(255) NOT NULL,
    userLevel INT DEFAULT 0,
        -- 0 = normal, 1 = teacher, 2 = admin
    pass VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    realFirstName CHAR(255) NOT NULL,
    realLastName CHAR(255) NOT NULL,
    email CHAR(255) NOT NULL,
    numChunksSeen INT DEFAULT 0,

    maxWordsPerChunk INT DEFAULT 50,

    textToVoice INT DEFAULT 0,
        -- 0 requires a button press to play audio
        -- 1 automatically plays audio when new chunk loads

    autoLogin INT DEFAULT 0,
        -- 0 does nothing
        -- 1 logs into the system automatically with cookies
    
    fontSize VARCHAR(10) DEFAULT '16px',
    fontColor VARCHAR(7) DEFAULT '#000000',
    backgroundColor VARCHAR(7) DEFAULT '#FFFFFF',
    
    lineHeight VARCHAR(7) DEFAULT '1.5',
    
    highlightColor VARCHAR(7) DEFAULT '#FFFF00',
    highlightingToggle INT DEFAULT 0,
        -- 0 does nothing
        -- 1 turns on highlighting a line

    buttonColor VARCHAR(7) DEFAULT '#A9A9A9',
    buttonHoverColor VARCHAR(7) DEFAULT '#696969',
    buttonTextColor VARCHAR(7) DEFAULT '#FFFFFF',

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

-- the books that are added to the feed
CREATE TABLE IF NOT EXISTS booksInFeed (
    id INT AUTO_INCREMENT,
    feedID INT NOT NULL,
    bookID INT NOT NULL,
    position INT NOT NULL,
    PRIMARY KEY (id)
);

-- how far through a feed a user is
CREATE TABLE IF NOT EXISTS userFeedProgress (
    userID INT NOT NULL,
    feedID INT NOT NULL,
    lastSeenChunkID INT NOT NULL,
    dateTimeLastSeen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userID, feedID)
);

-- recommendations of bugs submitted by users
CREATE TABLE IF NOT EXISTS userRecomendations (
    id INT AUTO_INCREMENT,
    userID INT NOT NULL,
    recomendationText LONGTEXT NOT NULL,
    dateTimeSubmitted DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- collections that users have created
CREATE TABLE IF NOT EXISTS collections (
    collectionID INT AUTO_INCREMENT,
    collectionType CHAR(16) NOT NULL,
    collectionName CHAR(255) NOT NULL,
    userID INT NOT NULL,
    PRIMARY KEY (collectionID)
);

-- the items in specific collections
CREATE TABLE IF NOT EXISTS itemsInCollection (
    indexID INT AUTO_INCREMENT,
    collectionID INT NOT NULL,
    itemID INT NOT NULL, -- does not have foreign key constraint, could come form multiple tables
    positionID INT NOT NULL, -- the position this item is in the collection
    PRIMARY KEY (indexID)
);

-- static records that can be pulled. includes html or php that can be added to the website
CREATE TABLE IF NOT EXISTS staticChunk (
    staticID INT AUTO_INCREMENT,
    staticContent LONGTEXT NOT NULL,
    staticDescription LONGTEXT NOT NULL,
    PRIMARY KEY (staticID)
);

-- flash cards the user can create and add to the feed
CREATE TABLE IF NOT EXISTS flashCards (
    cardID INT AUTO_INCREMENT,
    userID INT NOT NULL,
    cardName CHAR(255) NOT NULL,
    cardContent CHAR(255) NOT NULL,
    cardAnswer CHAR(255) NOT NULL,
    PRIMARY KEY (cardID)
);

-- friend relationships
CREATE TABLE IF NOT EXISTS friends (
    friendID INT AUTO_INCREMENT,
    userID1 INT NOT NULL,
    userID2 INT NOT NULL,
    dateFriended DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (friendID)
);

-- keeps track of chunks seen per user per week
CREATE TABLE IF NOT EXISTS chunksSeenPerWeek (
    id INT AUTO_INCREMENT,
    userID INT NOT NULL,
    weekStartDate DATETIME NOT NULL,
    weekEndDate DATETIME NOT NULL,
    chunksSeenInWeek INT,
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

ALTER TABLE userRecomendations
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE collections
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE itemsInCollection
ADD FOREIGN KEY (collectionID) REFERENCES collections(collectionID);

ALTER TABLE flashCards
ADD FOREIGN KEY (userID) REFERENCES users(userID);

ALTER TABLE chunksSeenPerWeek
ADD FOREIGN KEY (userID) REFERENCES users(userID);


-- ------------------------------------------------------------------------------------------
-- Add Values--------------------------------------------------------------------------------
-- ------------------------------------------------------------------------------------------


-- add static items