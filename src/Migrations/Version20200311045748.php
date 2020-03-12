<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311045748 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_about_store ADD first_purchase_message LONGTEXT DEFAULT NULL, ADD new_address_message LONGTEXT DEFAULT NULL, ADD maximum_purchase_value NUMERIC(13, 2) DEFAULT NULL, ADD minimum_purchase_value NUMERIC(13, 2) DEFAULT NULL, ADD days_to_choose_in_advance_to_purchase INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_about_store DROP first_purchase_message, DROP new_address_message, DROP maximum_purchase_value, DROP minimum_purchase_value, DROP days_to_choose_in_advance_to_purchase');
    }
}
