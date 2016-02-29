CREATE VIEW `AdminApiAuthView` AS
SELECT
	`t`.`id` AS `id`,
	`t`.`token` AS `token`,
	`t`.`clientId` AS `clientId`,
	`a`.`id` AS `adminId`,
	`a`.`username` AS `username`
FORM (
	`AdminToken` `t`
  JOIN `Admin` `a`
	ON((`t`.`adminId` = `a`.`id`))
	)
WHERE (
	`t`.`creationDate` > (now() - interval 5 day)
);