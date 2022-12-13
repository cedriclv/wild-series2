<?php

declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;/**
* Auto-generated Migration: Please modify to your needs!
*/
final class Version20221104155816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE program ADD country VARCHAR(255) NOT NULL, ADD year INT NOT NULL, ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED778412469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_92ED778412469DE2 ON program (category_id)');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED778412469DE2');
        $this->addSql('DROP INDEX IDX_92ED778412469DE2 ON program');
        $this->addSql('ALTER TABLE program DROP country, DROP year, DROP category_id');
    }
}