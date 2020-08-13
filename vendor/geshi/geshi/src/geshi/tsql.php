<?php
/*************************************************************************************
 * tsql.php
 * --------
 * Author: Duncan Lock (dunc@dflock.co.uk)
 * Copyright: (c) 2006 Duncan Lock (http://dflock.co.uk/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.9.1
 * Date Started: 2005/11/22
 *
 * T-SQL language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/01/23 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2006/01/23)
 * -------------------------
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'T-SQL',
    'COMMENT_SINGLE' => array(1 => '--'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'"),
    'HARDQUOTE' => array("N'", "'"),
    'ESCAPE_CHAR' => '[',
    'KEYWORDS' => array(
        1 => array(
            /*
                Built-in functions
                Highlighted in pink.
                Moved these to first array so that functions like @@ROWCOUNT
                weren't broken up into @@ in black and ROWCOUNT in blue
                This would prevent the correct pink coloring from taking place.
                Not sure of any other downsides to this.
            */

            //Configuration Functions
            '@@DATEFIRST','@@OPTIONS','@@DBTS','@@REMSERVER','@@LANGID','@@SERVERNAME',
            '@@LANGUAGE','@@SERVICENAME','@@LOCK_TIMEOUT','@@SPID','@@MAX_CONNECTIONS',
            '@@TEXTSIZE','@@MAX_PRECISION','@@VERSION','@@NESTLEVEL',

            //Cursor Functions
            '@@CURSOR_ROWS','@@FETCH_STATUS',

            //Date and Time Functions
            'DATEADD','DATEDIFF','DATENAME','DATEPART','GETDATE','GETUTCDATE',
            'DATEFROMPARTS','DATETIMEFROMPARTS', 'SMALLDATETIMEFROMPARTS', 'DATETIME2FROMPARTS', 'TIMEFROMPARTS',
            'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'SYSDATETIME', 'SYSUTCDATETIME',
            'YEAR', 'QUARTER', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'SECOND',
            'EOMONTH',

            //Mathematical Functions
            'ABS','DEGREES','RAND','ACOS','EXP','ROUND','ASIN','FLOOR','SIGN',
            'ATAN','LOG','SIN','ATN2','LOG10','SQUARE','CEILING','PI','SQRT','COS',
            'POWER','TAN','COT','RADIANS', 'RANK',

            //Meta Data Functions
            'COL_LENGTH','COL_NAME','FULLTEXTCATALOGPROPERTY',
            'COLUMNPROPERTY','FULLTEXTSERVICEPROPERTY','DATABASEPROPERTY','INDEX_COL',
            'DATABASEPROPERTYEX','INDEXKEY_PROPERTY','DB_ID','INDEXPROPERTY','DB_NAME',
            'OBJECT_ID','FILE_ID','OBJECT_NAME','FILE_NAME','OBJECTPROPERTY','FILEGROUP_ID',
            '@@PROCID','FILEGROUP_NAME','SQL_VARIANT_PROPERTY','FILEGROUPPROPERTY',
            'TYPEPROPERTY','FILEPROPERTY','OBJECT_SCHEMA_NAME', 'SCHEMA_ID', 'SCHEMA_NAME', 'OBJECT_DEFINITION',

            //Security Functions
            'IS_SRVROLEMEMBER','SUSER_SID','SUSER_SNAME','USER_NAME', 'SUSER_NAME', 'USER_ID',
            'HAS_DBACCESS','IS_MEMBER', 'CURRENT_USER',

            //String Functions
            'ASCII','SOUNDEX','PATINDEX','CHARINDEX','REPLACE','STR','LEFT', 'RIGHT','DATALENGTH','HASHBYTES',
            'DIFFERENCE','QUOTENAME','STUFF','REPLICATE','SUBSTRING','LEN',
            'REVERSE','UNICODE','LOWER','UPPER','LTRIM','RTRIM','FORMAT','TRY_CONVERT','TRY_PARSE','PARSE','CONVERT','CONCAT',

            //System Functions
            'APP_NAME','COLLATIONPROPERTY','@@ERROR','FORMATMESSAGE',
            'GETANSINULL','HOST_ID','HOST_NAME','IDENT_CURRENT','IDENT_INCR',
            'IDENT_SEED','@@IDENTITY','ISDATE','ISNUMERIC','PARSENAME','PERMISSIONS','PROGRAM_NAME',
            '@@ROWCOUNT','ROWCOUNT_BIG','SCOPE_IDENTITY','SERVERPROPERTY','SESSIONPROPERTY',
            'STATS_DATE','@@TRANCOUNT',

            // Error handling
            'ERROR_STATE', 'ERROR_SEVERITY', 'ERROR_NUMBER', 'ERROR_MESSAGE', 'ERROR_LINE', 'ERROR_PROCEDURE',

            //System Statistical Functions
            '@@CONNECTIONS','@@PACK_RECEIVED','@@CPU_BUSY','@@PACK_SENT',
            '@@TIMETICKS','@@IDLE','@@TOTAL_ERRORS','@@IO_BUSY',
            '@@TOTAL_READ','@@PACKET_ERRORS','@@TOTAL_WRITE',

            //Text and Image Functions
            'TEXTPTR','TEXTVALID',

            //Aggregate functions
            'AVG', 'MAX', 'BINARY_CHECKSUM', 'MIN', 'CHECKSUM', 'SUM', 'CHECKSUM_AGG',
            'STDEV', 'COUNT', 'STDEVP', 'COUNT_BIG', 'VAR', 'VARP', 'ROW_NUMBER',
            'LAG', 'LEAD', 'PERCENT_RANK', 'CUME_DIST', 'FIRST_VALUE', 'LAST_VALUE',
            'PERCENTILE_CONT', 'PERCENTILE_DESC',

            // Logic functions
            'CHOOSE', 'IIF', 'ISNULL', 'COALESCE', 'NULLIF',

            // GUID
            'NEWID', 'NEWSEQUENTIALID',
        ),
        2 => array(
            // Datatypes
            'bigint', 'tinyint', 'money',
            'smallmoney', 'datetime', 'smalldatetime',
            'text', 'nvarchar', 'ntext', 'varbinary', 'image',
            'sql_variant', 'uniqueidentifier', 'smallint', 'int',
            'xml', 'hierarchyid', 'geography', 'geometry', 'varchar', 'char', 'nchar',
            'numeric', 'decimal', 'bit', 'sysname', 'date', 'time', 'datetime2', 'datetimeoffset',

            // Keywords
            'ABSOLUTE', 'ACTION', 'ADD', 'ADMIN', 'AFTER', 'AGGREGATE', 'ALIAS', 'ALLOCATE', 'ALLOWED', 'ALTER', 'ARE', 'ARRAY', 'AS',
            'ASC', 'ASSERTION', 'AT', 'ATOMIC', 'AUDIT', 'AUTHORIZATION', 'AVAILABILITY', 'BACKUP', 'BEFORE', 'BEGIN', 'BINARY', 'BLOB', 'BOOLEAN', 'BOTH', 'BREADTH',
            'BREAK', 'BROWSE', 'BUCKET_COUNT', 'BULK', 'BY', 'CACHE', 'CALL', 'CASCADE', 'CASCADED', 'CASE', 'CAST', 'CATALOG', 'CHARACTER', 'CHECK', 'CHECKCONSTRAINTS',
            'CHECKDB', 'CHECKPOINT',
            'CLASS', 'CLEAR', 'CLOB', 'CLOSE', 'CLUSTERED', 'COLLATE', 'COLLATION', 'COLUMN', 'COLUMNSTORE', 'COMMIT',
            'COMMITTED', 'COMPLETION', 'COMPUTE', 'CONFIGURATION',
            'CONNECT', 'CONNECTION', 'CONSTRAINT', 'CONSTRAINTS', 'CONSTRUCTOR', 'CONTAINMENT', 'CONTAINS', 'CONTAINSTABLE', 'CONTINUE', 'CORRESPONDING', 'CREATE',
            'CUBE', 'CURRENT', 'CURRENT_DATE', 'CURRENT_PATH', 'CURRENT_ROLE',
            'CURSOR', 'CYCLE', 'DATA', 'DATA_MIRRORING', 'DATABASE', 'DBCC', 'DEALLOCATE', 'DEC', 'DECLARE', 'DEFAULT', 'DEFERRABLE',
            'DEFERRED', 'DELAY', 'DELAYED_DURABILITY', 'DELETE', 'DENY', 'DEPTH', 'DEREF', 'DESC', 'DESCRIBE', 'DESCRIPTOR', 'DESTROY', 'DESTRUCTOR', 'DETERMINISTIC',
            'DIAGNOSTICS', 'DIALOG', 'DICTIONARY', 'DISABLED', 'DISCONNECT', 'DISK', 'DISTINCT', 'DISTRIBUTED', 'DOMAIN', 'DOUBLE', 'DROP', 'DROPCLEANBUFFERS', 'DROP_EXISTING',
            'DUMMY', 'DUMP', 'DURABILITY', 'DYNAMIC',
            'EACH', 'ELSE', 'END', 'END-EXEC', 'EQUALS', 'ERRLVL', 'ESCAPE', 'EVERY', 'EXCEPT', 'EXCEPTION', 'EXEC', 'EXECUTE', 'EXIT',
            'EXTERNAL', 'FALSE', 'FETCH', 'FILE', 'FILLFACTOR', 'FIRST', 'FLOAT', 'FOR', 'FOR ATTACH', 'FOR ATTACH_REBUILD_LOG', 'FORCESCAN', 'FORCESEEK', 'FOREIGN', 'FOUND', 'FREE',
            'FREEPROCCACHE', 'FREETEXT', 'FREETEXTTABLE',
            'FROM', 'FUNCTION', 'GENERAL', 'GET', 'GLOBAL', 'GO', 'GOTO', 'GRANT', 'GROUP', 'GROUPING', 'HAVING', 'HOLDLOCK', 'HOST',
            'IDENTITY', 'IDENTITY_INSERT', 'IDENTITYCOL', 'IF', 'IGNORE', 'IMMEDIATE', 'INDEX', 'INDICATOR', 'INITIALIZE', 'INITIALLY',
            'INOUT', 'INPUT', 'INSERT', 'INTEGER', 'INTERSECT', 'INTERVAL', 'INTO', 'IS', 'ISOLATION', 'ITERATE', 'KEY',
            'KILL', 'LANGUAGE', 'LARGE', 'LAST', 'LATERAL', 'LEADING', 'LESS', 'LEVEL', 'LIMIT', 'LINENO', 'LOAD', 'LOCAL',
            'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATOR', 'MAP', 'MASTER KEY', 'MATCH', 'MATCHED', 'MEMORY_OPTIMIZED', 'MEMORY_OPTIMIZED_DATA', 'MEMORY_OPTIMIZED_ELEVATE_TO_SNAPSHOT', 'MESSAGE', 'MILLISECOND', 'MODIFIES', 'MODIFY', 'MODULE', 'NAMES', 'NANOSECOND', 'NATIONAL', 'NATIVE_COMPILATION',
            'NATURAL', 'NCLOB', 'NEW', 'NEXT', 'NO', 'NO_INFOMSGS', 'NOCHECK', 'NOCOUNT', 'NONCLUSTERED', 'NONE', 'OBJECT', 'OF',
            'OFF', 'OFFSET', 'OFFSETS', 'OLD', 'ON', 'ONLY', 'OPEN', 'OPENDATASOURCE', 'OPENQUERY', 'OPENROWSET', 'OPENXML', 'OPERATION', 'OPTION',
            'OPTIMIZER_WHATIF',
            'ORDER', 'ORDINALITY', 'OUT', 'OUTPUT', 'OVER', 'OWNER', 'PAD', 'PARAMETER', 'PARTIAL', 'PARTITION', 'PASSWORD', 'PATH', 'PERCENT', 'PLAN',
            'POSTFIX', 'PREFIX', 'PREORDER', 'PREPARE', 'PRESERVE', 'PRECEDING', 'PREVIOUS', 'PRIMARY', 'PRINT', 'PRIOR', 'PRIVILEGES', 'PROC', 'PROCEDURE',
            'PUBLIC', 'QUERYRULEOFF', 'QUERYTRACEON', 'RAISERROR', 'RANGE', 'READ', 'READS', 'READTEXT', 'REAL', 'REBUILD', 'RECEIVE', 'RECONFIGURE', 'RECURSIVE', 'REF', 'REFERENCES', 'REFERENCING', 'RELATIVE',
            'REPEATABLE', 'REPLICATION', 'RESTORE', 'RESTRICT', 'RESULT', 'RETURN', 'RETURNS', 'REVOKE', 'ROLE', 'ROLLBACK', 'ROLLUP', 'ROUTINE', 'ROW', 'ROWCOUNT',
            'ROWGUIDCOL', 'ROWS', 'RULE', 'SAVE', 'SAVEPOINT', 'SCHEMA', 'SCHEMA_AND_DATA', 'SCOPE', 'SCROLL', 'SEARCH', 'SECONDS', 'SECTION', 'SELECT', 'SEND', 'SENT',
            'SEQUENCE', 'SERIALIZABLE', 'SERVER', 'SESSION', 'SESSION_USER', 'SET', 'SETCPUWEIGHT', 'SETIOWEIGHT',
            'SETS', 'SETUSER', 'SHOW_STATISTICS', 'SHOWWEIGHTS', 'SHUTDOWN', 'SIZE', 'SNAPSHOT', 'SPACE', 'SPECIFIC', 'SPECIFICATION',
            'SPECIFICTYPE', 'SQL', 'SQLEXCEPTION', 'SQLPERF', 'SQLSTATE', 'SQLWARNING', 'START', 'STAT_HEADER', 'STATE', 'STATEMENT', 'STATIC', 'STATISTICS', 'STRUCTURE',
            'SYSTEM_USER', 'TABLE', 'TEMPORARY', 'TERMINATE', 'TEXTSIZE', 'THAN', 'THEN', 'THROW', 'TIES', 'TIMESTAMP', 'TIMEZONE_HOUR', 'TIMEZONE_MINUTE',
            'TO', 'TOP', 'TRAILING', 'TRAN', 'TRANSACTION', 'TRANSLATION', 'TREAT', 'TRIGGER', 'TRUE', 'TRUNCATE', 'TSEQUAL', 'TYPE', 'UNBOUNDED', 'UNCOMMITTED',
            'UNDEFINED', 'UNDER', 'UNION', 'UNIQUE', 'UNKNOWN', 'UNNEST', 'UPDATE', 'UPDATETEXT', 'USAGE', 'USE', 'USER', 'USING', 'VALUE', 'VALUES',
            'VARIABLE', 'VARYING', 'VIEW', 'WAITFOR', 'WHEN', 'WHENEVER', 'WHERE', 'WHILE', 'WITH', 'WITHIN', 'WITHOUT', 'WORK', 'WRITE', 'WRITETEXT', 'ZONE',

            // cursor keywords
            'FORWARD_ONLY', 'READ_ONLY', 'FAST_FORWARD',

            // resource governor
            'RESOURCE', 'GOVERNOR', 'POOL', 'WORKLOAD',

            // other keywords that were missing or are new in SQL Server 2012.
            'ANSI_NULL_DFLT_ON',
            'ACCENT_SENSITIVITY', 'ACTIVATION', 'ACTIVE', 'ADDRESS', 'AES', 'AFFINITY', 'ALGORITHM', 'ALL_SPARSE_COLUMNS', 'ALLOW_MULTIPLE_EVENT_LOSS',
            'ALLOW_PAGE_LOCKS', 'ALLOW_ROW_LOCKS', 'ALLOW_SINGLE_EVENT_LOSS', 'ALLOW_SNAPSHOT_ISOLATION', 'ANONYMOUS', 'ANSI_DEFAULTS', 'ANSI_NULL_DEFAULT',
            'ANSI_NULL_DFLT_OFF', 'ANSI_NULLS', 'ANSI_PADDING', 'ANSI_WARNINGS', 'ARITHABORT', 'ARITHIGNORE', 'ASSEMBLY', 'ASYMMETRIC', 'ATTACH_REBUILD_LOG', 'AUTO', 'AUTO_CLEANUP',
            'AUTO_CLOSE', 'AUTO_CREATE_STATISTICS', 'AUTO_SHRINK', 'AUTO_UPDATE_STATISTICS', 'AUTO_UPDATE_STATISTICS_ASYNC', 'BATCHSIZE', 'BEGIN_DIALOG',
            'BINDING', 'BROKER', 'BROKER_INSTANCE', 'BULK_LOGGED', 'CAP_CPU_PERCENT', 'CATALOG_DEFAULT', 'CATCH', 'CERTIFICATE', 'CHANGE_RETENTION',
            'CHANGE_TRACKING', 'CHECK_EXPIRATION',  'CHECK_POLICY', 'CLASSIFIER_FUNCTION', 'CLUSTER', 'CODEPAGE', 'COLLECTION',
            'COLUMN_SET', 'COMPATIBILITY_LEVEL', 'COMPRESSION', 'CONCAT_NULL_YIELDS_NULL', 'CONTENT', 'CONTRACT', 'CONVERSATION', 'CPU', 'CPU_ID', 'CREDENTIAL',
            'CRYPTOGRAPHIC', 'CURSOR_CLOSE_ON_COMMIT', 'CURSOR_DEFAULT', 'DATA_COMPRESSION', 'DATABASE_MIRRORING', 'DATAFILETYPE', 'DATE_CORRELATION_OPTIMIZATION',
            'DATEFIRST', 'DATEFORMAT', 'DAYS', 'DB_CHAINING', 'DEADLOCK_PRIORITY', 'DECRYPTION', 'DEFAULT_DATABASE', 'DEFAULT_FULLTEXT_LANGUAGE', 'DEFAULT_LANGUAGE',
            'DEFAULT_SCHEMA', 'DIRECTORY_NAME', 'DISABLE_BROKER', 'DOCUMENT', 'EMERGENCY', 'ENABLE_BROKER', 'ENCRYPTION', 'ENDPOINT', 'ERROR_BROKER_CONVERSATIONS',
            'ERRORFILE', 'EVENT', 'EVENT_RETENTION_MODE', 'EXPAND', 'EXTERNAL_ACCESS', 'FAILOVER', 'FAST', 'FIELDTERMINATOR', 'FILEGROUP', 'FILEGROWTH', 'FILENAME',
            'FILEPATH', 'FILESTREAM', 'FILESTREAM_ON', 'FILETABLE_DIRECTORY', 'FILETABLE_NAMESPACE', 'FIPS_FLAGGER', 'FIRE_TRIGGERS', 'FIRSTROW', 'FMTONLY', 'FORCE',
            'FORCE_SERVICE_ALLOW_DATA_LOSS', 'FORCED', 'FORCEPLAN', 'FORMATFILE', 'FULLTEXT', 'GROUP_MAX_REQUESTS', 'HASH', 'HIGH', 'HONOR_BROKER_PRIORITY', 'HOURS',
            'HTTP', 'IGNORE_CONSTRAINTS', 'IGNORE_DUP_KEY', 'IGNORE_NONCLUSTERED_COLUMNSTORE_INDEX', 'IGNORE_TRIGGERS', 'IMPLICIT_TRANSACTIONS', 'IMPORTANCE',
            'INCLUDE', 'INCREMENT', 'INCREMENTAL', 'INFINITE', 'INIT', 'INITIATOR', 'INSTEAD', 'IO', 'KB', 'KEEP', 'KEEPDEFAULTS', 'KEEPFIXED', 'KEEPIDENTITY',
            'KEEPNULLS', 'KERBEROS', 'KILOBYTES_PER_BATCH', 'LASTROW', 'LIFETIME', 'LIST', 'LISTENER_IP', 'LISTENER_PORT', 'LOCAL_SERVICE_NAME', 'LOCK_ESCALATION',
            'LOCK_TIMEOUT', 'LOOP', 'LOW', 'MAX_CPU_PERCENT', 'MAX_DISPATCH_LATENCY', 'MAX_DOP', 'MAX_EVENT_SIZE', 'MAX_FILES', 'MAX_MEMORY', 'MAX_MEMORY_PERCENT',
            'MAX_QUEUE_READERS', 'MAX_ROLLOVER_FILES', 'MAXDOP', 'MAXERRORS', 'MAXRECURSION', 'MAXSIZE', 'MAXVALUE', 'MB', 'MEDIUM', 'MEMORY_PARTITION_MODE',
            'MERGE', 'MESSAGE_FORWARD_SIZE', 'MESSAGE_FORWARDING', 'MIN_CPU_PERCENT', 'MIN_MEMORY_PERCENT', 'MINUTES', 'MINVALUE', 'MIRROR_ADDRESS', 'MOVE',
            'MULTI_USER', 'MUST_CHANGE', 'NEGOTIATE', 'NESTED_TRIGGERS', 'NEW_ACCOUNT', 'NEW_BROKER', 'NEWNAME', 'NO_COMPRESSION', 'NO_EVENT_LOSS', 'NO_WAIT', 'NOEXEC', 'NOEXPAND',
            'NOLOCK', 'NON_TRANSACTED_ACESS', 'NOWAIT', 'NTLM', 'NUMA_NODE_ID', 'NUMANODE', 'NUMERIC_ROUNDABORT', 'OFFLINE', 'OLD_ACCOUNT', 'ON_FAILURE', 'ONLINE',
            'OPTIMIZE', 'OVERRIDE', 'PAD_INDEX', 'PAGE', 'PAGE_VERIFY', 'PAGLOCK', 'PARAMETERIZATION', 'PARSEONLY', 'PARTITIONS', 'PARTNER', 'PER_CPU', 'PER_NODE',
            'PERMISSION_SET', 'PERSISTED', 'POISON_MESSAGE_HANDLING', 'POPULATION', 'PRIORITY', 'PRIORITY_LEVEL', 'PRIVATE', 'PROCEDURE_NAME', 'PROCESS', 'PROFILE',
            'PROPERTY', 'PROPERTY_DESCRIPTION', 'PROPERTY_INT_ID', 'PROPERTY_SET_GUID', 'PROVIDER', 'QUERY_GOVERNOR_COST_LIMIT', 'QUEUE', 'QUEUE_DELAY',
            'QUOTED_IDENTIFIER', 'RC4', 'READ_COMMITTED_SNAPSHOT', 'READ_WRITE', 'READCOMMITTED', 'READCOMMITTEDLOCK', 'READONLY', 'READPAST', 'READUNCOMMITTED',
            'READWRITE', 'RECOMPILE', 'RECOVERY', 'RECOVERY FULL', 'RECOVERY SIMPLE', 'RECURSIVE_TRIGGERS', 'REGENERATE', 'REMOTE', 'REMOTE_PROC_TRANSACTIONS', 'REMOTE_SERVICE_NAME', 'REMOVE',
            'REPEATABLEREAD', 'REQUEST_MAX_CPU_TIME_SEC', 'REQUEST_MAX_MEMORY_GRANT_PERCENT', 'REQUEST_MEMORY_GRANT_TIMEOUT_SEC', 'REQUIRED', 'RESERVE_DISK_SPACE',
            'RESET', 'RESTART', 'RESTRICTED_USER', 'RESUME', 'RETENTION', 'ROBUST', 'ROUTE', 'ROWLOCK', 'ROWS_PER_BATCH', 'ROWTERMINATOR', 'SAFE', 'SAFETY',
            'SCHEDULER', 'SCHEMABINDING', 'SCHEME', 'SECRET', 'SERVICE', 'SERVICE_BROKER', 'SERVICE_NAME', 'SETERROR', 'SHOWPLAN', 'SHOWPLAN_ALL', 'SHOWPLAN_TEXT',
            'SHOWPLAN_XML', 'SIMPLE', 'SINGLE_USER', 'SORT_IN_TEMPDB', 'SOURCE', 'SPARSE', 'SPATIAL_WINDOW_MAX_CELLS', 'SPLIT', 'STARTED', 'STARTUP_STATE',
            'STATISTICAL_SEMANTICS', 'STATISTICS_NORECOMPUTE', 'STATUS', 'STOP', 'STOPLIST', 'STOPPED', 'SUPPORTED', 'SUSPEND', 'SWITCH', 'SYMMETRIC', 'SYNONYM',
            'SYSTEM', 'TABLOCK', 'TABLOCKX', 'TARGET', 'TARGET_RECOVERY_TIME', 'TCP', 'TIMEOUT', 'TORN_PAGE_DETECTION', 'TRACK_CAUSALITY', 'TRACK_COLUMNS_UPDATED',
            'TRANSFER', 'TRANSFORM_NOISE_WORDS', 'TRUSTWORTHY', 'TRY', 'TSQL', 'TWO_DIGIT_YEAR_CUTOFF', 'UNCHECKED', 'UNLIMITED', 'UNLOCK', 'UNSAFE', 'UPDLOCK',
            'USED', 'VALID_XML', 'VALIDATION', 'VIEW_METADATA', 'VISIBILITY', 'WEEKDAY', 'WELL_FORMED_XML', 'WINDOWS', 'WITH SUBJECT', 'WITNESS', 'XACT_ABORT', 'XLOCK',

            /* AlwaysOn stuff */
            'AUTHENTICATION', 'ENDPOINT_URL', 'LISTENER', 'COPY_ONLY', 'NORECOVERY', 'NOUNLOAD', 'HADR', 'PORT',
            'FAILOVER_MODE', 'MANUAL', 'AVAILABILITY_MODE', 'ASYNCHRONOUS_COMMIT', 'SYNCHRONOUS_COMMIT', 'AUTOMATIC', 'REPLICA', 'READ_ONLY_ROUTING_URL', 'BACKUP_PRIORITY', 'SECONDARY_ROLE', 'ALLOW_CONNECTIONS', 'AUTOMATED_BACKUP_PREFERENCE', 'SECONDARY', 'SECONDARY_ONLY', 'PRIMARY_ROLE', 'READ_ONLY_ROUTING_LIST',
        ),
        3 => array(
            /*
                System stored procedures
                Higlighted dark brown
            */

            // CLR stored procedures
            'sp_FuzzyLookupTableMaintenanceInstall', 'sp_FuzzyLookupTableMaintenanceInvoke', 'sp_FuzzyLookupTableMaintenanceUninstall',

            // system procedures named with xp_
            'xp_grantlogin', 'xp_logininfo', 'xp_repl_convert_encrypt_sysadmin_wrapper', 'xp_revokelogin',

            // extended procedures
            'xp_availablemedia',  'xp_cmdshell', 'xp_create_subdir', 'xp_delete_file', 'xp_dirtree', 'xp_enum_oledb_providers',
            'xp_enumerrorlogs', 'xp_enumgroups',  'xp_fileexist', 'xp_fixeddrives', 'xp_get_script', 'xp_get_tape_devices',
            'xp_getnetname', 'xp_instance_regaddmultistring',  'xp_instance_regdeletekey', 'xp_instance_regdeletevalue',
            'xp_instance_regenumkeys', 'xp_instance_regenumvalues',  'xp_instance_regread', 'xp_instance_regremovemultistring',
            'xp_instance_regwrite', 'xp_logevent', 'xp_loginconfig', 'xp_msver',  'xp_msx_enlist', 'xp_passAgentInfo',
            'xp_prop_oledb_provider', 'xp_qv', 'xp_readerrorlog', 'xp_regaddmultistring',  'xp_regdeletekey', 'xp_regdeletevalue',
            'xp_regenumkeys', 'xp_regenumvalues', 'xp_regread', 'xp_regremovemultistring',  'xp_regwrite', 'xp_replposteor',
            'xp_servicecontrol', 'xp_sprintf', 'xp_sqlagent_enum_jobs', 'xp_sqlagent_is_starting',  'xp_sqlagent_monitor',
            'xp_sqlagent_notify', 'xp_sqlagent_param', 'xp_sqlmaint', 'xp_sscanf', 'xp_subdirs',  'xp_sysmail_activate',
            'xp_sysmail_attachment_load',  'xp_sysmail_format_query',

            // extended procedures named with sp_
            'sp_AddFunctionalUnitToComponent', 'sp_audit_write', 'sp_availability_group_command_internal', 'sp_begin_parallel_nested_tran',
            'sp_bindsession', 'sp_change_tracking_waitforchanges', 'sp_commit_parallel_nested_tran', 'sp_control_dbmasterkey_password',
            'sp_createorphan', 'sp_cursor', 'sp_cursorclose', 'sp_cursorexecute', 'sp_cursorfetch', 'sp_cursoropen', 'sp_cursoroption',
            'sp_cursorprepare', 'sp_cursorprepexec', 'sp_cursorunprepare', 'sp_delete_http_namespace_reservation',
            'sp_describe_first_result_set', 'sp_describe_undeclared_parameters', 'sp_droporphans', 'sp_enable_sql_debug', 'sp_execute',
            'sp_executesql', 'sp_fulltext_getdata', 'sp_fulltext_keymappings', 'sp_fulltext_pendingchanges', 'sp_get_query_template',
            'sp_getbindtoken', 'sp_getschemalock', 'sp_http_generate_wsdl_complex', 'sp_http_generate_wsdl_simple',
            'sp_migrate_user_to_contained', 'sp_new_parallel_nested_tran_id', 'sp_OACreate', 'sp_OADestroy', 'sp_OAGetErrorInfo',
            'sp_OAGetProperty', 'sp_OAMethod', 'sp_OASetProperty', 'sp_OAStop', 'sp_PostAgentInfo', 'sp_prepare', 'sp_prepexec',
            'sp_prepexecrpc', 'sp_releaseschemalock', 'sp_repl_generateevent', 'sp_replcmds', 'sp_replcounters', 'sp_replddlparser',
            'sp_repldone', 'sp_replflush', 'sp_replhelp', 'sp_replsendtoqueue', 'sp_replsetsyncstatus', 'sp_repltrans',
            'sp_replwritetovarbin', 'sp_reserve_http_namespace', 'sp_reset_connection', 'sp_resyncexecute', 'sp_resyncexecutesql',
            'sp_resyncprepare', 'sp_resyncuniquetable', 'sp_rollback_parallel_nested_tran', 'sp_server_diagnostics',
            'sp_SetOBDCertificate', 'sp_setuserbylogin', 'sp_showmemo_xml', 'sp_start_user_instance', 'sp_testlinkedserver',
            'sp_trace_create', 'sp_trace_generateevent', 'sp_trace_setevent', 'sp_trace_setfilter', 'sp_trace_setstatus', 'sp_unprepare',
            'sp_update_user_instance', 'sp_xml_preparedocument', 'sp_xml_removedocument', 'sp_xp_cmdshell_proxy_account'

        ),
        4 => array(
            // DMVs/DMFs/catalog views etc. highlighted green.

            //schemas
            'sys', 'INFORMATION_SCHEMA',

            // inline TVFs
            'dm_cryptographic_provider_algorithms','dm_cryptographic_provider_keys','dm_cryptographic_provider_sessions',
            'dm_db_database_page_allocations','dm_db_index_operational_stats','dm_db_index_physical_stats',
            'dm_db_missing_index_columns','dm_db_objects_disabled_on_compatibility_level_change',
            'dm_exec_cached_plan_dependent_objects','dm_exec_cursors','dm_exec_describe_first_result_set',
            'dm_exec_describe_first_result_set_for_object','dm_exec_plan_attributes','dm_exec_query_plan','dm_exec_sql_text',
            'dm_exec_text_query_plan','dm_exec_xml_handles','dm_fts_index_keywords','dm_fts_index_keywords_by_document',
            'dm_fts_index_keywords_by_property','dm_fts_parser','dm_io_virtual_file_stats','dm_logconsumer_cachebufferrefs',
            'dm_logconsumer_privatecachebuffers','dm_logpool_consumers','dm_logpool_sharedcachebuffers','dm_logpoolmgr_freepools',
            'dm_logpoolmgr_respoolsize','dm_logpoolmgr_stats','dm_os_volume_stats','dm_sql_referenced_entities',
            'dm_sql_referencing_entities','fn_builtin_permissions','fn_check_object_signatures','fn_dblog','fn_dump_dblog',
            'fn_get_audit_file','fn_get_sql','fn_helpcollations','fn_MSxe_read_event_stream','fn_trace_gettable',
            'fn_translate_permissions', 'fn_validate_plan_guide',  'fn_xe_file_target_read_file',

            // multi-statement TVFs
            'fn_EnumCurrentPrincipals', 'fn_helpdatatypemap', 'fn_listextendedproperty', 'fn_my_permissions',
            'fn_PhysLocCracker', 'fn_replgetcolidfrombitmap', 'fn_RowDumpCracker', 'fn_servershareddrives',
            'fn_trace_geteventinfo', 'fn_trace_getfilterinfo', 'fn_trace_getinfo',  'fn_virtualfilestats',
            'fn_virtualservernodes',

            // aggregate functions
            'GeographyCollectionAggregate', 'GeographyConvexHullAggregate', 'GeographyEnvelopeAggregate', 'GeographyUnionAggregate',
            'GeometryCollectionAggregate', 'GeometryConvexHullAggregate', 'GeometryEnvelopeAggregate', 'GeometryUnionAggregate',
            'ORMask',

            // scalar functions
            'fn_cColvEntries_80','fn_cdc_check_parameters','fn_cdc_get_column_ordinal','fn_cdc_get_max_lsn','fn_cdc_get_min_lsn',
            'fn_cdc_has_column_changed','fn_cdc_hexstrtobin','fn_cdc_map_lsn_to_time','fn_cdc_map_time_to_lsn','fn_fIsColTracked',
            'fn_GetCurrentPrincipal','fn_GetRowsetIdFromRowDump','fn_hadr_backup_is_preferred_replica','fn_IsBitSetInBitmask',
            'fn_isrolemember','fn_MapSchemaType','fn_MSdayasnumber','fn_MSgeneration_downloadonly','fn_MSget_dynamic_filter_login',
            'fn_MSorbitmaps','fn_MSrepl_map_resolver_clsid','fn_MStestbit','fn_MSvector_downloadonly','fn_numberOf1InBinaryAfterLoc',
            'fn_numberOf1InVarBinary','fn_PhysLocFormatter','fn_repladjustcolumnmap','fn_repldecryptver4','fn_replformatdatetime',
            'fn_replgetparsedddlcmd','fn_replp2pversiontotranid', 'fn_replreplacesinglequote',
            'fn_replreplacesinglequoteplusprotectstring', 'fn_repluniquename', 'fn_replvarbintoint', 'fn_sqlvarbasetostr',
            'fn_varbintohexstr', 'fn_varbintohexsubstring', 'fn_yukonsecuritymodelrequired',

            // service queues
            'EventNotificationErrorsQueue', 'QueryNotificationErrorsQueue', 'ServiceBrokerQueue',

            // system tables
            'sysallocunits','sysasymkeys','sysaudacts','sysbinobjs','sysbinsubobjs','sysbrickfiles','syscerts','syschildinsts','sysclones','sysclsobjs',
            'syscolpars','syscompfragments','sysconvgroup','syscscolsegments','syscsdictionaries','sysdbfiles','sysdbfrag','sysdbreg','sysdercv','sysdesend',
            'sysendpts','sysfgfrag','sysfiles1','sysfoqueues','sysfos','sysftinds','sysftproperties','sysftsemanticsdb','sysftstops','sysguidrefs',
            'sysidxstats','sysiscols','syslnklgns','sysmultiobjrefs','sysnsobjs','sysobjkeycrypts','sysobjvalues','sysowners','sysphfg','syspriorities',
            'sysprivs','syspru','sysprufiles','sysqnames','sysremsvcbinds','sysrmtlgns','sysrowsetrefs','sysrowsets','sysrscols','sysrts',
            'sysscalartypes','sysschobjs','sysseobjvalues','syssingleobjrefs','syssoftobjrefs','syssqlguides','systypedsubobjs','sysusermsgs','syswebmethods',
            'sysxlgns','sysxmitbody', 'sysxmitqueue', 'sysxmlcomponent', 'sysxmlfacet', 'sysxmlplacement', 'sysxprops', 'sysxsrvs',

            // user tables (these are currently in dbo schema but will be fixed)
            'trace_xe_action_map', 'trace_xe_event_map',

            // backward compatibility views
            'sysaltfiles', 'syscacheobjects', 'syscharsets', 'syscolumns', 'syscomments', 'sysconfigures', 'sysconstraints', 'syscurconfigs', 'syscursorcolumns',
            'syscursorrefs', 'syscursors', 'syscursortables', 'sysdatabases', 'sysdepends', 'sysdevices', 'sysfilegroups', 'sysfiles', 'sysforeignkeys',
            'sysfulltextcatalogs', 'sysindexes', 'sysindexkeys', 'syslanguages', 'syslockinfo', 'syslogins', 'sysmembers', 'sysmessages', 'sysobjects',
            'sysoledbusers', 'sysopentapes', 'sysperfinfo', 'syspermissions', 'sysprocesses', 'sysprotects', 'sysreferences', 'sysremotelogins', 'sysservers',
            'systypes', 'sysusers',

            // INFORMATION_SCHEMA views
            'COLUMN_DOMAIN_USAGE', 'COLUMN_PRIVILEGES', 'COLUMNS', 'CONSTRAINT_COLUMN_USAGE',
            'CONSTRAINT_TABLE_USAGE', 'KEY_COLUMN_USAGE', 'PARAMETERS', 'REFERENTIAL_CONSTRAINTS', 'ROUTINE_COLUMNS',
            'ROUTINES', 'SCHEMATA', 'SEQUENCES', 'TABLE_CONSTRAINTS', 'TABLE_PRIVILEGES', 'TABLES', 'VIEW_COLUMN_USAGE',
            'VIEW_TABLE_USAGE', 'VIEWS',

            // catalog views
            'default_constraints',
            'all_columns', 'all_objects', 'all_parameters', 'all_sql_modules', 'all_views', 'allocation_units',
            'assemblies', 'assembly_files', 'assembly_modules', 'assembly_references', 'assembly_types', 'asymmetric_keys',
            'availability_databases_cluster', 'availability_group_listener_ip_addresses', 'availability_group_listeners',
            'availability_groups', 'availability_groups_cluster', 'availability_read_only_routing_lists',
            'availability_replicas', 'backup_devices', 'certificates', 'change_tracking_databases', 'change_tracking_tables',
            'check_constraints', 'column_store_dictionaries', 'column_store_index_stats', 'column_store_segments',
            'column_type_usages', 'column_xml_schema_collection_usages', 'computed_columns', 'configurations',
            'conversation_endpoints', 'conversation_groups', 'conversation_priorities', 'credentials', 'crypt_properties',
            'cryptographic_providers', 'databases', 'endpoint_webmethods', 'endpoints', 'event_notification_event_types',
            'event_notifications', 'events', 'extended_procedures', 'extended_properties', 'filegroups',
            'filetable_system_defined_objects', 'filetables', 'foreign_key_columns', 'foreign_keys', 'fulltext_catalogs',
            'fulltext_document_types', 'fulltext_index_catalog_usages', 'fulltext_index_columns', 'fulltext_index_fragments',
            'fulltext_indexes', 'fulltext_languages', 'fulltext_semantic_language_statistics_database',
            'fulltext_semantic_languages', 'fulltext_stoplists', 'fulltext_stopwords', 'fulltext_system_stopwords',
            'function_order_columns', 'http_endpoints', 'identity_columns', 'index_columns', 'indexes', 'internal_tables',
            'key_constraints', 'key_encryptions', 'linked_logins', 'login_token', 'master_files', 'numbered_procedure_parameters',
            'numbered_procedures', 'objects', 'openkeys', 'parameter_type_usages', 'parameter_xml_schema_collection_usages',
            'partition_functions', 'partition_parameters', 'partition_range_values', 'partition_schemes',
            'partitions', 'plan_guides', 'procedures', 'registered_search_properties', 'registered_search_property_lists',
            'remote_logins', 'remote_service_bindings', 'resource_governor_configuration',  'resource_governor_resource_pool_affinity',
            'resource_governor_resource_pools', 'resource_governor_workload_groups', 'routes', 'schemas', 'securable_classes',
            'server_assembly_modules', 'server_audit_specification_details', 'server_audit_specifications',
            'server_audits', 'server_event_notifications', 'server_event_session_actions', 'server_event_session_events',
            'server_event_session_fields', 'server_event_session_targets', 'server_event_sessions', 'server_events',
            'server_file_audits', 'server_permissions', 'server_principal_credentials', 'server_principals', 'server_role_members',
            'server_sql_modules', 'server_trigger_events', 'server_triggers', 'servers', 'service_broker_endpoints',
            'service_contract_message_usages', 'service_contract_usages', 'service_contracts', 'service_message_types',
            'service_queue_usages', 'service_queues', 'services', 'soap_endpoints', 'spatial_index_tessellations', 'spatial_indexes',
            'spatial_reference_systems', 'sql_dependencies', 'sql_expression_dependencies', 'sql_logins', 'sql_modules', 'stats',
            'stats_columns', 'symmetric_keys', 'synonyms', 'system_columns', 'system_components_surface_area_configuration',
            'system_internals_allocation_units', 'system_internals_partition_columns', 'system_internals_partitions', 'system_objects',
            'system_parameters', 'system_sql_modules', 'system_views', 'table_types', 'tcp_endpoints', 'trace_categories',
            'trace_columns', 'trace_event_bindings', 'trace_events', 'trace_subclass_values', 'traces', 'transmission_queue',
            'trigger_event_types', 'trigger_events', 'triggers', 'type_assembly_usages', 'types', 'user_token', 'via_endpoints',
            'xml_indexes', 'xml_schema_attributes', 'xml_schema_collections', 'xml_schema_component_placements',
            'xml_schema_components',  'xml_schema_elements', 'xml_schema_facets', 'xml_schema_model_groups', 'xml_schema_namespaces',
            'xml_schema_types', 'xml_schema_wildcard_namespaces', 'xml_schema_wildcards',

            // DMVs / DMFs
            'dm_audit_actions', 'dm_audit_class_type_map', 'dm_broker_activated_tasks', 'dm_broker_connections', 'dm_broker_forwarded_messages',
            'dm_broker_queue_monitors', 'dm_cdc_errors', 'dm_cdc_log_scan_sessions', 'dm_clr_appdomains', 'dm_clr_loaded_assemblies',
            'dm_clr_properties', 'dm_clr_tasks', 'dm_cryptographic_provider_properties', 'dm_database_encryption_keys', 'dm_db_file_space_usage',
            'dm_db_fts_index_physical_stats', 'dm_db_index_usage_stats', 'dm_db_log_space_usage', 'dm_db_mirroring_auto_page_repair',
            'dm_db_mirroring_connections', 'dm_db_mirroring_past_actions', 'dm_db_missing_index_details', 'dm_db_missing_index_group_stats',
            'dm_db_missing_index_groups', 'dm_db_partition_stats', 'dm_db_persisted_sku_features', 'dm_db_script_level', 'dm_db_session_space_usage',
            'dm_db_task_space_usage', 'dm_db_uncontained_entities', 'dm_exec_background_job_queue', 'dm_exec_background_job_queue_stats',
            'dm_exec_cached_plans', 'dm_exec_connections', 'dm_exec_procedure_stats', 'dm_exec_query_memory_grants', 'dm_exec_query_optimizer_info', 'dm_exec_query_profiles',
            'dm_exec_query_resource_semaphores', 'dm_exec_query_stats', 'dm_exec_query_transformation_stats', 'dm_exec_requests', 'dm_exec_sessions',
            'dm_exec_trigger_stats', 'dm_filestream_file_io_handles', 'dm_filestream_file_io_requests', 'dm_filestream_non_transacted_handles',
            'dm_fts_active_catalogs', 'dm_fts_fdhosts', 'dm_fts_index_population', 'dm_fts_memory_buffers', 'dm_fts_memory_pools',
            'dm_fts_outstanding_batches', 'dm_fts_population_ranges', 'dm_fts_semantic_similarity_population', 'dm_hadr_auto_page_repair',
            'dm_hadr_availability_group_states', 'dm_hadr_availability_replica_cluster_nodes', 'dm_hadr_availability_replica_cluster_states',
            'dm_hadr_availability_replica_states', 'dm_hadr_cluster', 'dm_hadr_cluster_members', 'dm_hadr_cluster_networks',
            'dm_hadr_database_replica_cluster_states', 'dm_hadr_database_replica_states', 'dm_hadr_instance_node_map', 'dm_hadr_name_id_map',
            'dm_io_backup_tapes', 'dm_io_cluster_shared_drives', 'dm_io_pending_io_requests', 'dm_logpool_hashentries',
            'dm_logpool_stats', 'dm_os_buffer_descriptors', 'dm_os_child_instances', 'dm_os_cluster_nodes', 'dm_os_cluster_properties',
            'dm_os_dispatcher_pools', 'dm_os_dispatchers', 'dm_os_hosts', 'dm_os_latch_stats', 'dm_os_loaded_modules',
            'dm_os_memory_allocations', 'dm_os_memory_broker_clerks', 'dm_os_memory_brokers', 'dm_os_memory_cache_clock_hands',
            'dm_os_memory_cache_counters', 'dm_os_memory_cache_entries', 'dm_os_memory_cache_hash_tables', 'dm_os_memory_clerks',
            'dm_os_memory_node_access_stats', 'dm_os_memory_nodes', 'dm_os_memory_objects', 'dm_os_memory_pools', 'dm_os_nodes',
            'dm_os_performance_counters', 'dm_os_process_memory', 'dm_os_ring_buffers', 'dm_os_schedulers',
            'dm_os_server_diagnostics_log_configurations', 'dm_os_spinlock_stats', 'dm_os_stacks', 'dm_os_sublatches', 'dm_os_sys_info',
            'dm_os_sys_memory', 'dm_os_tasks', 'dm_os_threads', 'dm_os_virtual_address_dump', 'dm_os_wait_stats', 'dm_os_waiting_tasks',
            'dm_os_windows_info', 'dm_os_worker_local_storage', 'dm_os_workers', 'dm_qn_subscriptions', 'dm_repl_articles', 'dm_repl_schemas',
            'dm_repl_tranhash', 'dm_repl_traninfo', 'dm_resource_governor_configuration', 'dm_resource_governor_resource_pool_affinity',
            'dm_resource_governor_resource_pools', 'dm_resource_governor_workload_groups', 'dm_server_audit_status', 'dm_server_memory_dumps',
            'dm_server_registry', 'dm_server_services', 'dm_tcp_listener_states', 'dm_tran_active_snapshot_database_transactions',
            'dm_tran_active_transactions', 'dm_tran_commit_table', 'dm_tran_current_snapshot', 'dm_tran_current_transaction',
            'dm_tran_database_transactions', 'dm_tran_locks', 'dm_tran_session_transactions', 'dm_tran_top_version_generators',
            'dm_tran_transactions_snapshot', 'dm_tran_version_store', 'dm_xe_map_values', 'dm_xe_object_columns', 'dm_xe_objects', 'dm_xe_packages', 'dm_xe_sessions', 'dm_xe_session_targets',
        ),
        5 => array(

            // system procedures (sorry, not categorized, but definitely more complete and updated for Denali)
            // moved these to their own array because they overloaded array 3

            'sp_add_agent_parameter', 'sp_add_agent_profile', 'sp_add_data_file_recover_suspect_db', 'sp_add_log_file_recover_suspect_db',
            'sp_add_log_shipping_alert_job', 'sp_add_log_shipping_primary_database', 'sp_add_log_shipping_primary_secondary',
            'sp_add_log_shipping_secondary_database', 'sp_add_log_shipping_secondary_primary', 'sp_addapprole', 'sp_addarticle',
            'sp_adddatatype', 'sp_adddatatypemapping', 'sp_adddistpublisher', 'sp_adddistributiondb', 'sp_adddistributor',
            'sp_adddynamicsnapshot_job', 'sp_addextendedproc', 'sp_addextendedproperty', 'sp_addlinkedserver', 'sp_addlinkedsrvlogin',
            'sp_addlogin', 'sp_addlogreader_agent', 'sp_addmergealternatepublisher', 'sp_addmergearticle', 'sp_addmergefilter',
            'sp_addmergelogsettings', 'sp_addmergepartition', 'sp_addmergepublication', 'sp_addmergepullsubscription',
            'sp_addmergepullsubscription_agent', 'sp_addmergepushsubscription_agent', 'sp_addmergesubscription', 'sp_addmessage',
            'sp_addpublication', 'sp_addpublication_snapshot', 'sp_addpullsubscription', 'sp_addpullsubscription_agent',
            'sp_addpushsubscription_agent', 'sp_addqreader_agent', 'sp_addqueued_artinfo', 'sp_addremotelogin', 'sp_addrole',
            'sp_addrolemember', 'sp_addscriptexec', 'sp_addserver', 'sp_addsrvrolemember', 'sp_addsubscriber', 'sp_addsubscriber_schedule',
            'sp_addsubscription', 'sp_addsynctriggers', 'sp_addsynctriggerscore', 'sp_addtabletocontents', 'sp_addtype', 'sp_addumpdevice',
            'sp_adduser', 'sp_adjustpublisheridentityrange', 'sp_altermessage', 'sp_approlepassword', 'sp_article_validation',
            'sp_articlecolumn', 'sp_articlefilter', 'sp_articleview', 'sp_assemblies_rowset', 'sp_assemblies_rowset_rmt',
            'sp_assemblies_rowset2', 'sp_assembly_dependencies_rowset', 'sp_assembly_dependencies_rowset_rmt',
            'sp_assembly_dependencies_rowset2', 'sp_attach_db', 'sp_attach_single_file_db', 'sp_attachsubscription', 'sp_autostats',
            'sp_bcp_dbcmptlevel', 'sp_bindefault', 'sp_bindrule', 'sp_browsemergesnapshotfolder', 'sp_browsereplcmds',
            'sp_browsesnapshotfolder', 'sp_can_tlog_be_applied', 'sp_catalogs', 'sp_catalogs_rowset', 'sp_catalogs_rowset_rmt',
            'sp_catalogs_rowset2', 'sp_cdc_add_job', 'sp_cdc_change_job', 'sp_cdc_cleanup_change_table', 'sp_cdc_dbsnapshotLSN',
            'sp_cdc_disable_db', 'sp_cdc_disable_table', 'sp_cdc_drop_job', 'sp_cdc_enable_db', 'sp_cdc_enable_table',
            'sp_cdc_generate_wrapper_function', 'sp_cdc_get_captured_columns', 'sp_cdc_get_ddl_history', 'sp_cdc_help_change_data_capture',
            'sp_cdc_help_jobs', 'sp_cdc_restoredb', 'sp_cdc_scan', 'sp_cdc_start_job', 'sp_cdc_stop_job', 'sp_cdc_vupgrade',
            'sp_cdc_vupgrade_databases', 'sp_certify_removable', 'sp_change_agent_parameter', 'sp_change_agent_profile',
            'sp_change_log_shipping_primary_database', 'sp_change_log_shipping_secondary_database', 'sp_change_log_shipping_secondary_primary',
            'sp_change_subscription_properties', 'sp_change_users_login', 'sp_changearticle', 'sp_changearticlecolumndatatype',
            'sp_changedbowner', 'sp_changedistpublisher', 'sp_changedistributiondb', 'sp_changedistributor_password',
            'sp_changedistributor_property', 'sp_changedynamicsnapshot_job', 'sp_changelogreader_agent', 'sp_changemergearticle',
            'sp_changemergefilter', 'sp_changemergelogsettings', 'sp_changemergepublication', 'sp_changemergepullsubscription',
            'sp_changemergesubscription', 'sp_changeobjectowner', 'sp_changepublication', 'sp_changepublication_snapshot',
            'sp_changeqreader_agent', 'sp_changereplicationserverpasswords', 'sp_changesubscriber', 'sp_changesubscriber_schedule',
            'sp_changesubscription', 'sp_changesubscriptiondtsinfo', 'sp_changesubstatus', 'sp_check_constbytable_rowset',
            'sp_check_constbytable_rowset2', 'sp_check_constraints_rowset', 'sp_check_constraints_rowset2', 'sp_check_dynamic_filters',
            'sp_check_for_sync_trigger', 'sp_check_join_filter', 'sp_check_log_shipping_monitor_alert', 'sp_check_publication_access',
            'sp_check_removable', 'sp_check_subset_filter', 'sp_check_sync_trigger', 'sp_checkinvalidivarticle',
            'sp_checkOraclepackageversion', 'sp_clean_db_file_free_space', 'sp_clean_db_free_space', 'sp_cleanmergelogfiles',
            'sp_cleanup_log_shipping_history', 'sp_cleanupdbreplication', 'sp_column_privileges', 'sp_column_privileges_ex',
            'sp_column_privileges_rowset', 'sp_column_privileges_rowset_rmt', 'sp_column_privileges_rowset2', 'sp_columns', 'sp_columns_100',
            'sp_columns_100_rowset', 'sp_columns_100_rowset2', 'sp_columns_90', 'sp_columns_90_rowset', 'sp_columns_90_rowset_rmt',
            'sp_columns_90_rowset2', 'sp_columns_ex', 'sp_columns_ex_100', 'sp_columns_ex_90', 'sp_columns_managed', 'sp_columns_rowset',
            'sp_columns_rowset_rmt', 'sp_columns_rowset2', 'sp_configure', 'sp_configure_peerconflictdetection', 'sp_constr_col_usage_rowset',
            'sp_constr_col_usage_rowset2', 'sp_control_plan_guide', 'sp_copymergesnapshot', 'sp_copysnapshot', 'sp_copysubscription',
            'sp_create_plan_guide', 'sp_create_plan_guide_from_handle', 'sp_create_removable', 'sp_createmergepalrole', 'sp_createstats',
            'sp_createtranpalrole', 'sp_cursor_list', 'sp_cycle_errorlog', 'sp_databases', 'sp_datatype_info', 'sp_datatype_info_100',
            'sp_datatype_info_90', 'sp_db_increased_partitions', 'sp_db_vardecimal_storage_format', 'sp_dbcmptlevel', 'sp_dbfixedrolepermission',
            'sp_dbmmonitoraddmonitoring', 'sp_dbmmonitorchangealert', 'sp_dbmmonitorchangemonitoring', 'sp_dbmmonitordropalert',
            'sp_dbmmonitordropmonitoring', 'sp_dbmmonitorhelpalert', 'sp_dbmmonitorhelpmonitoring', 'sp_dbmmonitorresults', 'sp_dbmmonitorupdate',
            'sp_dbremove', 'sp_ddopen', 'sp_defaultdb', 'sp_defaultlanguage', 'sp_delete_log_shipping_alert_job',
            'sp_delete_log_shipping_primary_database', 'sp_delete_log_shipping_primary_secondary', 'sp_delete_log_shipping_secondary_database',
            'sp_delete_log_shipping_secondary_primary', 'sp_deletemergeconflictrow', 'sp_deletepeerrequesthistory', 'sp_deletetracertokenhistory',
            'sp_denylogin', 'sp_depends', 'sp_describe_cursor', 'sp_describe_cursor_columns', 'sp_describe_cursor_tables', 'sp_detach_db',
            'sp_disableagentoffload', 'sp_distcounters', 'sp_drop_agent_parameter', 'sp_drop_agent_profile', 'sp_dropanonymousagent',
            'sp_dropanonymoussubscription', 'sp_dropapprole', 'sp_droparticle', 'sp_dropdatatypemapping', 'sp_dropdevice', 'sp_dropdistpublisher',
            'sp_dropdistributiondb', 'sp_dropdistributor', 'sp_dropdynamicsnapshot_job', 'sp_dropextendedproc', 'sp_dropextendedproperty',
            'sp_droplinkedsrvlogin', 'sp_droplogin', 'sp_dropmergealternatepublisher', 'sp_dropmergearticle', 'sp_dropmergefilter',
            'sp_dropmergelogsettings', 'sp_dropmergepartition', 'sp_dropmergepublication', 'sp_dropmergepullsubscription',
            'sp_dropmergesubscription', 'sp_dropmessage', 'sp_droppublication', 'sp_droppublisher', 'sp_droppullsubscription',
            'sp_dropremotelogin', 'sp_dropreplsymmetrickey', 'sp_droprole', 'sp_droprolemember', 'sp_dropserver', 'sp_dropsrvrolemember',
            'sp_dropsubscriber', 'sp_dropsubscription', 'sp_droptype', 'sp_dropuser', 'sp_dsninfo', 'sp_enable_heterogeneous_subscription',
            'sp_enableagentoffload', 'sp_enum_oledb_providers', 'sp_enumcustomresolvers', 'sp_enumdsn', 'sp_enumeratependingschemachanges',
            'sp_enumerrorlogs', 'sp_enumfullsubscribers', 'sp_enumoledbdatasources', 'sp_estimate_data_compression_savings',
            'sp_estimated_rowsize_reduction_for_vardecimal', 'sp_expired_subscription_cleanup', 'sp_filestream_force_garbage_collection',
            'sp_filestream_recalculate_container_size', 'sp_firstonly_bitmap', 'sp_fkeys', 'sp_flush_commit_table',
            'sp_flush_commit_table_on_demand', 'sp_foreign_keys_rowset', 'sp_foreign_keys_rowset_rmt', 'sp_foreign_keys_rowset2',
            'sp_foreign_keys_rowset3', 'sp_foreignkeys', 'sp_fulltext_catalog', 'sp_fulltext_column', 'sp_fulltext_database',
            'sp_fulltext_load_thesaurus_file', 'sp_fulltext_recycle_crawl_log', 'sp_fulltext_semantic_register_language_statistics_db',
            'sp_fulltext_semantic_unregister_language_statistics_db', 'sp_fulltext_service', 'sp_fulltext_table', 'sp_generate_agent_parameter',
            'sp_generatefilters', 'sp_get_distributor', 'sp_get_job_status_mergesubscription_agent', 'sp_get_mergepublishedarticleproperties',
            'sp_get_Oracle_publisher_metadata', 'sp_get_redirected_publisher', 'sp_getagentparameterlist', 'sp_getapplock',
            'sp_getdefaultdatatypemapping', 'sp_getmergedeletetype', 'sp_getProcessorUsage', 'sp_getpublisherlink',
            'sp_getqueuedarticlesynctraninfo', 'sp_getqueuedrows', 'sp_getsqlqueueversion', 'sp_getsubscription_status_hsnapshot',
            'sp_getsubscriptiondtspackagename', 'sp_gettopologyinfo', 'sp_getVolumeFreeSpace', 'sp_grant_publication_access', 'sp_grantdbaccess',
            'sp_grantlogin', 'sp_help', 'sp_help_agent_default', 'sp_help_agent_parameter', 'sp_help_agent_profile', 'sp_help_datatype_mapping',
            'sp_help_fulltext_catalog_components', 'sp_help_fulltext_catalogs', 'sp_help_fulltext_catalogs_cursor', 'sp_help_fulltext_columns',
            'sp_help_fulltext_columns_cursor', 'sp_help_fulltext_system_components', 'sp_help_fulltext_tables', 'sp_help_fulltext_tables_cursor',
            'sp_help_log_shipping_alert_job', 'sp_help_log_shipping_monitor', 'sp_help_log_shipping_monitor_primary',
            'sp_help_log_shipping_monitor_secondary', 'sp_help_log_shipping_primary_database', 'sp_help_log_shipping_primary_secondary',
            'sp_help_log_shipping_secondary_database', 'sp_help_log_shipping_secondary_primary', 'sp_help_peerconflictdetection',
            'sp_help_publication_access', 'sp_help_spatial_geography_histogram', 'sp_help_spatial_geography_index',
            'sp_help_spatial_geography_index_xml', 'sp_help_spatial_geometry_histogram', 'sp_help_spatial_geometry_index',
            'sp_help_spatial_geometry_index_xml', 'sp_helpallowmerge_publication', 'sp_helparticle', 'sp_helparticlecolumns', 'sp_helparticledts',
            'sp_helpconstraint', 'sp_helpdatatypemap', 'sp_helpdb', 'sp_helpdbfixedrole', 'sp_helpdevice', 'sp_helpdistpublisher',
            'sp_helpdistributiondb', 'sp_helpdistributor', 'sp_helpdistributor_properties', 'sp_helpdynamicsnapshot_job', 'sp_helpextendedproc',
            'sp_helpfile', 'sp_helpfilegroup', 'sp_helpindex', 'sp_helplanguage', 'sp_helplinkedsrvlogin', 'sp_helplogins', 'sp_helplogreader_agent',
            'sp_helpmergealternatepublisher', 'sp_helpmergearticle', 'sp_helpmergearticlecolumn', 'sp_helpmergearticleconflicts',
            'sp_helpmergeconflictrows', 'sp_helpmergedeleteconflictrows', 'sp_helpmergefilter', 'sp_helpmergelogfiles',
            'sp_helpmergelogfileswithdata', 'sp_helpmergelogsettings', 'sp_helpmergepartition', 'sp_helpmergepublication',
            'sp_helpmergepullsubscription', 'sp_helpmergesubscription', 'sp_helpntgroup', 'sp_helppeerrequests', 'sp_helppeerresponses',
            'sp_helppublication', 'sp_helppublication_snapshot', 'sp_helppublicationsync', 'sp_helppullsubscription', 'sp_helpqreader_agent',
            'sp_helpremotelogin', 'sp_helpreplfailovermode', 'sp_helpreplicationdb', 'sp_helpreplicationdboption', 'sp_helpreplicationoption',
            'sp_helprole', 'sp_helprolemember', 'sp_helprotect', 'sp_helpserver', 'sp_helpsort', 'sp_helpsrvrole', 'sp_helpsrvrolemember',
            'sp_helpstats', 'sp_helpsubscriberinfo', 'sp_helpsubscription', 'sp_helpsubscription_properties', 'sp_helpsubscriptionerrors',
            'sp_helptext', 'sp_helptracertokenhistory', 'sp_helptracertokens', 'sp_helptrigger', 'sp_helpuser', 'sp_helpxactsetjob',
            'sp_hexadecimal', 'sp_http_generate_wsdl_defaultcomplexorsimple', 'sp_http_generate_wsdl_defaultsimpleorcomplex',
            'sp_identitycolumnforreplication', 'sp_IH_LR_GetCacheData', 'sp_IHadd_sync_command', 'sp_IHarticlecolumn',
            'sp_IHget_loopback_detection', 'sp_IHScriptIdxFile', 'sp_IHScriptSchFile', 'sp_IHValidateRowFilter', 'sp_IHXactSetJob',
            'sp_indexcolumns_managed', 'sp_indexes', 'sp_indexes_100_rowset', 'sp_indexes_100_rowset2', 'sp_indexes_90_rowset',
            'sp_indexes_90_rowset_rmt', 'sp_indexes_90_rowset2', 'sp_indexes_managed', 'sp_indexes_rowset', 'sp_indexes_rowset_rmt',
            'sp_indexes_rowset2', 'sp_indexoption', 'sp_invalidate_textptr', 'sp_is_makegeneration_needed', 'sp_ivindexhasnullcols',
            'sp_kill_filestream_non_transacted_handles', 'sp_lightweightmergemetadataretentioncleanup', 'sp_link_publication',
            'sp_linkedservers', 'sp_linkedservers_rowset', 'sp_linkedservers_rowset2', 'sp_lock', 'sp_logshippinginstallmetadata',
            'sp_lookupcustomresolver', 'sp_mapdown_bitmap', 'sp_markpendingschemachange', 'sp_marksubscriptionvalidation', 'sp_mergearticlecolumn',
            'sp_mergecleanupmetadata', 'sp_mergedummyupdate', 'sp_mergemetadataretentioncleanup', 'sp_mergesubscription_cleanup',
            'sp_mergesubscriptionsummary', 'sp_monitor',
            'sp_objectfilegroup',
            'sp_oledb_database', 'sp_oledb_defdb', 'sp_oledb_deflang', 'sp_oledb_language', 'sp_oledb_ro_usrname', 'sp_oledbinfo', 'sp_ORbitmap',
            'sp_password', 'sp_peerconflictdetection_tableaug', 'sp_pkeys', 'sp_posttracertoken', 'sp_primary_keys_rowset',
            'sp_primary_keys_rowset_rmt', 'sp_primary_keys_rowset2', 'sp_primarykeys', 'sp_procedure_params_100_managed',
            'sp_procedure_params_100_rowset', 'sp_procedure_params_100_rowset2', 'sp_procedure_params_90_rowset', 'sp_procedure_params_90_rowset2',
            'sp_procedure_params_managed', 'sp_procedure_params_rowset', 'sp_procedure_params_rowset2', 'sp_procedures_rowset', 'sp_procedures_rowset2',
            'sp_processlogshippingmonitorhistory', 'sp_processlogshippingmonitorprimary', 'sp_processlogshippingmonitorsecondary',
            'sp_processlogshippingretentioncleanup', 'sp_procoption', 'sp_prop_oledb_provider', 'sp_provider_types_100_rowset',
            'sp_provider_types_90_rowset', 'sp_provider_types_rowset', 'sp_publication_validation', 'sp_publicationsummary', 'sp_publishdb',
            'sp_publisherproperty', 'sp_readerrorlog', 'sp_recompile', 'sp_redirect_publisher', 'sp_refresh_heterogeneous_publisher',
            'sp_refresh_log_shipping_monitor', 'sp_refreshsqlmodule', 'sp_refreshsubscriptions', 'sp_refreshview', 'sp_register_custom_scripting',
            'sp_registercustomresolver', 'sp_reinitmergepullsubscription', 'sp_reinitmergesubscription', 'sp_reinitpullsubscription',
            'sp_reinitsubscription', 'sp_releaseapplock', 'sp_remoteoption', 'sp_removedbreplication', 'sp_removedistpublisherdbreplication',
            'sp_removesrvreplication', 'sp_rename', 'sp_renamedb', 'sp_repladdcolumn', 'sp_replcleanupccsprocs', 'sp_repldeletequeuedtran',
            'sp_repldropcolumn', 'sp_replgetparsedddlcmd', 'sp_replica', 'sp_replication_agent_checkup', 'sp_replicationdboption', 'sp_replincrementlsn',
            'sp_replmonitorchangepublicationthreshold', 'sp_replmonitorhelpmergesession', 'sp_replmonitorhelpmergesessiondetail',
            'sp_replmonitorhelpmergesubscriptionmoreinfo', 'sp_replmonitorhelppublication', 'sp_replmonitorhelppublicationthresholds',
            'sp_replmonitorhelppublisher', 'sp_replmonitorhelpsubscription', 'sp_replmonitorrefreshjob', 'sp_replmonitorsubscriptionpendingcmds',
            'sp_replpostsyncstatus', 'sp_replqueuemonitor', 'sp_replrestart', 'sp_replrethrow', 'sp_replsetoriginator', 'sp_replshowcmds',
            'sp_replsqlqgetrows', 'sp_replsync', 'sp_requestpeerresponse', 'sp_requestpeertopologyinfo', 'sp_resetsnapshotdeliveryprogress',
            'sp_resetstatus', 'sp_resign_database', 'sp_resolve_logins', 'sp_restoredbreplication', 'sp_restoremergeidentityrange',
            'sp_resyncmergesubscription', 'sp_revoke_publication_access', 'sp_revokedbaccess', 'sp_revokelogin', 'sp_schemafilter', 'sp_schemata_rowset',
            'sp_script_reconciliation_delproc', 'sp_script_reconciliation_insproc', 'sp_script_reconciliation_sinsproc',
            'sp_script_reconciliation_vdelproc', 'sp_script_reconciliation_xdelproc', 'sp_script_synctran_commands', 'sp_scriptdelproc',
            'sp_scriptdynamicupdproc', 'sp_scriptinsproc', 'sp_scriptmappedupdproc', 'sp_scriptpublicationcustomprocs', 'sp_scriptsinsproc',
            'sp_scriptsubconflicttable', 'sp_scriptsupdproc', 'sp_scriptupdproc', 'sp_scriptvdelproc', 'sp_scriptvupdproc', 'sp_scriptxdelproc',
            'sp_scriptxupdproc', 'sp_sequence_get_range', 'sp_server_info', 'sp_serveroption', 'sp_setapprole', 'sp_SetAutoSAPasswordAndDisable',
            'sp_setdefaultdatatypemapping', 'sp_setnetname', 'sp_setOraclepackageversion', 'sp_setreplfailovermode', 'sp_setsubscriptionxactseqno',
            'sp_settriggerorder', 'sp_showcolv', 'sp_showlineage', 'sp_showpendingchanges', 'sp_showrowreplicainfo', 'sp_spaceused',
            'sp_sparse_columns_100_rowset', 'sp_special_columns', 'sp_special_columns_100', 'sp_special_columns_90', 'sp_sproc_columns',
            'sp_sproc_columns_100', 'sp_sproc_columns_90', 'sp_sqlexec', 'sp_srvrolepermission', 'sp_startmergepullsubscription_agent',
            'sp_startmergepushsubscription_agent', 'sp_startpublication_snapshot', 'sp_startpullsubscription_agent', 'sp_startpushsubscription_agent',
            'sp_statistics', 'sp_statistics_100', 'sp_statistics_rowset', 'sp_statistics_rowset2', 'sp_stopmergepullsubscription_agent',
            'sp_stopmergepushsubscription_agent', 'sp_stoppublication_snapshot', 'sp_stoppullsubscription_agent', 'sp_stoppushsubscription_agent',
            'sp_stored_procedures', 'sp_subscribe', 'sp_subscription_cleanup', 'sp_subscriptionsummary', 'sp_syspolicy_execute_policy',
            'sp_syspolicy_subscribe_to_policy_category', 'sp_syspolicy_unsubscribe_from_policy_category', 'sp_syspolicy_update_ddl_trigger',
            'sp_syspolicy_update_event_notification', 'sp_table_constraints_rowset', 'sp_table_constraints_rowset2', 'sp_table_privileges',
            'sp_table_privileges_ex', 'sp_table_privileges_rowset', 'sp_table_privileges_rowset_rmt', 'sp_table_privileges_rowset2',
            'sp_table_statistics_rowset', 'sp_table_statistics2_rowset', 'sp_table_type_columns_100', 'sp_table_type_columns_100_rowset',
            'sp_table_type_pkeys', 'sp_table_type_primary_keys_rowset', 'sp_table_types', 'sp_table_types_rowset', 'sp_table_validation',
            'sp_tablecollations', 'sp_tablecollations_100', 'sp_tablecollations_90', 'sp_tableoption', 'sp_tables', 'sp_tables_ex',
            'sp_tables_info_90_rowset', 'sp_tables_info_90_rowset_64', 'sp_tables_info_90_rowset2', 'sp_tables_info_90_rowset2_64',
            'sp_tables_info_rowset', 'sp_tables_info_rowset_64', 'sp_tables_info_rowset2', 'sp_tables_info_rowset2_64', 'sp_tables_rowset',
            'sp_tables_rowset_rmt', 'sp_tables_rowset2', 'sp_tableswc', 'sp_trace_getdata', 'sp_unbindefault', 'sp_unbindrule',
            'sp_unregister_custom_scripting', 'sp_unregistercustomresolver', 'sp_unsetapprole', 'sp_unsubscribe', 'sp_update_agent_profile',
            'sp_updateextendedproperty', 'sp_updatestats', 'sp_upgrade_log_shipping', 'sp_user_counter1', 'sp_user_counter10', 'sp_user_counter2',
            'sp_user_counter3', 'sp_user_counter4', 'sp_user_counter5', 'sp_user_counter6', 'sp_user_counter7', 'sp_user_counter8', 'sp_user_counter9',
            'sp_usertypes_rowset', 'sp_usertypes_rowset_rmt', 'sp_usertypes_rowset2', 'sp_validate_redirected_publisher',
            'sp_validate_replica_hosts_as_publishers', 'sp_validatecache', 'sp_validatelogins', 'sp_validatemergepublication',
            'sp_validatemergepullsubscription', 'sp_validatemergesubscription', 'sp_validlang', 'sp_validname', 'sp_verifypublisher', 'sp_views_rowset',
            'sp_views_rowset2', 'sp_vupgrade_mergeobjects', 'sp_vupgrade_mergetables',  'sp_vupgrade_replication', 'sp_vupgrade_replsecurity_metadata',
            'sp_who', 'sp_who2', 'sp_xml_schema_rowset', 'sp_xml_schema_rowset2',
        ),
        6 => array(

            // system MS procedures, marked brown.

            'sp_MS_marksystemobject', 'sp_MS_replication_installed', 'sp_MSacquireHeadofQueueLock',
            'sp_MSacquireserverresourcefordynamicsnapshot', 'sp_MSacquireSlotLock', 'sp_MSacquiresnapshotdeliverysessionlock',
            'sp_MSactivate_auto_sub', 'sp_MSactivatelogbasedarticleobject', 'sp_MSactivateprocedureexecutionarticleobject',
            'sp_MSadd_anonymous_agent', 'sp_MSadd_article', 'sp_MSadd_compensating_cmd', 'sp_MSadd_distribution_agent',
            'sp_MSadd_distribution_history', 'sp_MSadd_dynamic_snapshot_location', 'sp_MSadd_filteringcolumn', 'sp_MSadd_log_shipping_error_detail',
            'sp_MSadd_log_shipping_history_detail', 'sp_MSadd_logreader_agent', 'sp_MSadd_logreader_history', 'sp_MSadd_merge_agent',
            'sp_MSadd_merge_anonymous_agent', 'sp_MSadd_merge_history', 'sp_MSadd_merge_history90', 'sp_MSadd_merge_subscription',
            'sp_MSadd_mergereplcommand', 'sp_MSadd_mergesubentry_indistdb', 'sp_MSadd_publication', 'sp_MSadd_qreader_agent',
            'sp_MSadd_qreader_history', 'sp_MSadd_repl_alert', 'sp_MSadd_repl_command', 'sp_MSadd_repl_commands27hp', 'sp_MSadd_repl_error',
            'sp_MSadd_replcmds_mcit', 'sp_MSadd_replmergealert', 'sp_MSadd_snapshot_agent', 'sp_MSadd_snapshot_history', 'sp_MSadd_subscriber_info',
            'sp_MSadd_subscriber_schedule', 'sp_MSadd_subscription', 'sp_MSadd_subscription_3rd', 'sp_MSadd_tracer_history', 'sp_MSadd_tracer_token',
            'sp_MSaddanonymousreplica', 'sp_MSadddynamicsnapshotjobatdistributor', 'sp_MSaddguidcolumn', 'sp_MSaddguidindex', 'sp_MSaddinitialarticle',
            'sp_MSaddinitialpublication', 'sp_MSaddinitialschemaarticle', 'sp_MSaddinitialsubscription', 'sp_MSaddlightweightmergearticle',
            'sp_MSaddmergedynamicsnapshotjob', 'sp_MSaddmergetriggers', 'sp_MSaddmergetriggers_from_template', 'sp_MSaddmergetriggers_internal',
            'sp_MSaddpeerlsn', 'sp_MSaddsubscriptionarticles', 'sp_MSadjust_pub_identity', 'sp_MSagent_retry_stethoscope', 'sp_MSagent_stethoscope',
            'sp_MSallocate_new_identity_range', 'sp_MSalreadyhavegeneration', 'sp_MSanonymous_status', 'sp_MSarticlecleanup',
            'sp_MSbrowsesnapshotfolder', 'sp_MScache_agent_parameter', 'sp_MScdc_capture_job', 'sp_MScdc_cleanup_job', 'sp_MScdc_db_ddl_event',
            'sp_MScdc_ddl_event', 'sp_MScdc_logddl', 'sp_MSchange_article', 'sp_MSchange_distribution_agent_properties',
            'sp_MSchange_logreader_agent_properties', 'sp_MSchange_merge_agent_properties', 'sp_MSchange_mergearticle', 'sp_MSchange_mergepublication',
            'sp_MSchange_originatorid', 'sp_MSchange_priority', 'sp_MSchange_publication', 'sp_MSchange_retention', 'sp_MSchange_retention_period_unit',
            'sp_MSchange_snapshot_agent_properties', 'sp_MSchange_subscription_dts_info', 'sp_MSchangearticleresolver',
            'sp_MSchangedynamicsnapshotjobatdistributor', 'sp_MSchangedynsnaplocationatdistributor', 'sp_MSchangeobjectowner',
            'sp_MScheck_agent_instance', 'sp_MScheck_Jet_Subscriber', 'sp_MScheck_logicalrecord_metadatamatch', 'sp_MScheck_merge_subscription_count',
            'sp_MScheck_pub_identity', 'sp_MScheck_pull_access', 'sp_MScheck_snapshot_agent', 'sp_MScheck_subscription', 'sp_MScheck_subscription_expiry',
            'sp_MScheck_subscription_partition', 'sp_MScheck_tran_retention', 'sp_MScheckexistsgeneration', 'sp_MScheckexistsrecguid',
            'sp_MScheckfailedprevioussync', 'sp_MScheckidentityrange', 'sp_MScheckIsPubOfSub', 'sp_MSchecksharedagentforpublication',
            'sp_MSchecksnapshotstatus', 'sp_MScleanup_agent_entry', 'sp_MScleanup_conflict', 'sp_MScleanup_publication_ADinfo',
            'sp_MScleanup_subscription_distside_entry', 'sp_MScleanupdynamicsnapshotfolder', 'sp_MScleanupdynsnapshotvws', 'sp_MSCleanupForPullReinit',
            'sp_MScleanupmergepublisher_internal', 'sp_MSclear_dynamic_snapshot_location', 'sp_MSclearresetpartialsnapshotprogressbit',
            'sp_MScomputelastsentgen', 'sp_MScomputemergearticlescreationorder', 'sp_MScomputemergeunresolvedrefs', 'sp_MSconflicttableexists',
            'sp_MScreate_all_article_repl_views', 'sp_MScreate_article_repl_views', 'sp_MScreate_dist_tables', 'sp_MScreate_logical_record_views',
            'sp_MScreate_sub_tables', 'sp_MScreate_tempgenhistorytable', 'sp_MScreatedisabledmltrigger', 'sp_MScreatedummygeneration',
            'sp_MScreateglobalreplica', 'sp_MScreatelightweightinsertproc', 'sp_MScreatelightweightmultipurposeproc',
            'sp_MScreatelightweightprocstriggersconstraints', 'sp_MScreatelightweightupdateproc', 'sp_MScreatemergedynamicsnapshot',
            'sp_MScreateretry', 'sp_MSdbuseraccess', 'sp_MSdbuserpriv', 'sp_MSdefer_check', 'sp_MSdelete_tracer_history', 'sp_MSdeletefoldercontents',
            'sp_MSdeletemetadataactionrequest', 'sp_MSdeletepeerconflictrow', 'sp_MSdeleteretry', 'sp_MSdeletetranconflictrow', 'sp_MSdelgenzero',
            'sp_MSdelrow', 'sp_MSdelrowsbatch', 'sp_MSdelrowsbatch_downloadonly', 'sp_MSdelsubrows', 'sp_MSdelsubrowsbatch', 'sp_MSdependencies',
            'sp_MSdetect_nonlogged_shutdown', 'sp_MSdetectinvalidpeerconfiguration', 'sp_MSdetectinvalidpeersubscription', 'sp_MSdist_activate_auto_sub',
            'sp_MSdist_adjust_identity', 'sp_MSdistpublisher_cleanup', 'sp_MSdistribution_counters', 'sp_MSdistributoravailable',
            'sp_MSdodatabasesnapshotinitiation', 'sp_MSdopartialdatabasesnapshotinitiation', 'sp_MSdrop_6x_publication', 'sp_MSdrop_6x_replication_agent',
            'sp_MSdrop_anonymous_entry', 'sp_MSdrop_article', 'sp_MSdrop_distribution_agent', 'sp_MSdrop_distribution_agentid_dbowner_proxy',
            'sp_MSdrop_dynamic_snapshot_agent', 'sp_MSdrop_logreader_agent', 'sp_MSdrop_merge_agent', 'sp_MSdrop_merge_subscription',
            'sp_MSdrop_publication', 'sp_MSdrop_qreader_history', 'sp_MSdrop_snapshot_agent', 'sp_MSdrop_snapshot_dirs', 'sp_MSdrop_subscriber_info',
            'sp_MSdrop_subscription', 'sp_MSdrop_subscription_3rd', 'sp_MSdrop_tempgenhistorytable', 'sp_MSdroparticleconstraints',
            'sp_MSdroparticletombstones', 'sp_MSdropconstraints', 'sp_MSdropdynsnapshotvws', 'sp_MSdropfkreferencingarticle', 'sp_MSdropmergearticle',
            'sp_MSdropmergedynamicsnapshotjob', 'sp_MSdropretry', 'sp_MSdroptemptable', 'sp_MSdummyupdate', 'sp_MSdummyupdate_logicalrecord',
            'sp_MSdummyupdate90', 'sp_MSdummyupdatelightweight', 'sp_MSdynamicsnapshotjobexistsatdistributor', 'sp_MSenable_publication_for_het_sub',
            'sp_MSensure_single_instance', 'sp_MSenum_distribution', 'sp_MSenum_distribution_s', 'sp_MSenum_distribution_sd',
            'sp_MSenum_logicalrecord_changes', 'sp_MSenum_logreader', 'sp_MSenum_logreader_s', 'sp_MSenum_logreader_sd', 'sp_MSenum_merge',
            'sp_MSenum_merge_agent_properties', 'sp_MSenum_merge_s', 'sp_MSenum_merge_sd', 'sp_MSenum_merge_subscriptions',
            'sp_MSenum_merge_subscriptions_90_publication', 'sp_MSenum_merge_subscriptions_90_publisher', 'sp_MSenum_metadataaction_requests',
            'sp_MSenum_qreader', 'sp_MSenum_qreader_s', 'sp_MSenum_qreader_sd', 'sp_MSenum_replication_agents', 'sp_MSenum_replication_job',
            'sp_MSenum_replqueues', 'sp_MSenum_replsqlqueues', 'sp_MSenum_snapshot', 'sp_MSenum_snapshot_s', 'sp_MSenum_snapshot_sd',
            'sp_MSenum_subscriptions', 'sp_MSenumallpublications', 'sp_MSenumallsubscriptions', 'sp_MSenumarticleslightweight', 'sp_MSenumchanges',
            'sp_MSenumchanges_belongtopartition', 'sp_MSenumchanges_notbelongtopartition', 'sp_MSenumchangesdirect', 'sp_MSenumchangeslightweight',
            'sp_MSenumcolumns', 'sp_MSenumcolumnslightweight', 'sp_MSenumdeletes_forpartition', 'sp_MSenumdeleteslightweight', 'sp_MSenumdeletesmetadata',
            'sp_MSenumdistributionagentproperties', 'sp_MSenumerate_PAL', 'sp_MSenumgenerations', 'sp_MSenumgenerations90', 'sp_MSenumpartialchanges',
            'sp_MSenumpartialchangesdirect', 'sp_MSenumpartialdeletes', 'sp_MSenumpubreferences', 'sp_MSenumreplicas', 'sp_MSenumreplicas90',
            'sp_MSenumretries', 'sp_MSenumschemachange', 'sp_MSenumsubscriptions', 'sp_MSenumthirdpartypublicationvendornames',
            'sp_MSestimatemergesnapshotworkload', 'sp_MSestimatesnapshotworkload', 'sp_MSevalsubscriberinfo',
            'sp_MSevaluate_change_membership_for_all_articles_in_pubid', 'sp_MSevaluate_change_membership_for_pubid',
            'sp_MSevaluate_change_membership_for_row', 'sp_MSexecwithlsnoutput', 'sp_MSfast_delete_trans', 'sp_MSfetchAdjustidentityrange',
            'sp_MSfetchidentityrange', 'sp_MSfillupmissingcols', 'sp_MSfilterclause', 'sp_MSfix_6x_tasks', 'sp_MSfixlineageversions',
            'sp_MSFixSubColumnBitmaps', 'sp_MSfixupbeforeimagetables', 'sp_MSflush_access_cache', 'sp_MSforce_drop_distribution_jobs',
            'sp_MSforcereenumeration', 'sp_MSforeach_worker', 'sp_MSforeachdb', 'sp_MSforeachtable', 'sp_MSgenerateexpandproc', 'sp_MSget_agent_names',
            'sp_MSget_attach_state', 'sp_MSget_DDL_after_regular_snapshot', 'sp_MSget_dynamic_snapshot_location', 'sp_MSget_identity_range_info',
            'sp_MSget_jobstate', 'sp_MSget_last_transaction', 'sp_MSget_latest_peerlsn', 'sp_MSget_load_hint', 'sp_MSget_log_shipping_new_sessionid',
            'sp_MSget_logicalrecord_lineage', 'sp_MSget_max_used_identity', 'sp_MSget_min_seqno', 'sp_MSget_MSmerge_rowtrack_colinfo',
            'sp_MSget_new_xact_seqno', 'sp_MSget_oledbinfo', 'sp_MSget_partitionid_eval_proc', 'sp_MSget_publication_from_taskname',
            'sp_MSget_publisher_rpc', 'sp_MSget_repl_cmds_anonymous', 'sp_MSget_repl_commands', 'sp_MSget_repl_error', 'sp_MSget_session_statistics',
            'sp_MSget_shared_agent', 'sp_MSget_snapshot_history', 'sp_MSget_subscriber_partition_id', 'sp_MSget_subscription_dts_info',
            'sp_MSget_subscription_guid', 'sp_MSget_synctran_commands', 'sp_MSget_type_wrapper', 'sp_MSgetagentoffloadinfo', 'sp_MSgetalertinfo',
            'sp_MSgetalternaterecgens', 'sp_MSgetarticlereinitvalue', 'sp_MSgetchangecount', 'sp_MSgetconflictinsertproc', 'sp_MSgetconflicttablename',
            'sp_MSGetCurrentPrincipal', 'sp_MSgetdatametadatabatch', 'sp_MSgetdbversion', 'sp_MSgetdynamicsnapshotapplock',
            'sp_MSgetdynsnapvalidationtoken', 'sp_MSgetisvalidwindowsloginfromdistributor', 'sp_MSgetlastrecgen', 'sp_MSgetlastsentgen',
            'sp_MSgetlastsentrecgens', 'sp_MSgetlastupdatedtime', 'sp_MSgetlightweightmetadatabatch', 'sp_MSgetmakegenerationapplock',
            'sp_MSgetmakegenerationapplock_90', 'sp_MSgetmaxbcpgen', 'sp_MSgetmaxsnapshottimestamp', 'sp_MSgetmergeadminapplock',
            'sp_MSgetmetadata_changedlogicalrecordmembers', 'sp_MSgetmetadatabatch', 'sp_MSgetmetadatabatch90', 'sp_MSgetmetadatabatch90new',
            'sp_MSgetonerow', 'sp_MSgetonerowlightweight', 'sp_MSgetpeerconflictrow', 'sp_MSgetpeerlsns', 'sp_MSgetpeertopeercommands',
            'sp_MSgetpeerwinnerrow', 'sp_MSgetpubinfo', 'sp_MSgetreplicainfo', 'sp_MSgetreplicastate', 'sp_MSgetrowmetadata',
            'sp_MSgetrowmetadatalightweight', 'sp_MSGetServerProperties', 'sp_MSgetsetupbelong_cost', 'sp_MSgetsubscriberinfo',
            'sp_MSgetsupportabilitysettings', 'sp_MSgettrancftsrcrow', 'sp_MSgettranconflictrow', 'sp_MSgetversion', 'sp_MSgrantconnectreplication',
            'sp_MShaschangeslightweight', 'sp_MShasdbaccess', 'sp_MShelp_article', 'sp_MShelp_distdb', 'sp_MShelp_distribution_agentid',
            'sp_MShelp_identity_property', 'sp_MShelp_logreader_agentid', 'sp_MShelp_merge_agentid', 'sp_MShelp_profile', 'sp_MShelp_profilecache',
            'sp_MShelp_publication', 'sp_MShelp_repl_agent', 'sp_MShelp_replication_status', 'sp_MShelp_replication_table', 'sp_MShelp_snapshot_agent',
            'sp_MShelp_snapshot_agentid', 'sp_MShelp_subscriber_info', 'sp_MShelp_subscription', 'sp_MShelp_subscription_status', 'sp_MShelpcolumns',
            'sp_MShelpconflictpublications', 'sp_MShelpcreatebeforetable', 'sp_MShelpdestowner', 'sp_MShelpdynamicsnapshotjobatdistributor',
            'sp_MShelpfulltextindex', 'sp_MShelpfulltextscript', 'sp_MShelpindex', 'sp_MShelplogreader_agent', 'sp_MShelpmergearticles',
            'sp_MShelpmergeconflictcounts', 'sp_MShelpmergedynamicsnapshotjob', 'sp_MShelpmergeidentity', 'sp_MShelpmergeschemaarticles',
            'sp_MShelpobjectpublications', 'sp_MShelpreplicationtriggers', 'sp_MShelpsnapshot_agent', 'sp_MShelpsummarypublication',
            'sp_MShelptracertokenhistory', 'sp_MShelptracertokens', 'sp_MShelptranconflictcounts', 'sp_MShelptype', 'sp_MShelpvalidationdate',
            'sp_MSIfExistsSubscription', 'sp_MSindexspace', 'sp_MSinit_publication_access', 'sp_MSinit_subscription_agent', 'sp_MSinitdynamicsubscriber',
            'sp_MSinsert_identity', 'sp_MSinsertdeleteconflict', 'sp_MSinserterrorlineage', 'sp_MSinsertgenerationschemachanges', 'sp_MSinsertgenhistory',
            'sp_MSinsertlightweightschemachange', 'sp_MSinsertschemachange', 'sp_MSinvalidate_snapshot', 'sp_MSisnonpkukupdateinconflict',
            'sp_MSispeertopeeragent', 'sp_MSispkupdateinconflict', 'sp_MSispublicationqueued', 'sp_MSisreplmergeagent', 'sp_MSissnapshotitemapplied',
            'sp_MSkilldb', 'sp_MSlock_auto_sub', 'sp_MSlock_distribution_agent', 'sp_MSlocktable', 'sp_MSloginmappings', 'sp_MSmakearticleprocs',
            'sp_MSmakebatchinsertproc', 'sp_MSmakebatchupdateproc', 'sp_MSmakeconflictinsertproc', 'sp_MSmakectsview', 'sp_MSmakedeleteproc',
            'sp_MSmakedynsnapshotvws', 'sp_MSmakeexpandproc', 'sp_MSmakegeneration', 'sp_MSmakeinsertproc', 'sp_MSmakemetadataselectproc',
            'sp_MSmakeselectproc', 'sp_MSmakesystableviews', 'sp_MSmakeupdateproc', 'sp_MSmap_partitionid_to_generations', 'sp_MSmarkreinit',
            'sp_MSmatchkey', 'sp_MSmerge_alterschemaonly', 'sp_MSmerge_altertrigger', 'sp_MSmerge_alterview', 'sp_MSmerge_ddldispatcher',
            'sp_MSmerge_getgencount', 'sp_MSmerge_getgencur_public', 'sp_MSmerge_is_snapshot_required', 'sp_MSmerge_log_identity_range_allocations',
            'sp_MSmerge_parsegenlist', 'sp_MSmerge_upgrade_subscriber', 'sp_MSmergesubscribedb', 'sp_MSmergeupdatelastsyncinfo',
            'sp_MSneedmergemetadataretentioncleanup', 'sp_MSNonSQLDDL', 'sp_MSNonSQLDDLForSchemaDDL', 'sp_MSobjectprivs', 'sp_MSpeerapplyresponse',
            'sp_MSpeerapplytopologyinfo', 'sp_MSpeerconflictdetection_statuscollection_applyresponse',
            'sp_MSpeerconflictdetection_statuscollection_sendresponse', 'sp_MSpeerconflictdetection_topology_applyresponse', 'sp_MSpeerdbinfo',
            'sp_MSpeersendresponse', 'sp_MSpeersendtopologyinfo', 'sp_MSpeertopeerfwdingexec', 'sp_MSpost_auto_proc',
            'sp_MSpostapplyscript_forsubscriberprocs', 'sp_MSprep_exclusive', 'sp_MSprepare_mergearticle', 'sp_MSprofile_in_use',
            'sp_MSproxiedmetadata', 'sp_MSproxiedmetadatabatch', 'sp_MSproxiedmetadatalightweight', 'sp_MSpub_adjust_identity',
            'sp_MSpublication_access', 'sp_MSpublicationcleanup', 'sp_MSpublicationview', 'sp_MSquery_syncstates', 'sp_MSquerysubtype',
            'sp_MSrecordsnapshotdeliveryprogress', 'sp_MSreenable_check', 'sp_MSrefresh_anonymous', 'sp_MSrefresh_publisher_idrange',
            'sp_MSregenerate_mergetriggersprocs', 'sp_MSregisterdynsnapseqno', 'sp_MSregistermergesnappubid', 'sp_MSregistersubscription',
            'sp_MSreinit_failed_subscriptions', 'sp_MSreinit_hub', 'sp_MSreinit_subscription', 'sp_MSreinitoverlappingmergepublications',
            'sp_MSreleasedynamicsnapshotapplock', 'sp_MSreleasemakegenerationapplock', 'sp_MSreleasemergeadminapplock', 'sp_MSreleaseSlotLock',
            'sp_MSreleasesnapshotdeliverysessionlock', 'sp_MSremove_mergereplcommand', 'sp_MSremoveoffloadparameter', 'sp_MSrepl_agentstatussummary',
            'sp_MSrepl_backup_complete', 'sp_MSrepl_backup_start', 'sp_MSrepl_check_publisher', 'sp_MSrepl_createdatatypemappings',
            'sp_MSrepl_distributionagentstatussummary', 'sp_MSrepl_dropdatatypemappings', 'sp_MSrepl_enumarticlecolumninfo',
            'sp_MSrepl_enumpublications', 'sp_MSrepl_enumpublishertables', 'sp_MSrepl_enumsubscriptions', 'sp_MSrepl_enumtablecolumninfo',
            'sp_MSrepl_FixPALRole', 'sp_MSrepl_getdistributorinfo', 'sp_MSrepl_getpkfkrelation', 'sp_MSrepl_gettype_mappings',
            'sp_MSrepl_helparticlermo', 'sp_MSrepl_init_backup_lsns', 'sp_MSrepl_isdbowner', 'sp_MSrepl_IsLastPubInSharedSubscription',
            'sp_MSrepl_IsUserInAnyPAL', 'sp_MSrepl_linkedservers_rowset', 'sp_MSrepl_mergeagentstatussummary', 'sp_MSrepl_PAL_rolecheck',
            'sp_MSrepl_raiserror', 'sp_MSrepl_schema', 'sp_MSrepl_setNFR', 'sp_MSrepl_snapshot_helparticlecolumns',
            'sp_MSrepl_snapshot_helppublication', 'sp_MSrepl_startup_internal', 'sp_MSrepl_subscription_rowset', 'sp_MSrepl_testadminconnection',
            'sp_MSrepl_testconnection', 'sp_MSreplagentjobexists', 'sp_MSreplcheck_permission', 'sp_MSreplcheck_pull', 'sp_MSreplcheck_subscribe',
            'sp_MSreplcheck_subscribe_withddladmin', 'sp_MSreplcheckoffloadserver', 'sp_MSreplcopyscriptfile', 'sp_MSreplraiserror',
            'sp_MSreplremoveuncdir', 'sp_MSreplupdateschema', 'sp_MSrequestreenumeration', 'sp_MSrequestreenumeration_lightweight',
            'sp_MSreset_attach_state', 'sp_MSreset_queued_reinit', 'sp_MSreset_subscription', 'sp_MSreset_subscription_seqno',
            'sp_MSreset_synctran_bit', 'sp_MSreset_transaction', 'sp_MSresetsnapshotdeliveryprogress', 'sp_MSrestoresavedforeignkeys',
            'sp_MSretrieve_publication_attributes', 'sp_MSscript_article_view', 'sp_MSscript_dri', 'sp_MSscript_pub_upd_trig',
            'sp_MSscript_sync_del_proc', 'sp_MSscript_sync_del_trig', 'sp_MSscript_sync_ins_proc', 'sp_MSscript_sync_ins_trig',
            'sp_MSscript_sync_upd_proc', 'sp_MSscript_sync_upd_trig', 'sp_MSscriptcustomdelproc', 'sp_MSscriptcustominsproc',
            'sp_MSscriptcustomupdproc', 'sp_MSscriptdatabase', 'sp_MSscriptdb_worker', 'sp_MSscriptforeignkeyrestore', 'sp_MSscriptsubscriberprocs',
            'sp_MSscriptviewproc', 'sp_MSsendtosqlqueue', 'sp_MSset_dynamic_filter_options', 'sp_MSset_logicalrecord_metadata',
            'sp_MSset_new_identity_range', 'sp_MSset_oledb_prop', 'sp_MSset_snapshot_xact_seqno', 'sp_MSset_sub_guid',
            'sp_MSset_subscription_properties', 'sp_MSsetaccesslist', 'sp_MSsetalertinfo', 'sp_MSsetartprocs', 'sp_MSsetbit',
            'sp_MSsetconflictscript', 'sp_MSsetconflicttable', 'sp_MSsetcontext_bypasswholeddleventbit', 'sp_MSsetcontext_replagent',
            'sp_MSsetgentozero', 'sp_MSsetlastrecgen', 'sp_MSsetlastsentgen', 'sp_MSsetreplicainfo', 'sp_MSsetreplicaschemaversion',
            'sp_MSsetreplicastatus', 'sp_MSsetrowmetadata', 'sp_MSSetServerProperties', 'sp_MSsetsubscriberinfo', 'sp_MSsettopology',
            'sp_MSsetup_identity_range', 'sp_MSsetup_partition_groups', 'sp_MSsetup_use_partition_groups', 'sp_MSsetupbelongs',
            'sp_MSsetupnosyncsubwithlsnatdist', 'sp_MSsetupnosyncsubwithlsnatdist_cleanup', 'sp_MSsetupnosyncsubwithlsnatdist_helper',
            'sp_MSSharedFixedDisk', 'sp_MSSQLDMO70_version', 'sp_MSSQLDMO80_version', 'sp_MSSQLDMO90_version', 'sp_MSSQLOLE_version',
            'sp_MSSQLOLE65_version', 'sp_MSstartdistribution_agent', 'sp_MSstartmerge_agent', 'sp_MSstartsnapshot_agent',
            'sp_MSstopdistribution_agent', 'sp_MSstopmerge_agent', 'sp_MSstopsnapshot_agent', 'sp_MSsub_check_identity', 'sp_MSsub_set_identity',
            'sp_MSsubscription_status', 'sp_MSsubscriptionvalidated', 'sp_MStablechecks', 'sp_MStablekeys', 'sp_MStablerefs', 'sp_MStablespace',
            'sp_MStestbit', 'sp_MStran_ddlrepl', 'sp_MStran_is_snapshot_required', 'sp_MStrypurgingoldsnapshotdeliveryprogress', 'sp_MSuniquename',
            'sp_MSunmarkifneeded', 'sp_MSunmarkreplinfo', 'sp_MSunmarkschemaobject', 'sp_MSunregistersubscription', 'sp_MSupdate_agenttype_default',
            'sp_MSupdate_singlelogicalrecordmetadata', 'sp_MSupdate_subscriber_info', 'sp_MSupdate_subscriber_schedule',
            'sp_MSupdate_subscriber_tracer_history', 'sp_MSupdate_subscription', 'sp_MSupdate_tracer_history', 'sp_MSupdatecachedpeerlsn',
            'sp_MSupdategenerations_afterbcp', 'sp_MSupdategenhistory', 'sp_MSupdateinitiallightweightsubscription', 'sp_MSupdatelastsyncinfo',
            'sp_MSupdatepeerlsn', 'sp_MSupdaterecgen', 'sp_MSupdatereplicastate', 'sp_MSupdatesysmergearticles', 'sp_MSuplineageversion',
            'sp_MSuploadsupportabilitydata', 'sp_MSuselightweightreplication', 'sp_MSvalidate_dest_recgen', 'sp_MSvalidate_subscription',
            'sp_MSvalidate_wellpartitioned_articles', 'sp_MSvalidatearticle', 'sp_MSwritemergeperfcounter',

        ),
        7 => array(
            'APPLY', 'FULL', 'ALL', 'AND', 'ANY', 'BETWEEN', 'CROSS',
            'EXISTS', 'IN', 'INNER', 'JOIN', 'LIKE', 'NOT', 'NULL', 'OR', 'OUTER', 'SOME'
        )
    ),
    'SYMBOLS' => array(
        '!', '!=', '%', '&', '&&', '(', ')', '*', '+', '-', '/', '<', '<<', '<=', ';', '::', ',', '.',
        '<=>', '<>', '=', '>', '>=', '>>', '^', '|', '||', '~'
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        6 => false,
        7 => false,
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #FF00FF;',
            2 => 'color: #0000FF;',
            3 => 'color: #AF0000;',
            4 => 'color: #00AF00;',
            5 => 'color: #AF0000;',
            6 => 'color: #AF0000;',
            7 => 'color: #808080;',
        ),
        'COMMENTS' => array(
            1 => 'color: #009E00;',
            'MULTI' => 'color: #009E00;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #ff0000;'
        ),
        'BRACKETS' => array(
            0 => 'color: #808080;'
        ),
        'STRINGS' => array(
            0 => 'color: #FF0000;',
            'HARD' => 'color: #FF0000;'
        ),
        'ESCAPE_CHAR' => array(
            'HARD' => 'color: #FF0000;'
        ),
        'NUMBERS' => array(
            0 => 'color: #000;'
        ),
        'METHODS' => array(
            1 => 'color: #202020;',
            2 => 'color: #202020;'
        ),
        'SYMBOLS' => array(
            0 => 'color: #808080;'
        ),
        'REGEXPS' => array(
            0 => 'color: #cc3333;'
        ),
        'SCRIPT' => array(
        )
    ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
    ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
    ),
    'REGEXPS' => array(
        // variables
        0 => "[\\@]+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"
    ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
    ),
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            2 => array(
                'DISALLOWED_BEFORE' => "(?<![@\[a-zA-Z0-9\$_\|\#|^&'\"])",
                'DISALLOWED_AFTER' => "(?![\]a-zA-Z0-9_\|%\-&;'\"])",
            ),
            7 => array(
                'DISALLOWED_BEFORE' => "(?<![@\[a-zA-Z0-9\$_\|\#|^&'\"])",
                'DISALLOWED_AFTER' => "(?![\]a-zA-Z0-9_\|%\-&;'\"])",
            ),
        )
    )
);
