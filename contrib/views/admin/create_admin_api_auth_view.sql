CREATE VIEW AdminApiAuthView AS
SELECT
    t.id,
    t.token,
    t.clientId,
    a.id AS adminId,
    a.username
FROM AdminToken AS t
JOIN Admin AS a ON t.username = a.username
WHERE
    -- current timestamp in millisecond
    UNIX_TIMESTAMP() * 1000
    <
    -- less than the expiration date
    -- which is 14 days
    (
        CAST(t.creationDate AS UNSIGNED)
        + 
        (14*24*60*60*1000)
    )
;
