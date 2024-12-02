<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202150101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande_detail (commande_id INT NOT NULL, detail_id INT NOT NULL, INDEX IDX_2C52844682EA2E54 (commande_id), INDEX IDX_2C528446D8D003BB (detail_id), PRIMARY KEY(commande_id, detail_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detail (id INT AUTO_INCREMENT NOT NULL, quantite_produit INT NOT NULL, prix_final DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detail_produit (detail_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_4E6A6CF2D8D003BB (detail_id), INDEX IDX_4E6A6CF2F347EFB (produit_id), PRIMARY KEY(detail_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, x INT NOT NULL, y INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantite_stock INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande_detail ADD CONSTRAINT FK_2C52844682EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_detail ADD CONSTRAINT FK_2C528446D8D003BB FOREIGN KEY (detail_id) REFERENCES detail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detail_produit ADD CONSTRAINT FK_4E6A6CF2D8D003BB FOREIGN KEY (detail_id) REFERENCES detail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detail_produit ADD CONSTRAINT FK_4E6A6CF2F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit ADD le_emplacement_id INT DEFAULT NULL, ADD le_stock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A3F94A4A FOREIGN KEY (le_emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27C3834AF9 FOREIGN KEY (le_stock_id) REFERENCES stock (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27A3F94A4A ON produit (le_emplacement_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27C3834AF9 ON produit (le_stock_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A3F94A4A');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27C3834AF9');
        $this->addSql('ALTER TABLE commande_detail DROP FOREIGN KEY FK_2C52844682EA2E54');
        $this->addSql('ALTER TABLE commande_detail DROP FOREIGN KEY FK_2C528446D8D003BB');
        $this->addSql('ALTER TABLE detail_produit DROP FOREIGN KEY FK_4E6A6CF2D8D003BB');
        $this->addSql('ALTER TABLE detail_produit DROP FOREIGN KEY FK_4E6A6CF2F347EFB');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_detail');
        $this->addSql('DROP TABLE detail');
        $this->addSql('DROP TABLE detail_produit');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP INDEX UNIQ_29A5EC27A3F94A4A ON produit');
        $this->addSql('DROP INDEX UNIQ_29A5EC27C3834AF9 ON produit');
        $this->addSql('ALTER TABLE produit DROP le_emplacement_id, DROP le_stock_id');
    }
}
