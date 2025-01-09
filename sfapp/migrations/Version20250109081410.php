<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250109081410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail_plan DROP INDEX IDX_2A0FB3F262CAE146, ADD UNIQUE INDEX UNIQ_2A0FB3F262CAE146 (sa_id)');
        $this->addSql('ALTER TABLE detail_plan CHANGE date_enleve date_enleve DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE detail_plan DROP INDEX UNIQ_2A0FB3F262CAE146, ADD INDEX IDX_2A0FB3F262CAE146 (sa_id)');
        $this->addSql('ALTER TABLE detail_plan CHANGE date_enleve date_enleve DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
