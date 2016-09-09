CREATE VIEW `sales_admin_api_auth_view` AS
SELECT
	`t`.`id` AS `id`,
	`t`.`token` AS `token`,
	`t`.`clientId` AS `clientId`,
	`a`.`id` AS `adminId`,
	`a`.`username` AS `username`
FROM (
	`sales_admin_token` `t`
  JOIN `sales_admin` `a`
	ON((`t`.`adminId` = `a`.`id`))
	)
WHERE (
	`t`.`creationDate` > (now() - interval 5 day)
);