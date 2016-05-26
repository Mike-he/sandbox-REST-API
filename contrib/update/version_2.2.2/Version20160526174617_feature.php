<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160526174617_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE User SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE UserRegistration SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE ForgetPassword SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE PhoneVerification SET phoneCode = '+86' WHERE phone IS NOT NULL");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE User SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE UserRegistration SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE ForgetPassword SET phoneCode = '+86' WHERE phone IS NOT NULL");
        $this->addSql("UPDATE PhoneVerification SET phoneCode = '+86' WHERE phone IS NOT NULL");
    }
}