<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Message edit/delete fields
 */
final class Version20251110211800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add edited and deleted fields to message table';
    }

    public function up(Schema $schema): void
    {
        // Add edited and deleted boolean columns to message table
        $this->addSql('ALTER TABLE message ADD edited BOOLEAN DEFAULT FALSE NOT NULL');
        $this->addSql('ALTER TABLE message ADD deleted BOOLEAN DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the columns if rolling back
        $this->addSql('ALTER TABLE message DROP edited');
        $this->addSql('ALTER TABLE message DROP deleted');
    }
}
