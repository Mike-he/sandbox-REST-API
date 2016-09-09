CREATE VIEW room_usage_view AS
SELECT
  id,
	productId,
	status,
	startDate,
	endDate,
	userId as user,
	appointedPerson as appointedUser
FROM product_order
;