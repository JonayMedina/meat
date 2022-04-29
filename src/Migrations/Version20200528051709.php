<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200528051709 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app_notification (id INT AUTO_INCREMENT NOT NULL, push_notification_id INT DEFAULT NULL, shop_user_id INT NOT NULL, title VARCHAR(200) NOT NULL, text LONGTEXT NOT NULL, type VARCHAR(100) NOT NULL, seen TINYINT(1) NOT NULL, response JSON DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, INDEX IDX_FA7D8D7F4E328CBE (push_notification_id), INDEX IDX_FA7D8D7FA45D93BF (shop_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_notification ADD CONSTRAINT FK_FA7D8D7F4E328CBE FOREIGN KEY (push_notification_id) REFERENCES app_push_notifications (id)');
        $this->addSql('ALTER TABLE app_notification ADD CONSTRAINT FK_FA7D8D7FA45D93BF FOREIGN KEY (shop_user_id) REFERENCES sylius_shop_user (id)');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE sylius_order DROP days_in_advance_to_purchase');
        $this->addSql('DELETE FROM app_push_notifications WHERE id > 0');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, push_notification_id INT DEFAULT NULL, shop_user_id INT NOT NULL, title VARCHAR(200) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, text LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, type VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, seen TINYINT(1) NOT NULL, response JSON DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, updated_by VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_BF5476CAA45D93BF (shop_user_id), INDEX IDX_BF5476CA4E328CBE (push_notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4E328CBE FOREIGN KEY (push_notification_id) REFERENCES app_push_notifications (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA45D93BF FOREIGN KEY (shop_user_id) REFERENCES sylius_shop_user (id)');
        $this->addSql('DROP TABLE app_notification');
        $this->addSql('ALTER TABLE sylius_order ADD days_in_advance_to_purchase INT DEFAULT NULL');
    }
}
