CREATE TABLE `EventFormOption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formId` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventFormOption_formId_idx` (`formId`),
  CONSTRAINT `fk_EventFormOption_formId` FOREIGN KEY (`formId`) REFERENCES `EventForm` (`id`) ON DELETE CASCADE
);