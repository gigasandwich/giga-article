\c postgres;
DROP DATABASE IF EXISTS 'giga_article';
CREATE DATABASE 'giga_article';
\c giga_article;

CREATE TABLE article(
   id SERIAL,
   title TEXT NOT NULL,
   url TEXT NOT NULL,
   cover TEXT NOT NULL,
   content TEXT NOT NULL,
   created_at TIMESTAMP NOT NULL,
   PRIMARY KEY(id),
   UNIQUE(url),
   UNIQUE(cover)
);