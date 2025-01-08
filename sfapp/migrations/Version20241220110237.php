<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241220110237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etage (id INT AUTO_INCREMENT NOT NULL, batiment_id INT NOT NULL, nom VARCHAR(255) NOT NULL, niveau INT NOT NULL, INDEX IDX_2DDCF14BD6F6891B (batiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etage ADD CONSTRAINT FK_2DDCF14BD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('ALTER TABLE batiment DROP etages');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5CD6F6891B');
        $this->addSql('DROP INDEX IDX_4E977E5CD6F6891B ON salle');
        $this->addSql('ALTER TABLE salle ADD etage_id INT NOT NULL, DROP batiment_id, DROP etage');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C984CE93F FOREIGN KEY (etage_id) REFERENCES etage (id)');
        $this->addSql('CREATE INDEX IDX_4E977E5C984CE93F ON salle (etage_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C984CE93F');
        $this->addSql('ALTER TABLE etage DROP FOREIGN KEY FK_2DDCF14BD6F6891B');
        $this->addSql('DROP TABLE etage');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX IDX_4E977E5C984CE93F ON salle');
        $this->addSql('ALTER TABLE salle ADD etage INT NOT NULL, CHANGE etage_id batiment_id INT NOT NULL');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('CREATE INDEX IDX_4E977E5CD6F6891B ON salle (batiment_id)');
        $this->addSql('ALTER TABLE batiment ADD etages JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
