-- Database schema for Avantlink example designed for PostgreSQL 9.3+

CREATE DATABASE avantlink_task WITH OWNER=postgres TEMPLATE=template1 ENCODING=UTF8;

CREATE ROLE webuser LOGIN PASSWORD 'w36U53r!';
CREATE GROUP application LOGIN;
GRANT application TO webuser;

CREATE TABLE task_category (
    category_id                     SERIAL NOT NULL,
    category_parent_id              INT DEFAULT NULL REFERENCES task_category(category_id) ON DELETE CASCADE ON UPDATE CASCADE,
    category_name                   VARCHAR(64),
    category_desc                   VARCHAR(255),
    category_depth                  SMALLINT NOT NULL DEFAULT 1,
    PRIMARY KEY (category_id)
);

CREATE TABLE task (
    task_id                         SERIAL NOT NULL,
    parent_task_id                  INT DEFAULT NULL REFERENCES task(task_id) ON DELETE CASCADE ON UPDATE CASCADE,
    category_id                     INT DEFAULT NULL REFERENCES task_category(category_id) ON DELETE SET NULL ON UPDATE CASCADE,
    task_name                       VARCHAR(64),
    task_desc                       VARCHAR(255),
    importance                      SMALLINT NOT NULL DEFAULT 0,
    alert_min                       SMALLINT DEFAULT NULL,
    alarm_set                       BOOLEAN NOT NULL DEFAULT false,
    date_due                        DATE DEFAULT NULL,
    time_due                        TIME WITHOUT TIME ZONE DEFAULT NULL,
    task_completed                  TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    task_created                    TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    task_updated                    TIMESTAMP(0) WITHOUT TIME ZONE,
    CHECK (importance >= 0 AND importance <= 10),
    PRIMARY KEY (task_id)
);
CREATE RULE get_pkey_on_insert AS ON INSERT TO task DO SELECT currval('task_task_id_seq'::text) AS task_id;

CREATE TABLE users (
    user_id                         SERIAL NOT NULL,
    user_email                      VARCHAR(255) NOT NULL,
    user_passwd                     VARCHAR(128) NOT NULL,
    PRIMARY KEY (user_id)
);

GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE task_category TO webuser;
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE task TO webuser;
GRANT SELECT, UPDATE ON SEQUENCE task_category_category_id_seq TO webuser;
GRANT SELECT, UPDATE ON SEQUENCE task_task_id_seq TO webuser;

INSERT INTO task_category (category_name, category_desc, category_depth) VALUES
    ('home', 'Use for tasks related to time spent at home', 0),
    ('personal', 'Use for tasks of a personal nature', 0),
    ('work', 'Use for tasks that are work related', 0),
    ('business', 'Use for tasks of a business nature', 0);

INSERT INTO task (category_id, task_name, task_desc, importance, alert_min, alarm_set, date_due, time_due, task_completed) VALUES
    (1, 'Walk dog', 'Daily dog walk with my furry friend', 5, 15, true, '2016-02-19', '23:30:00', null),
    (2, 'Pull transmission', 'Remove transmission from Jeep for last fix attempt', 3, 0, false, null, null, null),
    (3, 'Demo Code', 'Avantlink sample project code base due for delivery', 9, 15, true, '2016-02-19', '23:30:00', null),
    (1, 'Pay Bills', 'Pay second half of months bills', 7, 15, true, '2016-02-20', '23:30:00', null);
