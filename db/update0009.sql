CREATE TEMPORARY TABLE schemas_temp (
    id TEXT,
    tbl TEXT,
    ts TEXT
);

INSERT INTO schemas_temp (id, tbl, ts) SELECT id, tbl, ts FROM schemas;

DROP TABLE schemas;

CREATE TABLE schemas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tbl,
    ts,
    user,
    comment
);

INSERT INTO schemas (id, tbl, ts) SELECT id, tbl, ts FROM schemas_temp;

DROP TABLE schemas_temp;
