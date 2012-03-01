DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS passengers;
DROP TABLE IF EXISTS taxis;

-- Alter permissions of postgis tables

ALTER TABLE spatial_ref_sys OWNER TO taxirider;
ALTER TABLE geometry_columns OWNER TO taxirider;

-- WGS84 (google maps projection) = 4326.

CREATE TABLE passengers (
	id SERIAL CONSTRAINT passengers_pkey PRIMARY KEY,
	name VARCHAR(32) NOT NULL );
SELECT AddGeometryColumn( 'passengers', 'position', 4326, 'POINT', 2 );

ALTER TABLE passengers OWNER TO taxirider;

CREATE TABLE taxis (
	id SERIAL CONSTRAINT taxis_pkey PRIMARY KEY,
	name VARCHAR(64) NOT NULL,
	status BOOL NOT NULL DEFAULT TRUE);
SELECT AddGeometryColumn( 'taxis', 'position', 4326, 'POINT', 2 );

ALTER TABLE taxis OWNER TO taxirider;

-- // possible statuses: open=0, accepted=1, rejected=2, cancelled=3, active=4, closed=5

CREATE TABLE requests (
		id SERIAL CONSTRAINT requests_pkey PRIMARY KEY,
		passenger_id INTEGER references passengers(id),
		taxi_id INTEGER references taxis(id),
		status SMALLINT NOT NULL DEFAULT 0,
		passenger_boarded BOOL DEFAULT NULL,
		passenger_picked BOOL DEFAULT NULL,
		created TIMESTAMP DEFAULT NULL,
		modified TIMESTAMP DEFAULT NULL,
		closed TIMESTAMP DEFAULT NULL,
		review TEXT DEFAULT NULL,
		anonymous_review BOOL DEFAULT FALSE );
SELECT AddGeometryColumn( 'requests', 'start_position', 4326, 'POINT', 2 );
SELECT AddGeometryColumn( 'requests', 'end_position', 4326, 'POINT', 2 );

ALTER TABLE requests OWNER TO taxirider;

DROP TRIGGER IF EXISTS request_update ON requests;

CREATE OR REPLACE FUNCTION close_request() RETURNS TRIGGER AS $close_request$
    BEGIN
	-- When passenger boards taxi, and taxi picks passenger: request becomes ACTIVE
        IF NEW.passenger_boarded = TRUE AND NEW.passenger_picked = TRUE THEN
            NEW.status = 4;
            NEW.modified := current_timestamp;
        END IF;
	-- When passenger leaves taxi, and taxi drops passenger: request becomes CLOSED
        IF NEW.passenger_boarded = FALSE AND NEW.passenger_picked = FALSE THEN
            NEW.status = 5;
            NEW.modified := current_timestamp;
        END IF;
        RETURN NEW;
    END;
$close_request$ LANGUAGE plpgsql;

-- Creation timestamp already managed by CakePHP --
-- DROP TRIGGER IF EXISTS request_open ON requests;
-- CREATE OR REPLACE FUNCTION open_request() RETURNS trigger AS $open_request$
--     BEGIN
--         -- Time stamp request
--         NEW.created_ts := current_timestamp;
--         RETURN NEW;
--     END;
-- $open_request$ LANGUAGE plpgsql;

-- Already manage by CakePHP --
-- CREATE TRIGGER request_open
-- BEFORE INSERT ON requests
--     FOR EACH ROW EXECUTE PROCEDURE open_request();

CREATE TRIGGER request_update
BEFORE UPDATE ON requests
    FOR EACH ROW EXECUTE PROCEDURE close_request();
