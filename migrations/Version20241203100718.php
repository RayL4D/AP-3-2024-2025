<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203100718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, le_emplacement_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_497DD634A3F94A4A (le_emplacement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detail (id INT AUTO_INCREMENT NOT NULL, le_produit_id INT DEFAULT NULL, la_commande_id INT DEFAULT NULL, quantite_produit INT NOT NULL, INDEX IDX_2E067F932C340150 (le_produit_id), INDEX IDX_2E067F933743EDD (la_commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, x INT NOT NULL, y INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, la_categorie_id INT DEFAULT NULL, le_emplacement_id INT DEFAULT NULL, le_stock_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_29A5EC27281042B9 (la_categorie_id), UNIQUE INDEX UNIQ_29A5EC27A3F94A4A (le_emplacement_id), UNIQUE INDEX UNIQ_29A5EC27C3834AF9 (le_stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantite_stock INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD634A3F94A4A FOREIGN KEY (le_emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE detail ADD CONSTRAINT FK_2E067F932C340150 FOREIGN KEY (le_produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE detail ADD CONSTRAINT FK_2E067F933743EDD FOREIGN KEY (la_commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27281042B9 FOREIGN KEY (la_categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A3F94A4A FOREIGN KEY (le_emplacement_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27C3834AF9 FOREIGN KEY (le_stock_id) REFERENCES stock (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634A3F94A4A');
        $this->addSql('ALTER TABLE detail DROP FOREIGN KEY FK_2E067F932C340150');
        $this->addSql('ALTER TABLE detail DROP FOREIGN KEY FK_2E067F933743EDD');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27281042B9');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A3F94A4A');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27C3834AF9');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE detail');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
