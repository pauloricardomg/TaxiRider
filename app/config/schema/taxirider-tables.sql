DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS passengers;
DROP TABLE IF EXISTS taxis;

CREATE TABLE passengers (
	id SERIAL CONSTRAINT passengers_pkey PRIMARY KEY,
	name VARCHAR(32) NOT NULL );
SELECT AddGeometryColumn( 'passengers', 'position', -1, 'POINT', 2 );

CREATE TABLE taxis (
	id SERIAL CONSTRAINT taxis_pkey PRIMARY KEY,
	name VARCHAR(64) NOT NULL,
	status BOOL NOT NULL DEFAULT TRUE);
SELECT AddGeometryColumn( 'taxis', 'position', -1, 'POINT', 2 );

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
SELECT AddGeometryColumn( 'requests', 'start_position', -1, 'POINT', 2 );
SELECT AddGeometryColumn( 'requests', 'end_position', -1, 'POINT', 2 );


DROP TRIGGER IF EXISTS request_update ON requests;

CREATE OR REPLACE FUNCTION close_request() RETURNS TRIGGER AS $close_request$
    BEGIN
        IF NEW.passenger_boarded = FALSE AND NEW.passenger_picked = FALSE THEN
            NEW.status = 5;
            NEW.closed := current_timestamp;
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
