<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202162221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie ADD le_emplacement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD634A3F94A4A FOREIGN KEY (le_emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_497DD634A3F94A4A ON categorie (le_emplacement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634A3F94A4A');
        $this->addSql('DROP INDEX UNIQ_497DD634A3F94A4A ON categorie');
        $this->addSql('ALTER TABLE categorie DROP le_emplacement_id');
    }
}
