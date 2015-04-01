/* Replace this file with actual dump of your database */
CREATE TABLE tree
(
  id INTEGER PRIMARY KEY NOT NULL,
  label TEXT NOT NULL,
  path TEXT NOT NULL,
  level INTEGER NOT NULL,
  position INTEGER NOT NULL
);

-- INSERT INTO tree VALUES (1, 'root', '.', 0, 0);