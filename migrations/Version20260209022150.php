<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209022150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE follow_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status VARCHAR(10) NOT NULL, requested_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, requester_id INTEGER NOT NULL, target_id INTEGER NOT NULL, CONSTRAINT FK_6562D72FED442CF4 FOREIGN KEY (requester_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6562D72F158E0B66 FOREIGN KEY (target_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6562D72FED442CF4 ON follow_request (requester_id)');
        $this->addSql('CREATE INDEX IDX_6562D72F158E0B66 ON follow_request (target_id)');
        $this->addSql('CREATE TABLE profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, display_name VARCHAR(50) NOT NULL, bio CLOB DEFAULT NULL, photo_filename VARCHAR(255) DEFAULT NULL, wizard_completed BOOLEAN NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_8157AA0FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0FA76ED395 ON profile (user_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE follow_request');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE "user"');
    }
}
