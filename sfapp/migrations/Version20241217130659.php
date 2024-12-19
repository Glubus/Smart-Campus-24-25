<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217130659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE batiment ADD plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE batiment ADD CONSTRAINT FK_F5FAB00CE899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('CREATE INDEX IDX_F5FAB00CE899029B ON batiment (plan_id)');
        $this->addSql('ALTER TABLE detail_plan ADD etat_sa VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DD6F6891B');
        $this->addSql('DROP INDEX IDX_DD5A5B7DD6F6891B ON plan');
        $this->addSql('ALTER TABLE plan DROP batiment_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE batiment DROP FOREIGN KEY FK_F5FAB00CE899029B');
        $this->addSql('DROP INDEX IDX_F5FAB00CE899029B ON batiment');
        $this->addSql('ALTER TABLE batiment DROP plan_id');
        $this->addSql('ALTER TABLE plan ADD batiment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('CREATE INDEX IDX_DD5A5B7DD6F6891B ON plan (batiment_id)');
        $this->addSql('ALTER TABLE detail_plan DROP etat_sa');
    }
}
