<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200528121847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_notification RENAME INDEX idx_fa7d8d7f4e328cbe TO IDX_7DDA6C4B4E328CBE');
        $this->addSql('ALTER TABLE app_notification RENAME INDEX idx_fa7d8d7fa45d93bf TO IDX_7DDA6C4BA45D93BF');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_notification RENAME INDEX idx_7dda6c4ba45d93bf TO IDX_FA7D8D7FA45D93BF');
        $this->addSql('ALTER TABLE app_notification RENAME INDEX idx_7dda6c4b4e328cbe TO IDX_FA7D8D7F4E328CBE');
    }
}
