CREATE VIEW UserView AS
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
	     u.creationDate as userRegistrationDate
FROM User u
LEFT JOIN UserProfile up ON u.id = up.userId
;