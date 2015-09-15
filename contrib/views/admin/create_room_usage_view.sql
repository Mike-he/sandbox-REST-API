CREATE VIEW RoomUsageView AS
SELECT
    id,
	productId,
	status,
	startDate,
	endDate,
	userId as user,
	appointedPerson as appointedUser
FROM ProductOrder
;