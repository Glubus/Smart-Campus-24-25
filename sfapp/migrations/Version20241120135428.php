<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120135428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plan CHANGE salle_id salle_id INT DEFAULT NULL, CHANGE sa_id sa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sa DROP date_ajout');
        $this->addSql('ALTER TABLE salle ADD nom VARCHAR(20) NOT NULL, CHANGE etage etage INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle DROP nom, CHANGE etage etage VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sa ADD date_ajout DATETIME NOT NULL');
        $this->addSql('ALTER TABLE plan CHANGE sa_id sa_id INT NOT NULL, CHANGE salle_id salle_id INT NOT NULL');
    }
}
