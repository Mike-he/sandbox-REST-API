CREATE OR REPLACE VIEW user_view AS
SELECT
       u.id,
       u.phone,
       u.email,
       u.banned,
       u.authorized,
       u.cardNo,
       u.credentialNo,
       u.authorizedPlatform,
       u.authorizedAdminUsername,
       up.name,
       up.gender,
       u.creationDate as userRegistrationDate,
       u.bean as bean
FROM user u
LEFT JOIN user_profiles up ON u.id = up.userId