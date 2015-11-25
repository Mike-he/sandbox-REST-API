CREATE VIEW UserView AS
SELECT
       u.id,
       u.phone,
       u.email,
       u.banned,
       u.authorized,
       u.cardNo,
       u.credentialNo,
       up.name,
	     up.gender
FROM User u
LEFT JOIN UserProfile up ON u.id = up.userId
;