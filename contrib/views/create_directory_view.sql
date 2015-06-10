CREATE VIEW DirectoryView AS
SELECT
    cm.id,
    cm.userId,
    cm.companyId,
    vc.name,
    vc.email,
    vc.phone,
    -- we generate the jid by concatenating the username with xmpp domain
    CONCAT (
        cm.userId,
        '@',
        op.propValue
    ) AS jid
FROM CompanyMember cm
LEFT JOIN jtVCard AS vc ON cm.userId = vc.userId AND cm.companyId = vc.companyId
JOIN ofProperty op
WHERE cm.isDelete = 0 AND op.name = 'xmpp.domain';
