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
);

-- users settings

-- what books the user has saved in the database
CREATE TABLE IF NOT EXISTS collections(

)

-- the individual book chunks

