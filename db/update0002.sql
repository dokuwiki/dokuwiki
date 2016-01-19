-- what schemas are currently assigned to a page
-- we do not store any timestamps or schemaIDs here because this always holds the
-- current state only. Old states are determined by the actually saved data for a page
-- at that time
CREATE TABLE schema_assignments (
    assign NOT NULL,
    tbl NOT NULL,
    PRIMARY KEY(assign, tbl)
);
