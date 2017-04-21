<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161207025408 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill ADD sender INT DEFAULT NULL, ADD drawee INT DEFAULT NULL, ADD payment_date DATETIME DEFAULT NULL, ADD send_date DATETIME DEFAULT NULL, ADD order_method VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE lease_bill ADD CONSTRAINT FK_467B22FC5F004ACF FOREIGN KEY (sender) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease_bill ADD CONSTRAINT FK_467B22FC56C3BBC6 FOREIGN KEY (drawee) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_467B22FC5F004ACF ON lease_bill (sender)');
        $this->addSql('CREATE INDEX IDX_467B22FC56C3BBC6 ON lease_bill (drawee)');
        $this->addSql('ALTER TABLE product_appointment ADD user_id INT NOT NULL, ADD product_id INT NOT NULL, ADD applicant_name VARCHAR(255) NOT NULL, ADD applicant_company VARCHAR(255) NOT NULL, ADD applicant_phone VARCHAR(255) NOT NULL, ADD applicant_email VARCHAR(255) NOT NULL, ADD start_rent_date DATETIME NOT NULL, ADD rent_time_length INT NOT NULL, ADD creation_date DATETIME NOT NULL, ADD modification_date DATETIME NOT NULL, ADD address VARCHAR(255) DEFAULT NULL, DROP userId, DROP productId, DROP applicantName, DROP applicantCompany, DROP applicantPhone, DROP applicantEmail, DROP startRentDate, DROP rentTimeLength, DROP creationDate, DROP modificationDate, CHANGE renttimeunit rent_time_unit VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE user_appointment_profiles ADD creation_date DATETIME NOT NULL, ADD modification_date DATETIME NOT NULL, DROP creationDate, DROP modificationDate');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill DROP FOREIGN KEY FK_467B22FC5F004ACF');
        $this->addSql('ALTER TABLE lease_bill DROP FOREIGN KEY FK_467B22FC56C3BBC6');
        $this->addSql('DROP INDEX IDX_467B22FC5F004ACF ON lease_bill');
        $this->addSql('DROP INDEX IDX_467B22FC56C3BBC6 ON lease_bill');
        $this->addSql('ALTER TABLE lease_bill DROP sender, DROP drawee, DROP payment_date, DROP send_date, DROP order_method');
        $this->addSql('ALTER TABLE product_appointment ADD userId INT NOT NULL, ADD productId INT NOT NULL, ADD applicantName VARCHAR(255) NOT NULL, ADD applicantCompany VARCHAR(255) NOT NULL, ADD applicantPhone VARCHAR(255) NOT NULL, ADD applicantEmail VARCHAR(255) NOT NULL, ADD startRentDate DATETIME NOT NULL, ADD rentTimeLength INT NOT NULL, ADD creationDate DATETIME NOT NULL, ADD modificationDate DATETIME NOT NULL, DROP user_id, DROP product_id, DROP applicant_name, DROP applicant_company, DROP applicant_phone, DROP applicant_email, DROP start_rent_date, DROP rent_time_length, DROP creation_date, DROP modification_date, DROP address, CHANGE rent_time_unit rentTimeUnit VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE user_appointment_profiles ADD creationDate DATETIME NOT NULL, ADD modificationDate DATETIME NOT NULL, DROP creation_date, DROP modification_date');
    }
}
