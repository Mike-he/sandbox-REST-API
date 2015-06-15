CREATE TABLE `FeedAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `content` text NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` text,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feed_fk_fid_idx` (`fid`),
  CONSTRAINT `fk_feedattachment_fid` FOREIGN KEY (`fid`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
