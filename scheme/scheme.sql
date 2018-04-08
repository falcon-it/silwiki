/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  ilya
 * Created: 07.04.2018
 */

CREATE DATABASE wiki CHARACTER SET utf8 COLLATE utf8_general_ci;
USE wiki;

CREATE TABLE articles
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    title VARCHAR(128) NOT NULL,
    link TEXT NOT NULL,
    article LONGTEXT NOT NULL,
    size INT NOT NULL,
    INDEX (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE atoms
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    atom VARCHAR(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE links
(
    atom_id INT NOT NULL,
    article_id INT NOT NULL,
    counter INT NOT NULL DEFAULT 1,
    UNIQUE INDEX (atom_id, article_id),
    INDEX (article_id),
    CONSTRAINT FK_atoms_links_id FOREIGN KEY (atom_id) REFERENCES atoms(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_articles_links_id FOREIGN KEY (article_id) REFERENCES  articles(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
