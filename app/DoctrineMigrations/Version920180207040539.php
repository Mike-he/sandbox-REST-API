<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920180207040539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('
            CREATE OR REPLACE VIEW sales_company_view AS
            SELECT
                sc.id AS id,
                sc.name AS name,
                sc.phone AS phone,
                sc.address AS address,
                sc.contacter AS contacter,
                sc.contacter_phone AS contacter_phone,
                sc.contacter_email AS contacter_email,
                sc.creation_date AS creation_date,
                if(sc.banned=0,"using","banned") as status,
                "sales_company" as type
            FROM sales_company AS sc
            UNION ALL
            SELECT 
                a.id as id,
                a.name as name,
                a.phone as phone,
                a.address as address,
                a.contacter AS contacter,
                a.contacter_phone AS contacter_phone,
                a.contacter_email AS contacter_email,
                a.creation_date AS creation_date,
                a.status as status,
                "sales_company_apply" as type
            FROM
            sales_company_apply as a
            where a.status in ("pending","accepted","refused")
            ;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
