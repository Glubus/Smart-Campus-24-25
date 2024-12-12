<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212140442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail_plan DROP FOREIGN KEY FK_2A0FB3F2E899029B');
        $this->addSql('DROP INDEX IDX_2A0FB3F2E899029B ON detail_plan');
        $this->addSql('ALTER TABLE detail_plan DROP plan_id');
        $this->addSql('ALTER TABLE valeur_capteur ADD sa_id INT DEFAULT NULL, ADD salle_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD141262CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE valeur_capteur ADD CONSTRAINT FK_98BD1412DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_98BD141262CAE146 ON valeur_capteur (sa_id)');
        $this->addSql('CREATE INDEX IDX_98BD1412DC304035 ON valeur_capteur (salle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD141262CAE146');
        $this->addSql('ALTER TABLE valeur_capteur DROP FOREIGN KEY FK_98BD1412DC304035');
        $this->addSql('DROP INDEX IDX_98BD141262CAE146 ON valeur_capteur');
        $this->addSql('DROP INDEX IDX_98BD1412DC304035 ON valeur_capteur');
        $this->addSql('ALTER TABLE valeur_capteur DROP sa_id, DROP salle_id, DROP type');
        $this->addSql('ALTER TABLE detail_plan ADD plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE detail_plan ADD CONSTRAINT FK_2A0FB3F2E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('CREATE INDEX IDX_2A0FB3F2E899029B ON detail_plan (plan_id)');
    }
}
