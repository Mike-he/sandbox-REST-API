CREATE VIEW client_api_auth_view AS
SELECT
    t.id,
    t.token,
    t.clientId,
    u.id AS userId
FROM user_token AS t
JOIN user AS u ON t.userId = u.id
WHERE
    t.modificationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
    AND
    u.banned != 1
    AND
    t.online = 1
;
