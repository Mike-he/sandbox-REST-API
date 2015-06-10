CREATE VIEW ValidApiUserView AS
SELECT
    t.clientId AS uii,
    u.id AS userId,
    u.xmppUsername AS username,
    t.creationDate,
    t.token AS secretDigest,
    -- we generate the jid by concatenating the username with
    -- XMPP domain
    CONCAT (
        u.xmppUsername,
        '@',
        op.propValue
    ) AS jid
FROM UserToken AS t
JOIN User AS u ON t.userId = u.id
JOIN Property op
WHERE
    -- current timestamp in millisecond
    UNIX_TIMESTAMP() * 1000
    <
    -- less than the expiration date
    -- which is ...
    (
        CAST(t.creationDate AS UNSIGNED)
        + 
        (
            -- ... either 14 days ...
            SELECT IFNULL(MIN(propValue), 14* 24*60*60*1000)
            FROM ofProperty
            -- ... or if existing the value entered in openfire conf
            WHERE name = 'auth.usersharedsecret.valid.time'
        ) 
    )
    AND op.name = 'xmpp.domain'
    AND u.activated = 1
;
