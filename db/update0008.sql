-- keeps the title of pages for easy selection
CREATE TABLE titles (
    pid NOT NULL,
    title NOT NULL,
    PRIMARY KEY(pid)
);

-- fill with page names
INSERT INTO titles SELECT DISTINCT pid, pid FROM schema_assignments;
