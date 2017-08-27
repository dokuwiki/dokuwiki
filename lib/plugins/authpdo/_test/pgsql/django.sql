--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.1
-- Dumped by pg_dump version 9.5.1

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: auth_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_group (
    id integer NOT NULL,
    name character varying(80) NOT NULL
);


ALTER TABLE auth_group OWNER TO postgres;

--
-- Name: auth_group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_group_id_seq OWNER TO postgres;

--
-- Name: auth_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_group_id_seq OWNED BY auth_group.id;


--
-- Name: auth_group_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_group_permissions (
    id integer NOT NULL,
    group_id integer NOT NULL,
    permission_id integer NOT NULL
);


ALTER TABLE auth_group_permissions OWNER TO postgres;

--
-- Name: auth_group_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_group_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_group_permissions_id_seq OWNER TO postgres;

--
-- Name: auth_group_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_group_permissions_id_seq OWNED BY auth_group_permissions.id;


--
-- Name: auth_permission; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_permission (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    content_type_id integer NOT NULL,
    codename character varying(100) NOT NULL
);


ALTER TABLE auth_permission OWNER TO postgres;

--
-- Name: auth_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_permission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_permission_id_seq OWNER TO postgres;

--
-- Name: auth_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_permission_id_seq OWNED BY auth_permission.id;


--
-- Name: auth_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_user (
    id integer NOT NULL,
    password character varying(128) NOT NULL,
    last_login timestamp with time zone,
    is_superuser boolean NOT NULL,
    username character varying(30) NOT NULL,
    first_name character varying(30) NOT NULL,
    last_name character varying(30) NOT NULL,
    email character varying(254) NOT NULL,
    is_staff boolean NOT NULL,
    is_active boolean NOT NULL,
    date_joined timestamp with time zone NOT NULL
);


ALTER TABLE auth_user OWNER TO postgres;

--
-- Name: auth_user_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_user_groups (
    id integer NOT NULL,
    user_id integer NOT NULL,
    group_id integer NOT NULL
);


ALTER TABLE auth_user_groups OWNER TO postgres;

--
-- Name: auth_user_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_user_groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_user_groups_id_seq OWNER TO postgres;

--
-- Name: auth_user_groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_user_groups_id_seq OWNED BY auth_user_groups.id;


--
-- Name: auth_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_user_id_seq OWNER TO postgres;

--
-- Name: auth_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_user_id_seq OWNED BY auth_user.id;


--
-- Name: auth_user_user_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_user_user_permissions (
    id integer NOT NULL,
    user_id integer NOT NULL,
    permission_id integer NOT NULL
);


ALTER TABLE auth_user_user_permissions OWNER TO postgres;

--
-- Name: auth_user_user_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_user_user_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE auth_user_user_permissions_id_seq OWNER TO postgres;

--
-- Name: auth_user_user_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE auth_user_user_permissions_id_seq OWNED BY auth_user_user_permissions.id;


--
-- Name: django_admin_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE django_admin_log (
    id integer NOT NULL,
    action_time timestamp with time zone NOT NULL,
    object_id text,
    object_repr character varying(200) NOT NULL,
    action_flag smallint NOT NULL,
    change_message text NOT NULL,
    content_type_id integer,
    user_id integer NOT NULL,
    CONSTRAINT django_admin_log_action_flag_check CHECK ((action_flag >= 0))
);


ALTER TABLE django_admin_log OWNER TO postgres;

--
-- Name: django_admin_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE django_admin_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE django_admin_log_id_seq OWNER TO postgres;

--
-- Name: django_admin_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE django_admin_log_id_seq OWNED BY django_admin_log.id;


--
-- Name: django_content_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE django_content_type (
    id integer NOT NULL,
    app_label character varying(100) NOT NULL,
    model character varying(100) NOT NULL
);


ALTER TABLE django_content_type OWNER TO postgres;

--
-- Name: django_content_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE django_content_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE django_content_type_id_seq OWNER TO postgres;

