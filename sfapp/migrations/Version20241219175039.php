<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219175039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail_intervention (id INT AUTO_INCREMENT NOT NULL, technicien_id INT NOT NULL, salle_id INT NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_CD1B7EA413457256 (technicien_id), INDEX IDX_CD1B7EA4DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detail_intervention ADD CONSTRAINT FK_CD1B7EA413457256 FOREIGN KEY (technicien_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE detail_intervention ADD CONSTRAINT FK_CD1B7EA4DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE batiment CHANGE etages etages JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail_intervention DROP FOREIGN KEY FK_CD1B7EA413457256');
        $this->addSql('ALTER TABLE detail_intervention DROP FOREIGN KEY FK_CD1B7EA4DC304035');
        $this->addSql('DROP TABLE detail_intervention');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('ALTER TABLE batiment CHANGE etages etages JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
