CREATE  TABLE `UserRegistration` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `phone` VARCHAR(64) NULL ,
  `email` VARCHAR(128) NULL ,
  `code` VARCHAR(16) NULL ,
  `creationDate` VARCHAR(15) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `uk_UserRegistration__phone` (`phone`) ,
  UNIQUE KEY `uk_UserRegistration__email` (`email`)
) ;
