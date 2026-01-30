<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130170016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, member_id INT NOT NULL, tournament_id INT NOT NULL, INDEX IDX_AB55E24F7597D3FE (member_id), INDEX IDX_AB55E24F33D1A3E7 (tournament_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tournoi (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, level VARCHAR(100) NOT NULL, address VARCHAR(255) NOT NULL, status VARCHAR(30) NOT NULL, tournament_name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F7597D3FE FOREIGN KEY (member_id) REFERENCES membre (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournoi (id)');
        $this->addSql('ALTER TABLE membre DROP password');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F7597D3FE');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F33D1A3E7');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE tournoi');
        $this->addSql('ALTER TABLE membre ADD password VARCHAR(255) NOT NULL');
    }
}
