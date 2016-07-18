CREATE VIEW ClientApiAuthView AS
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
