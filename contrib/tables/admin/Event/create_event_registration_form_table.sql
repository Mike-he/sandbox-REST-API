CREATE TABLE `EventRegistrationForm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registrationId` int(11) NOT NULL,
  `formId` int(11) NOT NULL,
  `userInput` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventRegistrationForm_registrationId_idx` (`registrationId`),
  KEY `fk_EventRegistrationForm_formId_idx` (`formId`),
  CONSTRAINT `fk_EventRegistrationForm_formId` FOREIGN KEY (`formId`) REFERENCES `EventForm` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_EventRegistrationForm_registrationId` FOREIGN KEY (`registrationId`) REFERENCES `EventRegistration` (`id`) ON DELETE CASCADE
);