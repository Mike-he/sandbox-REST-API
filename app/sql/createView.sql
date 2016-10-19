CREATE OR REPLACE VIEW AdminApiAuthView AS
SELECT
    t.id,
    t.token,
    t.clientId,
    a.id AS adminId,
    a.username
FROM AdminToken AS t
JOIN Admin AS a ON t.adminId = a.id
WHERE
    t.creationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
;


CREATE OR REPLACE VIEW ClientApiAuthView AS
SELECT
    t.id,
    t.token,
    t.clientId,
    u.id AS userId
FROM UserToken AS t
JOIN User AS u ON t.userId = u.id
WHERE
    t.modificationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
    AND
    u.banned != 1
    AND
    t.online = 1
;


CREATE OR REPLACE VIEW FeedView AS
SELECT DISTINCT f.*,
       (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
       (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
FROM Feed AS f;


CREATE OR REPLACE VIEW RoomUsageView AS
SELECT
    id,
	productId,
	status,
	startDate,
	endDate,
	userId as user,
	appointedPerson as appointedUser
FROM ProductOrder
;


CREATE OR REPLACE VIEW RoomView AS
SELECT
	r.*,
	o.status,
	o.startDate as orderStartDate,
	o.endDate as orderEndDate,
	up.userId as renterId,
	up.name as renterName,
	up.email as renterEmail
FROM Room r
JOIN RoomFloor rf ON rf.id = r.floorId
LEFT JOIN Product p ON r.id = p.roomId
LEFT JOIN ProductOrder o ON p.id = o.productId
LEFT JOIN UserProfile up ON o.userId = up.userId
;


CREATE OR REPLACE VIEW `SalesAdminApiAuthView` AS
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


CREATE OR REPLACE VIEW `ShopAdminApiAuthView` AS
SELECT
	`t`.`id` AS `id`,
	`t`.`token` AS `token`,
	`t`.`clientId` AS `clientId`,
	`a`.`id` AS `adminId`,
	`a`.`username` AS `username`
FROM (
	`ShopAdminToken` `t`
  JOIN `ShopAdmin` `a`
	ON((`t`.`adminId` = `a`.`id`))
	)
WHERE (
	`t`.`creationDate` > (now() - interval 5 day)
);


CREATE OR REPLACE VIEW UserView AS
SELECT
       u.id,
       u.phone,
       u.email,
       u.banned,
       u.authorized,
       u.cardNo,
       u.credentialNo,
       u.authorizedPlatform,
       u.authorizedAdminUsername,
       up.name,
	     up.gender,
	     u.creationDate as userRegistrationDate
FROM User u
LEFT JOIN UserProfile up ON u.id = up.userId
;