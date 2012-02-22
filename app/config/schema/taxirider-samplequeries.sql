DELETE FROM requests;
DELETE FROM passengers;
DELETE FROM taxis;

INSERT INTO passengers (nome, position) VALUES ('joao', ST_GeomFromText('POINT(0 0)'));
INSERT INTO taxis (nome, position) VALUES ('marcelo', ST_GeomFromText('POINT(1 1)'));

INSERT INTO requests (id_passenger, id_taxi) VALUES (1, 1) RETURNING id;

UPDATE requests SET status=2 WHERE id=1;
UPDATE requests SET passenger_boarded=FALSE WHERE id=1;
UPDATE requests SET passenger_picked=FALSE WHERE id=1;
select * from requests;