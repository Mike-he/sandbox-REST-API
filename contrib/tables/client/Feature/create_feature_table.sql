CREATE TABLE `Feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `ready` boolean DEFAULT FALSE NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO Feature(`name`,`ready`) VALUES('food', false);
INSERT INTO Feature(`name`,`ready`) VALUES('print', false);
INSERT INTO Feature(`name`,`ready`) VALUES('coffee', false);