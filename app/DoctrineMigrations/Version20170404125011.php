<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170404125011 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE newsletter_content (id BIGINT AUTO_INCREMENT NOT NULL, locale VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL, send_at DATETIME DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_email (email VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL, token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6DFF3080E7927C74 (email), PRIMARY KEY(email)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE newsletter_content');
        $this->addSql('DROP TABLE newsletter_email');
    }
}
