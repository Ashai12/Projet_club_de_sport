<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211113616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE group_membre (group_id INT NOT NULL, membre_id INT NOT NULL, INDEX IDX_253223F9FE54D947 (group_id), INDEX IDX_253223F96A99F74A (membre_id), PRIMARY KEY (group_id, membre_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE group_membre ADD CONSTRAINT FK_253223F9FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_membre ADD CONSTRAINT FK_253223F96A99F74A FOREIGN KEY (membre_id) REFERENCES membre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_membre DROP FOREIGN KEY FK_253223F9FE54D947');
        $this->addSql('ALTER TABLE group_membre DROP FOREIGN KEY FK_253223F96A99F74A');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_membre');
    }
}
