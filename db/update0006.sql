DROP TABLE schema_assignments;
-- this table now will hold information about all pages that ever had a schema assigned
-- which is basically the same as all pages that ever had struct data saved
CREATE TABLE schema_assignments (
    pid NOT NULL,
    tbl NOT NULL,
    assigned BOOLEAN NOT NULL DEFAULT 1,
    PRIMARY KEY(pid, tbl)
);
