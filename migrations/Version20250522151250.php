<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522151250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD categories_id INT NOT NULL, CHANGE quantity_per_unit quantity_per_unit INT NOT NULL, CHANGE unit_price unit_price NUMERIC(10, 2) NOT NULL, CHANGE units_on_stock units_on_stock INT NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AA21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5AA21214B7 ON products (categories_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AA21214B7');
        $this->addSql('DROP INDEX IDX_B3BA5A5AA21214B7 ON products');
        $this->addSql('ALTER TABLE products DROP categories_id, CHANGE quantity_per_unit quantity_per_unit VARCHAR(255) NOT NULL, CHANGE unit_price unit_price VARCHAR(255) NOT NULL, CHANGE units_on_stock units_on_stock VARCHAR(255) NOT NULL');
    }
}
