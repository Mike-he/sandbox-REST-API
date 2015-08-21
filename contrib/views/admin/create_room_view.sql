CREATE VIEW RoomView AS
SELECT
	r.*,
	o.status,
	o.startDate as orderStartDate,
	o.endDate as orderEndDate,
	up.userId as renterId,
	up.name as renterName,
	up.email as renterEmail
FROM Room r
JOIN RoomFloor rf ON rf.id = r.floorId
LEFT JOIN Product p ON r.id = p.roomId
LEFT JOIN ProductOrder o ON p.id = o.productId
LEFT JOIN UserProfile up ON o.userId = up.userId
;