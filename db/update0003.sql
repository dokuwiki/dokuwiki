-- stores multi values for all tables, we might split this later into one multival
-- table per data table
CREATE TABLE multivals (
    tbl NOT NULL,
    colref INTEGER NOT NULL,
    pid NOT NULL,
    rev INTEGER NOT NULL,
    row INTEGER NOT NULL,
    value,
    PRIMARY KEY(tbl, colref, pid, rev, row)
);
