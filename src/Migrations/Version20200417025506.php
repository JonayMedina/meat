<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417025506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE push_notification DROP FOREIGN KEY FK_4ABA22EADB296AAD');
        $this->addSql('CREATE TABLE app_segment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, gender VARCHAR(3) DEFAULT NULL, frequency_type VARCHAR(20) DEFAULT NULL, fixed_amount NUMERIC(10, 2) DEFAULT NULL, purchase_times INT DEFAULT NULL, min_age INT DEFAULT NULL, max_age INT DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_push_notifications (id INT AUTO_INCREMENT NOT NULL, coupon_id INT DEFAULT NULL, segment_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(100) NOT NULL, sent TINYINT(1) NOT NULL, response JSON DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_from_ip VARCHAR(45) DEFAULT NULL, updated_from_ip VARCHAR(45) DEFAULT NULL, INDEX IDX_3B8AB53766C5951B (coupon_id), INDEX IDX_3B8AB537DB296AAD (segment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_push_notifications ADD CONSTRAINT FK_3B8AB53766C5951B FOREIGN KEY (coupon_id) REFERENCES sylius_promotion_coupon (id)');
        $this->addSql('ALTER TABLE app_push_notifications ADD CONSTRAINT FK_3B8AB537DB296AAD FOREIGN KEY (segment_id) REFERENCES app_segment (id)');
        $this->addSql('DROP TABLE push_notification');
        $this->addSql('DROP TABLE segment');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_push_notifications DROP FOREIGN KEY FK_3B8AB537DB296AAD');
        $this->addSql('CREATE TABLE push_notification (id INT AUTO_INCREMENT NOT NULL, coupon_id INT DEFAULT NULL, segment_id INT DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, type VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, sent TINYINT(1) NOT NULL, response JSON DEFAULT NULL, created_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, updated_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_from_ip VARCHAR(45) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, updated_from_ip VARCHAR(45) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_4ABA22EADB296AAD (segment_id), INDEX IDX_4ABA22EA66C5951B (coupon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE segment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, gender VARCHAR(3) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, frequency_type VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, fixed_amount NUMERIC(10, 2) DEFAULT NULL, purchase_times INT DEFAULT NULL, min_age INT DEFAULT NULL, max_age INT DEFAULT NULL, created_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, updated_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE push_notification ADD CONSTRAINT FK_4ABA22EA66C5951B FOREIGN KEY (coupon_id) REFERENCES sylius_promotion_coupon (id)');
        $this->addSql('ALTER TABLE push_notification ADD CONSTRAINT FK_4ABA22EADB296AAD FOREIGN KEY (segment_id) REFERENCES segment (id)');
        $this->addSql('DROP TABLE app_segment');
        $this->addSql('DROP TABLE app_push_notifications');
    }
}
