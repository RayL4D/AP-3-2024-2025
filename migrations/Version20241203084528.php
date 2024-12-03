<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203084528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, commande_id INT NOT NULL, quantite_produit INT NOT NULL, prix_final NUMERIC(10, 2) NOT NULL, INDEX IDX_2E067F93F347EFB (produit_id), INDEX IDX_2E067F9382EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, la_categorie_id INT DEFAULT NULL, le_emplacement_id INT DEFAULT NULL, le_stock_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_29A5EC27281042B9 (la_categorie_id), UNIQUE INDEX UNIQ_29A5EC27A3F94A4A (le_emplacement_id), UNIQUE INDEX UNIQ_29A5EC27C3834AF9 (le_stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detail ADD CONSTRAINT FK_2E067F93F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE detail ADD CONSTRAINT FK_2E067F9382EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27281042B9 FOREIGN KEY (la_categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A3F94A4A FOREIGN KEY (le_emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27C3834AF9 FOREIGN KEY (le_stock_id) REFERENCES stock (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail DROP FOREIGN KEY FK_2E067F93F347EFB');
        $this->addSql('ALTER TABLE detail DROP FOREIGN KEY FK_2E067F9382EA2E54');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27281042B9');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A3F94A4A');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27C3834AF9');
        $this->addSql('DROP TABLE detail');
        $this->addSql('DROP TABLE produit');
    }
}
