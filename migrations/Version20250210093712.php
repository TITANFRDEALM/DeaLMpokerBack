<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210093712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture_nft DROP FOREIGN KEY FK_E923C50FEE45BDBF');
        $this->addSql('ALTER TABLE picture_nft DROP FOREIGN KEY FK_E923C50FE813668D');
        $this->addSql('DROP TABLE picture_nft');
        $this->addSql('ALTER TABLE picture ADD nft_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89E813668D FOREIGN KEY (nft_id) REFERENCES nft (id)');
        $this->addSql('CREATE INDEX IDX_16DB4F89E813668D ON picture (nft_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE picture_nft (picture_id INT NOT NULL, nft_id INT NOT NULL, INDEX IDX_E923C50FE813668D (nft_id), INDEX IDX_E923C50FEE45BDBF (picture_id), PRIMARY KEY(picture_id, nft_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE picture_nft ADD CONSTRAINT FK_E923C50FEE45BDBF FOREIGN KEY (picture_id) REFERENCES picture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture_nft ADD CONSTRAINT FK_E923C50FE813668D FOREIGN KEY (nft_id) REFERENCES nft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F89E813668D');
        $this->addSql('DROP INDEX IDX_16DB4F89E813668D ON picture');
        $this->addSql('ALTER TABLE picture DROP nft_id');
    }
}
