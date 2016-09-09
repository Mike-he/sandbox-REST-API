CREATE VIEW room_view AS
SELECT
	r.*,
	o.status,
	o.startDate as orderStartDate,
	o.endDate as orderEndDate,
	up.userId as renterId,
	up.name as renterName,
	up.email as renterEmail
FROM room r
JOIN room_floor rf ON rf.id = r.floorId
LEFT JOIN product p ON r.id = p.roomId
LEFT JOIN product_order o ON p.id = o.productId
LEFT JOIN user_profiles up ON o.userId = up.userId
;