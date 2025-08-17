<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250515105232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction migration pour éviter doublon colonne categories_id';
    }

    public function up(Schema $schema): void
    {
        // La colonne categories_id existe déjà, on commente ces lignes pour éviter l'erreur
        // $this->addSql('ALTER TABLE products ADD categories_id INT NOT NULL');
        // $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AA21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id)');
        // $this->addSql('CREATE INDEX IDX_B3BA5A5AA21214B7 ON products (categories_id)');
    }

    public function down(Schema $schema): void
    {
        // Comme la migration up ne fait rien, down ne fait rien non plus
        // $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AA21214B7');
        // $this->addSql('DROP INDEX IDX_B3BA5A5AA21214B7 ON products');
        // $this->addSql('ALTER TABLE products DROP categories_id');
    }
}
