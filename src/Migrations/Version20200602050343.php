<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200602050343 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_address ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_address ADD CONSTRAINT FK_B97FF058727ACA70 FOREIGN KEY (parent_id) REFERENCES sylius_address (id)');
        $this->addSql('CREATE INDEX IDX_B97FF058727ACA70 ON sylius_address (parent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_address DROP FOREIGN KEY FK_B97FF058727ACA70');
        $this->addSql('DROP INDEX IDX_B97FF058727ACA70 ON sylius_address');
        $this->addSql('ALTER TABLE sylius_address DROP parent_id');
    }
}
