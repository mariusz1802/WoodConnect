<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170331102544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE node (id BIGINT AUTO_INCREMENT NOT NULL, parent_id BIGINT DEFAULT NULL, node_url_id BIGINT DEFAULT NULL, root_module_id BIGINT DEFAULT NULL, pos INT NOT NULL, type VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, INDEX IDX_857FE845727ACA70 (parent_id), INDEX IDX_857FE84535DDCF42 (node_url_id), INDEX IDX_857FE845E6D0AFB6 (root_module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_module (id BIGINT AUTO_INCREMENT NOT NULL, node_id BIGINT DEFAULT NULL, section VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, pos INT NOT NULL, INDEX IDX_B836338D460D9FD7 (node_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_module_variable (node_module_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, pos INT NOT NULL, type VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, text_value LONGTEXT DEFAULT NULL, date_time_value DATETIME DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, mime VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_43FF701BB47DBDF2 (node_module_id), PRIMARY KEY(node_module_id, name, locale, pos)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_url (id BIGINT AUTO_INCREMENT NOT NULL, locale VARCHAR(2) NOT NULL, slug VARCHAR(255) NOT NULL, path LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_875367D54180C698989D9B62 (locale, slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_variable (node_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, pos INT NOT NULL, type VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, text_value LONGTEXT DEFAULT NULL, date_time_value DATETIME DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, mime VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_72E09FD8460D9FD7 (node_id), PRIMARY KEY(node_id, name, locale, pos)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (name VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, pos INT NOT NULL, type VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, text_value LONGTEXT DEFAULT NULL, date_time_value DATETIME DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, mime VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, PRIMARY KEY(name, locale, pos)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_users (id INT AUTO_INCREMENT NOT NULL, password VARCHAR(60) NOT NULL, email VARCHAR(60) NOT NULL, main_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_B4A95E13E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845727ACA70 FOREIGN KEY (parent_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE84535DDCF42 FOREIGN KEY (node_url_id) REFERENCES node_url (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845E6D0AFB6 FOREIGN KEY (root_module_id) REFERENCES node_module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_module ADD CONSTRAINT FK_B836338D460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_module_variable ADD CONSTRAINT FK_43FF701BB47DBDF2 FOREIGN KEY (node_module_id) REFERENCES node_module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD8460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845727ACA70');
        $this->addSql('ALTER TABLE node_module DROP FOREIGN KEY FK_B836338D460D9FD7');
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD8460D9FD7');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845E6D0AFB6');
        $this->addSql('ALTER TABLE node_module_variable DROP FOREIGN KEY FK_43FF701BB47DBDF2');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE84535DDCF42');
        $this->addSql('DROP TABLE node');
        $this->addSql('DROP TABLE node_module');
        $this->addSql('DROP TABLE node_module_variable');
        $this->addSql('DROP TABLE node_url');
        $this->addSql('DROP TABLE node_variable');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE admin_users');
    }
}
