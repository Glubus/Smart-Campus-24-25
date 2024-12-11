<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204104609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE salog (id INT AUTO_INCREMENT NOT NULL, sa_id INT DEFAULT NULL, date DATETIME NOT NULL, action VARCHAR(255) NOT NULL, INDEX IDX_8BB44CB362CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE valeur_capteur (id INT AUTO_INCREMENT NOT NULL, capteur_id INT NOT NULL, valeur DOUBLE PRECISION NOT NULL, INDEX IDX_98BD14121708A229 (capteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salog ADD CONSTRAINT FK_8BB44CB362CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD14121708A229 FOREIGN KEY (capteur_id) REFERENCES capteur (id)');
        $this->addSql('ALTER TABLE capteur CHANGE sa_id sa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE detail_plan DROP INDEX UNIQ_DD5A5B7DDC304035, ADD INDEX IDX_DD5A5B7DDC304035 (salle_id)');
        $this->addSql('ALTER TABLE detail_plan DROP INDEX UNIQ_DD5A5B7D62CAE146, ADD INDEX IDX_DD5A5B7D62CAE146 (sa_id)');
        $this->addSql('ALTER TABLE detail_plan CHANGE date_ajout date_ajout DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salog DROP FOREIGN KEY FK_8BB44CB362CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD14121708A229');
        $this->addSql('DROP TABLE salog');
        $this->addSql('DROP TABLE valeur_capteur');
        $this->addSql('ALTER TABLE capteur CHANGE sa_id sa_id INT NOT NULL');
        $this->addSql('ALTER TABLE detail_plan DROP INDEX IDX_DD5A5B7DDC304035, ADD UNIQUE INDEX UNIQ_DD5A5B7DDC304035 (salle_id)');
        $this->addSql('ALTER TABLE detail_plan DROP INDEX IDX_DD5A5B7D62CAE146, ADD UNIQUE INDEX UNIQ_DD5A5B7D62CAE146 (sa_id)');
        $this->addSql('ALTER TABLE detail_plan CHANGE date_ajout date_ajout DATE NOT NULL');
    }
}
