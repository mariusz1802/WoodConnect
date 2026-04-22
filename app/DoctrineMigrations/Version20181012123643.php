<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181012123643 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rate (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, ip VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, approved TINYINT(1) NOT NULL, confirmed TINYINT(1) NOT NULL, value NUMERIC(5, 2) NOT NULL, INDEX rate_view (approved, confirmed, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rate_category (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, value NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rate_value (rate_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', category_id BIGINT NOT NULL, value INT NOT NULL, INDEX IDX_8D374117BC999F9F (rate_id), INDEX IDX_8D37411712469DE2 (category_id), PRIMARY KEY(rate_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rate_value ADD CONSTRAINT FK_8D374117BC999F9F FOREIGN KEY (rate_id) REFERENCES rate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rate_value ADD CONSTRAINT FK_8D37411712469DE2 FOREIGN KEY (category_id) REFERENCES rate_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rate_value DROP FOREIGN KEY FK_8D374117BC999F9F');
        $this->addSql('ALTER TABLE rate_value DROP FOREIGN KEY FK_8D37411712469DE2');
        $this->addSql('DROP TABLE rate');
        $this->addSql('DROP TABLE rate_category');
        $this->addSql('DROP TABLE rate_value');
    }
}
