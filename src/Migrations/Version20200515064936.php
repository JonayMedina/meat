<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200515064936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_push_notifications ADD banner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_push_notifications ADD CONSTRAINT FK_3B8AB537684EC833 FOREIGN KEY (banner_id) REFERENCES app_promotion_banner (id)');
        $this->addSql('CREATE INDEX IDX_3B8AB537684EC833 ON app_push_notifications (banner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_push_notifications DROP FOREIGN KEY FK_3B8AB537684EC833');
        $this->addSql('DROP INDEX IDX_3B8AB537684EC833 ON app_push_notifications');
        $this->addSql('ALTER TABLE app_push_notifications DROP banner_id');
    }
}
