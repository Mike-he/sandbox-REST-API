CREATE VIEW ClientUserLoginView AS
SELECT
    id,
    username,
    password,
    email,
    CONCAT(
        countryCode,
        phone
    ) AS phoneNumber,
    status
FROM User;
