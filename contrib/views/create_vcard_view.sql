CREATE VIEW VCardView AS
SELECT
    CONCAT(
        username,
        '@',
        -- the domain stored in database (as openfire support only ONE
        -- domain
        (SELECT propValue FROM ofProperty WHERE name = 'xmpp.domain')
    ) AS jid,
    vcard
FROM ofVCard;
