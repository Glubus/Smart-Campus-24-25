<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119052124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plan CHANGE sa_id sa_id INT DEFAULT NULL, CHANGE salle_id salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sa DROP FOREIGN KEY FK_7F7E6904DC304035');
        $this->addSql('DROP INDEX UNIQ_7F7E6904DC304035 ON sa');
        $this->addSql('ALTER TABLE sa DROP salle_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F7E69046C6E55B5 ON sa (nom)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_7F7E69046C6E55B5 ON sa');
        $this->addSql('ALTER TABLE sa ADD salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sa ADD CONSTRAINT FK_7F7E6904DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F7E6904DC304035 ON sa (salle_id)');
        $this->addSql('ALTER TABLE plan CHANGE sa_id sa_id INT NOT NULL, CHANGE salle_id salle_id INT NOT NULL');
    }
}
