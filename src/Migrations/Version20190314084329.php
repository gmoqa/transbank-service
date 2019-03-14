<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190314084329 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5FCDAEAAA');
        $this->addSql('DROP INDEX IDX_8F3F68C5FCDAEAAA ON log');
        $this->addSql('ALTER TABLE log ADD created_at DATETIME NOT NULL, DROP order_id_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log ADD order_id_id INT NOT NULL, DROP created_at');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5FCDAEAAA ON log (order_id_id)');
    }
}
