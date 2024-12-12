<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212185229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE batiment (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(10) NOT NULL, adresse VARCHAR(255) NOT NULL, nb_etages INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, sa_id INT DEFAULT NULL, description VARCHAR(50) NOT NULL, date_ajout DATE NOT NULL, nom_tech VARCHAR(25) NOT NULL, INDEX IDX_D9BEC0C462CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detail_plan (id INT AUTO_INCREMENT NOT NULL, salle_id INT DEFAULT NULL, sa_id INT DEFAULT NULL, date_ajout DATETIME NOT NULL, INDEX IDX_2A0FB3F2DC304035 (salle_id), INDEX IDX_2A0FB3F262CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, batiment_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_DD5A5B7DD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sa (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_7F7E69046C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, batiment_id INT NOT NULL, etage INT NOT NULL, nom VARCHAR(20) NOT NULL, fenetre INT DEFAULT NULL, radiateur INT DEFAULT NULL, INDEX IDX_4E977E5CD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C462CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE detail_plan ADD CONSTRAINT FK_2A0FB3F2DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE detail_plan ADD CONSTRAINT FK_2A0FB3F262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE salog ADD CONSTRAINT FK_8BB44CB362CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('DROP INDEX IDX_98BD14121708A229 ON valeur_capteur');
        $this->addSql('ALTER TABLE valeur_capteur ADD sa_id INT DEFAULT NULL, ADD salle_id INT DEFAULT NULL, ADD date_ajout DATETIME NOT NULL, ADD type VARCHAR(255) NOT NULL, DROP capteur_id');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD141262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD1412DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_98BD141262CAE146 ON valeur_capteur (sa_id)');
        $this->addSql('CREATE INDEX IDX_98BD1412DC304035 ON valeur_capteur (salle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salog DROP FOREIGN KEY FK_8BB44CB362CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD141262CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD1412DC304035');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C462CAE146');
        $this->addSql('ALTER TABLE detail_plan DROP FOREIGN KEY FK_2A0FB3F2DC304035');
        $this->addSql('ALTER TABLE detail_plan DROP FOREIGN KEY FK_2A0FB3F262CAE146');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD6F6891B');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5CD6F6891B');
        $this->addSql('DROP TABLE batiment');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE detail_plan');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE sa');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX IDX_98BD141262CAE146 ON valeur_capteur');
        $this->addSql('DROP INDEX IDX_98BD1412DC304035 ON valeur_capteur');
        $this->addSql('ALTER TABLE valeur_capteur ADD capteur_id INT NOT NULL, DROP sa_id, DROP salle_id, DROP date_ajout, DROP type');
        $this->addSql('CREATE INDEX IDX_98BD14121708A229 ON valeur_capteur (capteur_id)');
    }
}
