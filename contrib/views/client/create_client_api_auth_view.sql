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
    AND u.status = 'activated'
;