--
-- Name: django_content_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE django_content_type_id_seq OWNED BY django_content_type.id;


--
-- Name: django_migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE django_migrations (
    id integer NOT NULL,
    app character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    applied timestamp with time zone NOT NULL
);


ALTER TABLE django_migrations OWNER TO postgres;

--
-- Name: django_migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE django_migrations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE django_migrations_id_seq OWNER TO postgres;

--
-- Name: django_migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE django_migrations_id_seq OWNED BY django_migrations.id;


--
-- Name: django_session; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE django_session (
    session_key character varying(40) NOT NULL,
    session_data text NOT NULL,
    expire_date timestamp with time zone NOT NULL
);


ALTER TABLE django_session OWNER TO postgres;

--
-- Name: timetracker_billingperiod; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE timetracker_billingperiod (
    id integer NOT NULL,
    time_from timestamp with time zone NOT NULL,
    time_until timestamp with time zone NOT NULL,
    closed boolean NOT NULL,
    identifier character varying(10),
    project_id integer NOT NULL
);


ALTER TABLE timetracker_billingperiod OWNER TO postgres;

--
-- Name: timetracker_billingperiod_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE timetracker_billingperiod_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE timetracker_billingperiod_id_seq OWNER TO postgres;

--
-- Name: timetracker_billingperiod_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE timetracker_billingperiod_id_seq OWNED BY timetracker_billingperiod.id;


--
-- Name: timetracker_project; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE timetracker_project (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    billing_id character varying(50) NOT NULL,
    active boolean NOT NULL,
    time_created timestamp with time zone NOT NULL,
    last_modified timestamp with time zone NOT NULL
);


ALTER TABLE timetracker_project OWNER TO postgres;

--
-- Name: timetracker_project_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE timetracker_project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE timetracker_project_id_seq OWNER TO postgres;

--
-- Name: timetracker_project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE timetracker_project_id_seq OWNED BY timetracker_project.id;


