<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200601043239 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_notification ADD order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_notification ADD CONSTRAINT FK_7DDA6C4B8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('CREATE INDEX IDX_7DDA6C4B8D9F6D38 ON app_notification (order_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_notification DROP FOREIGN KEY FK_7DDA6C4B8D9F6D38');
        $this->addSql('DROP INDEX IDX_7DDA6C4B8D9F6D38 ON app_notification');
        $this->addSql('ALTER TABLE app_notification DROP order_id');
    }
}
