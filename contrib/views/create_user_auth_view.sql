CREATE VIEW UserAuthView AS
SELECT
    id,
    xmppUsername,
    activated,
    password,
    email,
    CONCAT(
        countryCode,
        phone
    ) AS phoneNum
FROM jtUser;
