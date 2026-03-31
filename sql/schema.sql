\c postgres;
DROP DATABASE IF EXISTS giga_article;
CREATE DATABASE giga_article;
\c giga_article;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(249) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(249) NOT NULL UNIQUE,
    verified BOOLEAN NOT NULL DEFAULT FALSE,
    resettable BOOLEAN NOT NULL DEFAULT TRUE,
    status INT NOT NULL DEFAULT 0,
    roles_mask INT NOT NULL DEFAULT 0,
    registered BIGINT NOT NULL,
    last_login BIGINT, -- BIGINT, not timestamp ??
    force_logout BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE users_throttling (
    bucket VARCHAR(255) PRIMARY KEY,
    tokens NUMERIC(15, 6) NOT NULL,
    replenished_at BIGINT NOT NULL,
    expires_at BIGINT NOT NULL
);

CREATE TABLE users_audit_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    event_at BIGINT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    admin_id INT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    details_json TEXT
);

CREATE TABLE users_2fa (
    user_id INT PRIMARY KEY,
    mechanism VARCHAR(50) NOT NULL,
    seed VARCHAR(255) NOT NULL,
    expires_at BIGINT NOT NULL DEFAULT 0
);

CREATE TABLE article(
   id SERIAL,
   title TEXT NOT NULL,
   url TEXT NOT NULL,
   cover TEXT,
   content TEXT NOT NULL,
   created_at TIMESTAMP NOT NULL,
   PRIMARY KEY(id),
   UNIQUE(url),
   UNIQUE(cover)
);

CREATE TABLE picture(
   Id SERIAL,
   url VARCHAR(512) ,
   article_id INTEGER NOT NULL,
   PRIMARY KEY(Id),
   UNIQUE(url),
   FOREIGN KEY(article_id) REFERENCES article(id)
);

CREATE TABLE article_historic (
    id SERIAL PRIMARY KEY,
    article_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    cover TEXT,
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    version INTEGER NOT NULL,
    FOREIGN KEY(article_id) REFERENCES article(id) ON DELETE CASCADE
);