--
-- Name: timetracker_project_members; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE timetracker_project_members (
    id integer NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE timetracker_project_members OWNER TO postgres;

--
-- Name: timetracker_project_members_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE timetracker_project_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE timetracker_project_members_id_seq OWNER TO postgres;

--
-- Name: timetracker_project_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE timetracker_project_members_id_seq OWNED BY timetracker_project_members.id;


--
-- Name: timetracker_worklog; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE timetracker_worklog (
    id integer NOT NULL,
    time_from timestamp with time zone NOT NULL,
    time_until timestamp with time zone NOT NULL,
    description text NOT NULL,
    office_hour_rate boolean NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    billing_period_id integer
);


ALTER TABLE timetracker_worklog OWNER TO postgres;

--
-- Name: timetracker_worklog_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE timetracker_worklog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE timetracker_worklog_id_seq OWNER TO postgres;

--
-- Name: timetracker_worklog_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE timetracker_worklog_id_seq OWNED BY timetracker_worklog.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group ALTER COLUMN id SET DEFAULT nextval('auth_group_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group_permissions ALTER COLUMN id SET DEFAULT nextval('auth_group_permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_permission ALTER COLUMN id SET DEFAULT nextval('auth_permission_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user ALTER COLUMN id SET DEFAULT nextval('auth_user_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_groups ALTER COLUMN id SET DEFAULT nextval('auth_user_groups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_user_permissions ALTER COLUMN id SET DEFAULT nextval('auth_user_user_permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_admin_log ALTER COLUMN id SET DEFAULT nextval('django_admin_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_content_type ALTER COLUMN id SET DEFAULT nextval('django_content_type_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_migrations ALTER COLUMN id SET DEFAULT nextval('django_migrations_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_billingperiod ALTER COLUMN id SET DEFAULT nextval('timetracker_billingperiod_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project ALTER COLUMN id SET DEFAULT nextval('timetracker_project_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project_members ALTER COLUMN id SET DEFAULT nextval('timetracker_project_members_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_worklog ALTER COLUMN id SET DEFAULT nextval('timetracker_worklog_id_seq'::regclass);


--
-- Data for Name: auth_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_group (id, name) VALUES (2, 'Kunden');
INSERT INTO auth_group (id, name) VALUES (3, 'Projektleiter');
INSERT INTO auth_group (id, name) VALUES (1, 'Mitarbeiter');
INSERT INTO auth_group (id, name) VALUES (4, 'Billing');


--
-- Name: auth_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_group_id_seq', 4, true);


--
-- Data for Name: auth_group_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (1, 4, 8);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (2, 4, 7);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (3, 3, 1);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (4, 3, 2);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (5, 3, 3);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (6, 3, 4);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (7, 3, 5);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (8, 3, 6);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (9, 1, 4);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (10, 1, 5);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (11, 1, 6);
INSERT INTO auth_group_permissions (id, group_id, permission_id) VALUES (12, 4, 9);


--
-- Name: auth_group_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_group_permissions_id_seq', 12, true);


--
-- Data for Name: auth_permission; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (1, 'Can add project', 1, 'add_project');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (2, 'Can change project', 1, 'change_project');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (3, 'Can delete project', 1, 'delete_project');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (4, 'Can add work log', 2, 'add_worklog');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (5, 'Can change work log', 2, 'change_worklog');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (6, 'Can delete work log', 2, 'delete_worklog');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (7, 'Can add billing period', 3, 'add_billingperiod');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (8, 'Can change billing period', 3, 'change_billingperiod');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (9, 'Can delete billing period', 3, 'delete_billingperiod');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (10, 'Can add log entry', 4, 'add_logentry');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (11, 'Can change log entry', 4, 'change_logentry');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (12, 'Can delete log entry', 4, 'delete_logentry');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (13, 'Can add permission', 5, 'add_permission');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (14, 'Can change permission', 5, 'change_permission');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (15, 'Can delete permission', 5, 'delete_permission');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (16, 'Can add group', 6, 'add_group');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (17, 'Can change group', 6, 'change_group');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (18, 'Can delete group', 6, 'delete_group');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (19, 'Can add user', 7, 'add_user');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (20, 'Can change user', 7, 'change_user');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (21, 'Can delete user', 7, 'delete_user');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (22, 'Can add content type', 8, 'add_contenttype');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (23, 'Can change content type', 8, 'change_contenttype');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (24, 'Can delete content type', 8, 'delete_contenttype');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (25, 'Can add session', 9, 'add_session');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (26, 'Can change session', 9, 'change_session');
INSERT INTO auth_permission (id, name, content_type_id, codename) VALUES (27, 'Can delete session', 9, 'delete_session');


--
-- Name: auth_permission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_permission_id_seq', 27, true);


--
-- Data for Name: auth_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_user (id, password, last_login, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined) VALUES (5, 'pbkdf2_sha256$24000$LakQQ2OOTO1v$dmUgz8V7zcpaoBSA3MV76J5a4rzrszF0NpxGx6HRBbE=', NULL, false, 'test-billing', 'Joana', 'Gröschel', 'jg@billing.com', true, true, '2016-03-07 15:58:49+01');
INSERT INTO auth_user (id, password, last_login, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined) VALUES (3, 'pbkdf2_sha256$24000$PXogIZpE4gaK$F/P/L5SRrbb6taOGEr4w6DhxjMzNAj1jEWTPyAUn8WU=', NULL, false, 'test-kunde', 'Niels', 'Buchberger', 'ng@kunde.com', false, true, '2016-03-07 15:57:52+01');
INSERT INTO auth_user (id, password, last_login, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined) VALUES (2, 'pbkdf2_sha256$24000$vtn5APnhirmB$/jzJXYvm78X8/FCOMhGUmcCy0iWhtk0L1hcBWN1AYZc=', NULL, false, 'test-mitarbeiter', 'Claus', 'Wernke', 'cw@mitarbeiter.com', false, true, '2016-03-07 15:57:23+01');
INSERT INTO auth_user (id, password, last_login, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined) VALUES (4, 'pbkdf2_sha256$24000$meyCtGKrS5Ai$vkMfMzB/yGFKplmXujgtfl3OGR27AwOQmP+YeRP6lbw=', NULL, false, 'test-projektleiter', 'Sascha', 'Weiher', 'sw@projektleiter.com', true, true, '2016-03-07 15:58:09+01');
INSERT INTO auth_user (id, password, last_login, is_superuser, username, first_name, last_name, email, is_staff, is_active, date_joined) VALUES (1, 'pbkdf2_sha256$24000$M8ecC8zfqLmJ$l6cIa/Od+m56VMm9hJbdPNhTXZykPVbUGGTPx7/VRE4=', '2016-03-07 15:54:45+01', true, 'admin', 'Admin', 'Istrator', 'admin@example.com', true, true, '2016-03-07 15:54:22+01');


--
-- Data for Name: auth_user_groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_user_groups (id, user_id, group_id) VALUES (1, 2, 1);
INSERT INTO auth_user_groups (id, user_id, group_id) VALUES (2, 3, 2);
INSERT INTO auth_user_groups (id, user_id, group_id) VALUES (3, 4, 3);
INSERT INTO auth_user_groups (id, user_id, group_id) VALUES (4, 5, 4);


--
-- Name: auth_user_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_user_groups_id_seq', 4, true);


--
-- Name: auth_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_user_id_seq', 5, true);


--
-- Data for Name: auth_user_user_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_user_user_permissions (id, user_id, permission_id) VALUES (1, 4, 19);
INSERT INTO auth_user_user_permissions (id, user_id, permission_id) VALUES (2, 4, 20);
INSERT INTO auth_user_user_permissions (id, user_id, permission_id) VALUES (3, 4, 21);


--
-- Name: auth_user_user_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_user_user_permissions_id_seq', 3, true);


--
-- Data for Name: django_admin_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (1, '2016-03-07 15:54:56.300644+01', '1', 'Mitarbeiter', 1, 'Hinzugefügt.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (2, '2016-03-07 15:55:03.567461+01', '2', 'Kunden', 1, 'Hinzugefügt.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (3, '2016-03-07 15:55:12.909732+01', '3', 'Projektleiter', 1, 'Hinzugefügt.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (4, '2016-03-07 15:55:19.286428+01', '4', 'Billing', 1, 'Hinzugefügt.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (5, '2016-03-07 15:55:46.352427+01', '4', 'Billing', 2, 'permissions geändert.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (6, '2016-03-07 15:56:20.226365+01', '3', 'Projektleiter', 2, 'permissions geändert.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (7, '2016-03-07 15:56:28.990235+01', '1', 'Mitarbeiter', 2, 'permissions geändert.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (8, '2016-03-07 15:56:52.876303+01', '4', 'Billing', 2, 'permissions geändert.', 6, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (9, '2016-03-07 15:57:23.335998+01', '2', 'test-mitarbeiter', 1, 'Hinzugefügt.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (10, '2016-03-07 15:57:44.179275+01', '2', 'test-mitarbeiter', 2, 'groups geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (11, '2016-03-07 15:57:52.235262+01', '3', 'test-kunde', 1, 'Hinzugefügt.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (12, '2016-03-07 15:57:59.05676+01', '3', 'test-kunde', 2, 'groups geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (13, '2016-03-07 15:58:10.0047+01', '4', 'test-projektleiter', 1, 'Hinzugefügt.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (14, '2016-03-07 15:58:35.229359+01', '4', 'test-projektleiter', 2, 'is_staff, groups und user_permissions geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (15, '2016-03-07 15:58:49.513774+01', '5', 'test-billing', 1, 'Hinzugefügt.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (16, '2016-03-07 15:58:55.398695+01', '5', 'test-billing', 2, 'is_staff und groups geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (17, '2016-03-07 15:59:56.823546+01', '5', 'test-billing', 2, 'first_name, last_name und email geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (18, '2016-03-07 16:00:15.718568+01', '3', 'test-kunde', 2, 'first_name, last_name und email geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (19, '2016-03-07 16:00:37.583668+01', '2', 'test-mitarbeiter', 2, 'first_name, last_name und email geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (20, '2016-03-07 16:00:59.426815+01', '4', 'test-projektleiter', 2, 'first_name, last_name und email geändert.', 7, 1);
INSERT INTO django_admin_log (id, action_time, object_id, object_repr, action_flag, change_message, content_type_id, user_id) VALUES (21, '2016-03-07 16:01:13.440369+01', '1', 'admin', 2, 'first_name und last_name geändert.', 7, 1);


--
-- Name: django_admin_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('django_admin_log_id_seq', 21, true);


--
-- Data for Name: django_content_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO django_content_type (id, app_label, model) VALUES (1, 'timetracker', 'project');
INSERT INTO django_content_type (id, app_label, model) VALUES (2, 'timetracker', 'worklog');
INSERT INTO django_content_type (id, app_label, model) VALUES (3, 'timetracker', 'billingperiod');
INSERT INTO django_content_type (id, app_label, model) VALUES (4, 'admin', 'logentry');
INSERT INTO django_content_type (id, app_label, model) VALUES (5, 'auth', 'permission');
INSERT INTO django_content_type (id, app_label, model) VALUES (6, 'auth', 'group');
INSERT INTO django_content_type (id, app_label, model) VALUES (7, 'auth', 'user');
INSERT INTO django_content_type (id, app_label, model) VALUES (8, 'contenttypes', 'contenttype');
INSERT INTO django_content_type (id, app_label, model) VALUES (9, 'sessions', 'session');


--
-- Name: django_content_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('django_content_type_id_seq', 9, true);


--
-- Data for Name: django_migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO django_migrations (id, app, name, applied) VALUES (1, 'contenttypes', '0001_initial', '2016-03-07 15:53:25.193162+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (2, 'auth', '0001_initial', '2016-03-07 15:53:25.273474+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (3, 'admin', '0001_initial', '2016-03-07 15:53:25.30131+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (4, 'admin', '0002_logentry_remove_auto_add', '2016-03-07 15:53:25.319456+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (5, 'contenttypes', '0002_remove_content_type_name', '2016-03-07 15:53:25.36513+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (6, 'auth', '0002_alter_permission_name_max_length', '2016-03-07 15:53:25.38065+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (7, 'auth', '0003_alter_user_email_max_length', '2016-03-07 15:53:25.398969+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (8, 'auth', '0004_alter_user_username_opts', '2016-03-07 15:53:25.41613+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (9, 'auth', '0005_alter_user_last_login_null', '2016-03-07 15:53:25.434578+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (10, 'auth', '0006_require_contenttypes_0002', '2016-03-07 15:53:25.437224+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (11, 'auth', '0007_alter_validators_add_error_messages', '2016-03-07 15:53:25.45391+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (12, 'sessions', '0001_initial', '2016-03-07 15:53:25.46445+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (13, 'timetracker', '0001_initial', '2016-03-07 15:53:25.530527+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (14, 'timetracker', '0002_billingperiod', '2016-03-07 15:53:25.560994+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (15, 'timetracker', '0003_local_datetime_fields', '2016-03-07 15:53:25.677892+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (16, 'timetracker', '0004_worklog_billing_period', '2016-03-07 15:53:25.703913+01');
INSERT INTO django_migrations (id, app, name, applied) VALUES (17, 'timetracker', '0005_billing_period_identifier_index', '2016-03-07 15:53:25.726466+01');


--
-- Name: django_migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('django_migrations_id_seq', 17, true);


--
-- Data for Name: django_session; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO django_session (session_key, session_data, expire_date) VALUES ('lu5upc331w1hp9t6ofu5xt5asbd4k5mv', 'ODMxNDFiOGMzYTJmNGFkM2ZhZmVkOWEyMmZhMjJmMDgzNTg4ZWJlMTp7Il9hdXRoX3VzZXJfaGFzaCI6ImQxODIzNDYyYWU2ZjNiZmVmOGJkZTFlMmJiYTE3ZDkyOGQ3NDAwMTAiLCJfYXV0aF91c2VyX2JhY2tlbmQiOiJkamFuZ28uY29udHJpYi5hdXRoLmJhY2tlbmRzLk1vZGVsQmFja2VuZCIsIl9hdXRoX3VzZXJfaWQiOiIxIn0=', '2016-03-21 15:54:45.280447+01');


--
-- Data for Name: timetracker_billingperiod; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: timetracker_billingperiod_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('timetracker_billingperiod_id_seq', 1, false);


--
-- Data for Name: timetracker_project; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: timetracker_project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('timetracker_project_id_seq', 1, false);


--
-- Data for Name: timetracker_project_members; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: timetracker_project_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('timetracker_project_members_id_seq', 1, false);


--
-- Data for Name: timetracker_worklog; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: timetracker_worklog_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('timetracker_worklog_id_seq', 1, false);


--
-- Name: auth_group_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group
    ADD CONSTRAINT auth_group_name_key UNIQUE (name);


--
-- Name: auth_group_permissions_group_id_0cd325b0_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group_permissions
    ADD CONSTRAINT auth_group_permissions_group_id_0cd325b0_uniq UNIQUE (group_id, permission_id);


--
-- Name: auth_group_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group_permissions
    ADD CONSTRAINT auth_group_permissions_pkey PRIMARY KEY (id);


--
-- Name: auth_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group
    ADD CONSTRAINT auth_group_pkey PRIMARY KEY (id);


--
-- Name: auth_permission_content_type_id_01ab375a_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_permission
    ADD CONSTRAINT auth_permission_content_type_id_01ab375a_uniq UNIQUE (content_type_id, codename);


--
-- Name: auth_permission_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_permission
    ADD CONSTRAINT auth_permission_pkey PRIMARY KEY (id);


--
-- Name: auth_user_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_groups
    ADD CONSTRAINT auth_user_groups_pkey PRIMARY KEY (id);


--
-- Name: auth_user_groups_user_id_94350c0c_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_groups
    ADD CONSTRAINT auth_user_groups_user_id_94350c0c_uniq UNIQUE (user_id, group_id);


--
-- Name: auth_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user
    ADD CONSTRAINT auth_user_pkey PRIMARY KEY (id);


--
-- Name: auth_user_user_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_user_permissions
    ADD CONSTRAINT auth_user_user_permissions_pkey PRIMARY KEY (id);


--
-- Name: auth_user_user_permissions_user_id_14a6b632_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_user_permissions
    ADD CONSTRAINT auth_user_user_permissions_user_id_14a6b632_uniq UNIQUE (user_id, permission_id);


--
-- Name: auth_user_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user
    ADD CONSTRAINT auth_user_username_key UNIQUE (username);


--
-- Name: django_admin_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_admin_log
    ADD CONSTRAINT django_admin_log_pkey PRIMARY KEY (id);


--
-- Name: django_content_type_app_label_76bd3d3b_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_content_type
    ADD CONSTRAINT django_content_type_app_label_76bd3d3b_uniq UNIQUE (app_label, model);


--
-- Name: django_content_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_content_type
    ADD CONSTRAINT django_content_type_pkey PRIMARY KEY (id);


--
-- Name: django_migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_migrations
    ADD CONSTRAINT django_migrations_pkey PRIMARY KEY (id);


--
-- Name: django_session_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_session
    ADD CONSTRAINT django_session_pkey PRIMARY KEY (session_key);


--
-- Name: timetracker_billingperiod_identifier_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_billingperiod
    ADD CONSTRAINT timetracker_billingperiod_identifier_key UNIQUE (identifier);


--
-- Name: timetracker_billingperiod_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_billingperiod
    ADD CONSTRAINT timetracker_billingperiod_pkey PRIMARY KEY (id);


--
-- Name: timetracker_project_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project_members
    ADD CONSTRAINT timetracker_project_members_pkey PRIMARY KEY (id);


--
-- Name: timetracker_project_members_project_id_786577ed_uniq; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project_members
    ADD CONSTRAINT timetracker_project_members_project_id_786577ed_uniq UNIQUE (project_id, user_id);


--
-- Name: timetracker_project_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project
    ADD CONSTRAINT timetracker_project_name_key UNIQUE (name);


--
-- Name: timetracker_project_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project
    ADD CONSTRAINT timetracker_project_pkey PRIMARY KEY (id);


--
-- Name: timetracker_worklog_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_worklog
    ADD CONSTRAINT timetracker_worklog_pkey PRIMARY KEY (id);


--
-- Name: auth_group_name_a6ea08ec_like; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_group_name_a6ea08ec_like ON auth_group USING btree (name varchar_pattern_ops);


--
-- Name: auth_group_permissions_0e939a4f; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_group_permissions_0e939a4f ON auth_group_permissions USING btree (group_id);


--
-- Name: auth_group_permissions_8373b171; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_group_permissions_8373b171 ON auth_group_permissions USING btree (permission_id);


--
-- Name: auth_permission_417f1b1c; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_permission_417f1b1c ON auth_permission USING btree (content_type_id);


--
-- Name: auth_user_groups_0e939a4f; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_user_groups_0e939a4f ON auth_user_groups USING btree (group_id);


--
-- Name: auth_user_groups_e8701ad4; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_user_groups_e8701ad4 ON auth_user_groups USING btree (user_id);


--
-- Name: auth_user_user_permissions_8373b171; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_user_user_permissions_8373b171 ON auth_user_user_permissions USING btree (permission_id);


--
-- Name: auth_user_user_permissions_e8701ad4; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_user_user_permissions_e8701ad4 ON auth_user_user_permissions USING btree (user_id);


--
-- Name: auth_user_username_6821ab7c_like; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auth_user_username_6821ab7c_like ON auth_user USING btree (username varchar_pattern_ops);


--
-- Name: django_admin_log_417f1b1c; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX django_admin_log_417f1b1c ON django_admin_log USING btree (content_type_id);


--
-- Name: django_admin_log_e8701ad4; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX django_admin_log_e8701ad4 ON django_admin_log USING btree (user_id);


--
-- Name: django_session_de54fa62; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX django_session_de54fa62 ON django_session USING btree (expire_date);


--
-- Name: django_session_session_key_c0390e0f_like; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX django_session_session_key_c0390e0f_like ON django_session USING btree (session_key varchar_pattern_ops);


--
-- Name: timetracker_billingperiod_b098ad43; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_billingperiod_b098ad43 ON timetracker_billingperiod USING btree (project_id);


--
-- Name: timetracker_billingperiod_identifier_8432361f_like; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_billingperiod_identifier_8432361f_like ON timetracker_billingperiod USING btree (identifier varchar_pattern_ops);


--
-- Name: timetracker_project_members_b098ad43; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_project_members_b098ad43 ON timetracker_project_members USING btree (project_id);


--
-- Name: timetracker_project_members_e8701ad4; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_project_members_e8701ad4 ON timetracker_project_members USING btree (user_id);


--
-- Name: timetracker_project_name_daf0136a_like; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_project_name_daf0136a_like ON timetracker_project USING btree (name varchar_pattern_ops);


--
-- Name: timetracker_worklog_3c1a7956; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_worklog_3c1a7956 ON timetracker_worklog USING btree (billing_period_id);


--
-- Name: timetracker_worklog_b098ad43; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_worklog_b098ad43 ON timetracker_worklog USING btree (project_id);


--
-- Name: timetracker_worklog_e8701ad4; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX timetracker_worklog_e8701ad4 ON timetracker_worklog USING btree (user_id);


--
-- Name: auth_group_permiss_permission_id_84c5c92e_fk_auth_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group_permissions
    ADD CONSTRAINT auth_group_permiss_permission_id_84c5c92e_fk_auth_permission_id FOREIGN KEY (permission_id) REFERENCES auth_permission(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_group_permissions_group_id_b120cbf9_fk_auth_group_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_group_permissions
    ADD CONSTRAINT auth_group_permissions_group_id_b120cbf9_fk_auth_group_id FOREIGN KEY (group_id) REFERENCES auth_group(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_permiss_content_type_id_2f476e4b_fk_django_content_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_permission
    ADD CONSTRAINT auth_permiss_content_type_id_2f476e4b_fk_django_content_type_id FOREIGN KEY (content_type_id) REFERENCES django_content_type(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_user_groups_group_id_97559544_fk_auth_group_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_groups
    ADD CONSTRAINT auth_user_groups_group_id_97559544_fk_auth_group_id FOREIGN KEY (group_id) REFERENCES auth_group(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_user_groups_user_id_6a12ed8b_fk_auth_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_groups
    ADD CONSTRAINT auth_user_groups_user_id_6a12ed8b_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES auth_user(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_user_user_per_permission_id_1fbb5f2c_fk_auth_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_user_permissions
    ADD CONSTRAINT auth_user_user_per_permission_id_1fbb5f2c_fk_auth_permission_id FOREIGN KEY (permission_id) REFERENCES auth_permission(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: auth_user_user_permissions_user_id_a95ead1b_fk_auth_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_user_user_permissions
    ADD CONSTRAINT auth_user_user_permissions_user_id_a95ead1b_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES auth_user(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: django_admin_content_type_id_c4bce8eb_fk_django_content_type_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_admin_log
    ADD CONSTRAINT django_admin_content_type_id_c4bce8eb_fk_django_content_type_id FOREIGN KEY (content_type_id) REFERENCES django_content_type(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: django_admin_log_user_id_c564eba6_fk_auth_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY django_admin_log
    ADD CONSTRAINT django_admin_log_user_id_c564eba6_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES auth_user(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: time_billing_period_id_c4b944d1_fk_timetracker_billingperiod_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_worklog
    ADD CONSTRAINT time_billing_period_id_c4b944d1_fk_timetracker_billingperiod_id FOREIGN KEY (billing_period_id) REFERENCES timetracker_billingperiod(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: timetracker_billi_project_id_8dd7b2e2_fk_timetracker_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_billingperiod
    ADD CONSTRAINT timetracker_billi_project_id_8dd7b2e2_fk_timetracker_project_id FOREIGN KEY (project_id) REFERENCES timetracker_project(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: timetracker_proje_project_id_85632ed8_fk_timetracker_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project_members
    ADD CONSTRAINT timetracker_proje_project_id_85632ed8_fk_timetracker_project_id FOREIGN KEY (project_id) REFERENCES timetracker_project(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: timetracker_project_members_user_id_62b48c3e_fk_auth_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_project_members
    ADD CONSTRAINT timetracker_project_members_user_id_62b48c3e_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES auth_user(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: timetracker_workl_project_id_69fa76d5_fk_timetracker_project_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_worklog
    ADD CONSTRAINT timetracker_workl_project_id_69fa76d5_fk_timetracker_project_id FOREIGN KEY (project_id) REFERENCES timetracker_project(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: timetracker_worklog_user_id_c74c8c68_fk_auth_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY timetracker_worklog
    ADD CONSTRAINT timetracker_worklog_user_id_c74c8c68_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES auth_user(id) DEFERRABLE INITIALLY DEFERRED;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

