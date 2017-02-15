<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170118063034 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("
            CREATE VIEW trade_invoice_view AS
            SELECT
                `o`.`orderNumber` AS `number`,
                `o`.`userId` AS `user_id`,
                `o`.`creationDate` AS `creation_date`
            FROM `product_order` AS `o`
            WHERE
                `o`.`status` = 'completed'
                AND `o`.`discountPrice` > 0
                AND `o`.`payChannel` != 'account'
                AND `o`.`rejected` = false
                AND `o`.`invoiced` = false
                AND `o`.`salesInvoice` = true
            UNION ALL
            SELECT
                `l`.`serial_number` AS `number`,
                `l`.`drawee` AS `user_id`,
                `l`.`creation_date`
            FROM `lease_bill` AS `l`
            WHERE
                `l`.`status` = 'paid'
                AND `l`.`pay_channel` != 'offline'
                AND `l`.`pay_channel` != 'sales_offline'
                AND `l`.`payment_date` IS NOT NULL
                AND `l`.`invoiced` = false
                AND `l`.`sales_invoice` = true;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
