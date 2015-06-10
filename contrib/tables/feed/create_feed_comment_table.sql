CREATE TABLE `FeedComment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `authorID` varchar(64) NOT NULL,
  `payload` mediumtext CHARACTER SET utf8mb4 NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feed_comment_fk_fid_idx` (`fid`),
  CONSTRAINT `fk_feedcomment_fid` FOREIGN KEY (`fid`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
