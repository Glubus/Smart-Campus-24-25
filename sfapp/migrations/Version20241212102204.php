<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212102204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, batiment_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_DD5A5B7DD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD sa_id INT DEFAULT NULL, ADD salle_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD141262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD1412DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_98BD141262CAE146 ON valeur_capteur (sa_id)');
        $this->addSql('CREATE INDEX IDX_98BD1412DC304035 ON valeur_capteur (salle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD6F6891B');
        $this->addSql('DROP TABLE plan');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD141262CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD1412DC304035');
        $this->addSql('DROP INDEX IDX_98BD141262CAE146 ON valeur_capteur');
        $this->addSql('DROP INDEX IDX_98BD1412DC304035 ON valeur_capteur');
        $this->addSql('ALTER TABLE valeur_capteur DROP sa_id, DROP salle_id, DROP type');
    }
}
