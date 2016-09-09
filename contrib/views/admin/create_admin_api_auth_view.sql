CREATE VIEW admin_api_auth_view AS
SELECT
    t.id,
    t.token,
    t.clientId,
    a.id AS adminId,
    a.username
FROM admin_token AS t
JOIN admin AS a ON t.adminId = a.id
WHERE
    t.creationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
;