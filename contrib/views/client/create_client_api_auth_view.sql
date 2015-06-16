CREATE VIEW ClientApiAuthView AS
SELECT
    t.id,
    t.token,
    t.clientId,
    u.id AS userId,
    u.username
FROM UserToken AS t
JOIN User AS u ON t.username = u.username
WHERE
    t.creationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
;
