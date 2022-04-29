<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311042737 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_admin_user ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_promotion_coupon ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_promotion ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_promotion_rule ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_promotion_action ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_order ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shop_user ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_association ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_taxon_image ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_taxon ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL, ADD created_from_ip VARCHAR(45) DEFAULT NULL, ADD updated_from_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_address ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer_group ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_address DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE sylius_admin_user DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_customer DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE sylius_customer_group DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE sylius_order DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE sylius_product DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_product_association DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_promotion DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_promotion_action DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_promotion_coupon DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_promotion_rule DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_shop_user DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_taxon DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
        $this->addSql('ALTER TABLE sylius_taxon_image DROP created_by, DROP updated_by, DROP created_from_ip, DROP updated_from_ip');
    }
}
