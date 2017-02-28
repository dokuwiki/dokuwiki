CREATE TEMPORARY TABLE schemas_temp (
    id INTEGER,
    tbl,
    ts,
    islookup INTEGER,
    user,
    comment,
    editors
);

INSERT INTO schemas_temp (id, tbl, ts, islookup, user, comment, editors) SELECT id, tbl, ts, islookup, user, comment, editors FROM schemas;

DROP TABLE schemas;

CREATE TABLE schemas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tbl,
    ts,
    islookup INTEGER,
    user,
    comment,
    config NOT NULL DEFAULT '{}'
);

INSERT INTO schemas (id, tbl, ts, islookup, user, comment, config) SELECT id, tbl, ts, islookup, user, comment, '{"allowed editors":"' || editors || '"}' FROM schemas_temp;

DROP TABLE schemas_temp;
