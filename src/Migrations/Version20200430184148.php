<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200430184148 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app_promotion_banner (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, photo_web LONGTEXT DEFAULT NULL, photo_tablet LONGTEXT DEFAULT NULL, photo_mobile LONGTEXT DEFAULT NULL, photo_app LONGTEXT DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_from_ip VARCHAR(45) DEFAULT NULL, updated_from_ip VARCHAR(45) DEFAULT NULL, INDEX IDX_7A2793DAA80EF684 (product_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_promotion_banner ADD CONSTRAINT FK_7A2793DAA80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_shop_user_device ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE app_promotion_banner');
        $this->addSql('ALTER TABLE app_shop_user_device DROP created_by, DROP updated_by, DROP created_at, DROP updated_at, DROP created_from_ip, DROP updated_from_ip');
    }
}
