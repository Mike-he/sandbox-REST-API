CREATE VIEW AdminApiAuthView AS
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
