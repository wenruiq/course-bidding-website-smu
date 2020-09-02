--
-- Insert mandatory data into BIOS
--
USE `bios`;

INSERT INTO admin (userid, password) VALUES ('admin', 'Best@dmin1337!');
INSERT INTO student (userid, password, name, school, edollar) VALUES ('amy', '123', 'Amy Huang', 'SMOO', '999');
INSERT INTO round (roundnum, status) VALUES ('round 1', 'not started');
INSERT INTO round (roundnum, status) VALUES ('round 2', 'not started');
