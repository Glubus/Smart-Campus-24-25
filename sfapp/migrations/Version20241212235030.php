<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212235030 extends AbstractMigration
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
        $this->addSql('CREATE TABLE sa (id INT AUTO_INCREMENT NOT NULL, salle_id INT DEFAULT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_7F7E69046C6E55B5 (nom), INDEX IDX_7F7E6904DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, batiment_id INT NOT NULL, etage INT NOT NULL, nom VARCHAR(20) NOT NULL, fenetre INT DEFAULT NULL, radiateur INT DEFAULT NULL, INDEX IDX_4E977E5CD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salog (id INT AUTO_INCREMENT NOT NULL, sa_id INT DEFAULT NULL, date DATETIME NOT NULL, action VARCHAR(255) NOT NULL, INDEX IDX_8BB44CB362CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE valeur_capteur (id INT AUTO_INCREMENT NOT NULL, sa_id INT DEFAULT NULL, salle_id INT DEFAULT NULL, valeur DOUBLE PRECISION NOT NULL, date_ajout DATETIME NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_98BD141262CAE146 (sa_id), INDEX IDX_98BD1412DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C462CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE detail_plan ADD CONSTRAINT FK_2A0FB3F2DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE detail_plan ADD CONSTRAINT FK_2A0FB3F262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE sa ADD CONSTRAINT FK_7F7E6904DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE salog ADD CONSTRAINT FK_8BB44CB362CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD141262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD1412DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C462CAE146');
        $this->addSql('ALTER TABLE detail_plan DROP FOREIGN KEY FK_2A0FB3F2DC304035');
        $this->addSql('ALTER TABLE detail_plan DROP FOREIGN KEY FK_2A0FB3F262CAE146');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD6F6891B');
        $this->addSql('ALTER TABLE sa DROP FOREIGN KEY FK_7F7E6904DC304035');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5CD6F6891B');
        $this->addSql('ALTER TABLE salog DROP FOREIGN KEY FK_8BB44CB362CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD141262CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD1412DC304035');
        $this->addSql('DROP TABLE batiment');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE detail_plan');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE sa');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE salog');
        $this->addSql('DROP TABLE valeur_capteur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
