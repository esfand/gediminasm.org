
CREATE TABLE posts (
  id SERIAL NOT NULL PRIMARY KEY,
  slug varchar(127) NOT NULL,
  title varchar(127) NOT NULL,
  summary text NOT NULL,
  content text NOT NULL,
  views bigint NOT NULL DEFAULT 0,
  updated timestamp NOT NULL DEFAULT NOW(),
  created timestamp NOT NULL DEFAULT NOW()
);
CREATE INDEX posts_created_index ON posts (created);
CREATE UNIQUE INDEX posts_slug_index ON posts (slug);

CREATE TABLE comments (
  id SERIAL NOT NULL PRIMARY KEY,
  author varchar(127) DEFAULT NULL,
  subject varchar(127) NOT NULL,
  content text NOT NULL,
  post_id integer REFERENCES posts (id) ON DELETE CASCADE DEFERRABLE,
  created timestamptz NOT NULL DEFAULT NOW()
);
CREATE INDEX comments_created_index ON comments (created);
CREATE INDEX comments_post_id ON comments (post_id);

CREATE TABLE messages (
  id SERIAL NOT NULL PRIMARY KEY,
  sender varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  content text NOT NULL
);

