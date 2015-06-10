CREATE VIEW CompanyMemberView AS
SELECT
    cm.id,
    cm.userId,
    cm.companyId,
    vc.name,
    vc.email,
    vc.phone
FROM CompanyMember AS cm
LEFT JOIN jtVCard AS vc ON cm.userId = vc.userId AND cm.companyId = vc.companyId
WHERE cm.isDelete = 0;
