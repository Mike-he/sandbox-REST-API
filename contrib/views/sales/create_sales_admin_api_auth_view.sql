CREATE VIEW `SalesAdminApiAuthView` AS
SELECT
	`t`.`id` AS `id`,
	`t`.`token` AS `token`,
	`t`.`clientId` AS `clientId`,
	`a`.`id` AS `adminId`,
	`a`.`username` AS `username`
FROM (
	`SalesAdminToken` `t`
  JOIN `SalesAdmin` `a`
	ON((`t`.`adminId` = `a`.`id`))
	)
WHERE (
	`t`.`creationDate` > (now() - interval 5 day)
);