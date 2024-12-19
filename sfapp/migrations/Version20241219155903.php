<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219155903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE batiment ADD plan_id INT DEFAULT NULL, ADD etages JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE batiment ADD CONSTRAINT FK_F5FAB00CE899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('CREATE INDEX IDX_F5FAB00CE899029B ON batiment (plan_id)');
        $this->addSql('ALTER TABLE detail_plan ADD etat_sa VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD6F6891B');
        $this->addSql('DROP INDEX IDX_DD5A5B7DD6F6891B ON plan');
        $this->addSql('ALTER TABLE plan DROP batiment_id');
        $this->addSql('ALTER TABLE valeur_capteur ADD sa_id INT DEFAULT NULL, ADD salle_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD141262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD1412DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_98BD141262CAE146 ON valeur_capteur (sa_id)');
        $this->addSql('CREATE INDEX IDX_98BD1412DC304035 ON valeur_capteur (salle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE plan ADD batiment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('CREATE INDEX IDX_DD5A5B7DD6F6891B ON plan (batiment_id)');
        $this->addSql('ALTER TABLE batiment DROP FOREIGN KEY FK_F5FAB00CE899029B');
        $this->addSql('DROP INDEX IDX_F5FAB00CE899029B ON batiment');
        $this->addSql('ALTER TABLE batiment DROP plan_id, DROP etages');
        $this->addSql('ALTER TABLE detail_plan DROP etat_sa');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD141262CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD1412DC304035');
        $this->addSql('DROP INDEX IDX_98BD141262CAE146 ON valeur_capteur');
        $this->addSql('DROP INDEX IDX_98BD1412DC304035 ON valeur_capteur');
        $this->addSql('ALTER TABLE valeur_capteur DROP sa_id, DROP salle_id, DROP type');
    }
}
