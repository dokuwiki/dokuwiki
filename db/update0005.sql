CREATE TABLE schema_assignments_patterns (
    pattern NOT NULL,
    tbl NOT NULL,
    PRIMARY KEY(pattern, tbl)
);
INSERT INTO schema_assignments_patterns SELECT * FROM schema_assignments;
DELETE FROM schema_assignments;
