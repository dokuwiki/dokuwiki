-- Defines the schema of one class of data
CREATE TABLE schemas (
    id INT PRIMARY KEY NOT NULL,
    tbl NOT NULL,
    ts INT NOT NULL,
    chksum DEFAULT ''
);

-- Stores the configured type of a single column in a schema
CREATE TABLE types (
    id INT PRIMARY KEY NOT NULL,
    class NOT NULL,
    multi BOOLEAN DEFAULT 0,
    label DEFAULT '',
    config DEFAULT ''
);

-- Store what columns of which type are there in a schema
CREATE TABLE schema_cols (
    schema_id REFERENCES schema (id),
    col NOT NULL,
    type_id REFERENCES type (id),
    sort INT NOT NULL,
    PRIMARY KEY ( schema_id, col)
);
