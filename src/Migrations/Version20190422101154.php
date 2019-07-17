<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190422101154 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE info (id INT AUTO_INCREMENT NOT NULL, ratio VARCHAR(50) NOT NULL, lbs_lat NUMERIC(16, 6) DEFAULT NULL, lbs_lng NUMERIC(16, 6) DEFAULT NULL, user_id INT NOT NULL, content LONGTEXT NOT NULL, fav_count INT NOT NULL, col_count INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE info_img (id INT AUTO_INCREMENT NOT NULL, info_id INT NOT NULL, path VARCHAR(255) NOT NULL, file_size INT NOT NULL, height INT NOT NULL, weight INT NOT NULL, format VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE info_img_tag (id INT AUTO_INCREMENT NOT NULL, info_img_id INT NOT NULL, x NUMERIC(4, 3) NOT NULL, y NUMERIC(4, 3) NOT NULL, content VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE info');
        $this->addSql('DROP TABLE info_img');
        $this->addSql('DROP TABLE info_img_tag');
    }
}
