<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325165813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_about_store CHANGE delivery_hours delivery_hours JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE app_location CHANGE schedule schedule JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE app_faq CHANGE time_to_place_an_order time_to_place_an_order JSON DEFAULT NULL, CHANGE order_delivery_time order_delivery_time JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_address ADD full_address LONGTEXT DEFAULT NULL, ADD annotations LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_about_store CHANGE delivery_hours delivery_hours LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE app_faq CHANGE time_to_place_an_order time_to_place_an_order LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE order_delivery_time order_delivery_time LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE app_location CHANGE schedule schedule LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE sylius_address DROP full_address, DROP annotations');
    }
}
