<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121033326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE batiment ADD nb_etages INT NOT NULL');
        $this->addSql('ALTER TABLE plan DROP INDEX IDX_DD5A5B7D62CAE146, ADD UNIQUE INDEX UNIQ_DD5A5B7D62CAE146 (sa_id)');
        $this->addSql('ALTER TABLE plan DROP INDEX IDX_DD5A5B7DDC304035, ADD UNIQUE INDEX UNIQ_DD5A5B7DDC304035 (salle_id)');
        $this->addSql('ALTER TABLE plan DROP etat');
        $this->addSql('ALTER TABLE salle ADD nom VARCHAR(20) NOT NULL, DROP numero, CHANGE etage etage INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle ADD numero VARCHAR(2) DEFAULT NULL, DROP nom, CHANGE etage etage VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE batiment DROP nb_etages');
        $this->addSql('ALTER TABLE plan DROP INDEX UNIQ_DD5A5B7D62CAE146, ADD INDEX IDX_DD5A5B7D62CAE146 (sa_id)');
        $this->addSql('ALTER TABLE plan DROP INDEX UNIQ_DD5A5B7DDC304035, ADD INDEX IDX_DD5A5B7DDC304035 (salle_id)');
        $this->addSql('ALTER TABLE plan ADD etat VARCHAR(255) NOT NULL');
    }
}
