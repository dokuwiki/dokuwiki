-- Defines the schema of one class of data
CREATE TABLE schemas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tbl NOT NULL,
    ts INT NOT NULL,
    chksum DEFAULT ''
);

-- Stores the configured type of a single column in a schema
CREATE TABLE types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class NOT NULL,
    ismulti BOOLEAN DEFAULT 0,
    label DEFAULT '',
    config DEFAULT ''
);

-- Store what columns of which type are there in a schema
CREATE TABLE schema_cols (
    sid INTEGER REFERENCES schemas (id),
    colref INTEGER NOT NULL,
    enabled BOOLEAN DEFAULT 1,
    tid INTEGER REFERENCES types (id),
    sort INTEGER NOT NULL,
    PRIMARY KEY ( sid, colref)
);
