<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717121505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE allergie_categorie (allergie_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_6D37F3D67C86304A (allergie_id), INDEX IDX_6D37F3D6BCF5E72D (categorie_id), PRIMARY KEY (allergie_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE alternative (id INT AUTO_INCREMENT NOT NULL, note VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, ingredient_id INT NOT NULL, produit_substitut_id INT NOT NULL, INDEX IDX_EFF5DFA933FE08C (ingredient_id), INDEX IDX_EFF5DFABB0B8227 (produit_substitut_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_liste (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, coche TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, liste_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_8B4558E5E85441D8 (liste_id), INDEX IDX_8B4558E5F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, modere TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, recette_id INT NOT NULL, auteur_id INT DEFAULT NULL, INDEX IDX_67F068BC89312FE9 (recette_id), INDEX IDX_67F068BC60BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, enseigne VARCHAR(150) NOT NULL, valeur NUMERIC(6, 2) NOT NULL, expiration DATE NOT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_64BF3F02FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE creneau (id INT AUTO_INCREMENT NOT NULL, jour VARCHAR(20) NOT NULL, moment VARCHAR(10) NOT NULL, plat_libre VARCHAR(255) DEFAULT NULL, url_source VARCHAR(500) DEFAULT NULL, notification_envoyee TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, planning_id INT NOT NULL, recette_id INT DEFAULT NULL, INDEX IDX_F9668B5F3D865311 (planning_id), INDEX IDX_F9668B5F89312FE9 (recette_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etape_recette (id INT AUTO_INCREMENT NOT NULL, ordre SMALLINT UNSIGNED NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, recette_id INT NOT NULL, INDEX IDX_12D2C04B89312FE9 (recette_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE favori (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_EF85A2CCFB88E14F (utilisateur_id), INDEX IDX_EF85A2CC89312FE9 (recette_id), UNIQUE INDEX uniq_utilisateur_recette_favori (utilisateur_id, recette_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, quantite NUMERIC(8, 2) DEFAULT NULL, unite VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, recette_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_6BAF787089312FE9 (recette_id), INDEX IDX_6BAF7870F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ingredient_ref (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ingredient_categorie (ingredient_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_430F7E43933FE08C (ingredient_id), INDEX IDX_430F7E43BCF5E72D (categorie_id), PRIMARY KEY (ingredient_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ingredient_regime_alimentaire (ingredient_id INT NOT NULL, regime_id INT NOT NULL, INDEX IDX_6FCAC5E7933FE08C (ingredient_id), INDEX IDX_6FCAC5E735E7D534 (regime_id), PRIMARY KEY (ingredient_id, regime_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE liste_courses (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_189FC21DFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, valeur NUMERIC(3, 1) NOT NULL, created_at DATETIME NOT NULL, recette_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_CFBDFA1489312FE9 (recette_id), INDEX IDX_CFBDFA14FB88E14F (utilisateur_id), UNIQUE INDEX uniq_recette_utilisateur_note (recette_id, utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, semaine_debut DATE NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_D499BFF6FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preference_gout (id INT AUTO_INCREMENT NOT NULL, smiley VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, ingredient_ref_id INT NOT NULL, INDEX IDX_B6E9FC01FB88E14F (utilisateur_id), INDEX IDX_B6E9FC01CDCD81E8 (ingredient_ref_id), UNIQUE INDEX uniq_utilisateur_ingredient (utilisateur_id, ingredient_ref_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preferences (id INT AUTO_INCREMENT NOT NULL, nb_personnes INT NOT NULL, budget_mensuel INT NOT NULL, jour_courses VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_E931A6F5FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preferences_allergie (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, preferences_id INT NOT NULL, allergie_id INT NOT NULL, INDEX IDX_BF0457587CCD6FB7 (preferences_id), INDEX IDX_BF0457587C86304A (allergie_id), UNIQUE INDEX uniq_preferences_allergie (preferences_id, allergie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preferences_equipement (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, preferences_id INT NOT NULL, equipement_id INT NOT NULL, INDEX IDX_22F86DE87CCD6FB7 (preferences_id), INDEX IDX_22F86DE8806F0F5C (equipement_id), UNIQUE INDEX uniq_preferences_equipement (preferences_id, equipement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preferences_regime (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, preferences_id INT NOT NULL, regime_id INT NOT NULL, INDEX IDX_9D80D5F67CCD6FB7 (preferences_id), INDEX IDX_9D80D5F635E7D534 (regime_id), UNIQUE INDEX uniq_preferences_regime (preferences_id, regime_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, code_barres VARCHAR(13) DEFAULT NULL, categorie VARCHAR(150) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_29A5EC2768EF62E0 (code_barres), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recette (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, temps_preparation INT DEFAULT NULL, temps_cuisson INT DEFAULT NULL, nb_personnes INT NOT NULL, difficulte VARCHAR(20) DEFAULT NULL, budget_estime NUMERIC(6, 2) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, visibilite VARCHAR(20) NOT NULL, brouillon TINYINT DEFAULT 0 NOT NULL, anonymisee TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, auteur_id INT DEFAULT NULL, origine_id INT DEFAULT NULL, INDEX IDX_49BB639060BB6FE6 (auteur_id), INDEX IDX_49BB639087998E (origine_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recette_equipement (recette_id INT NOT NULL, equipement_id INT NOT NULL, INDEX IDX_C4DBFF2A89312FE9 (recette_id), INDEX IDX_C4DBFF2A806F0F5C (equipement_id), PRIMARY KEY (recette_id, equipement_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, expiration DATETIME NOT NULL, actif TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, UNIQUE INDEX UNIQ_C74F21955F37A13B (token), INDEX IDX_C74F2195FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE regime_alimentaire (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, emplacement VARCHAR(20) NOT NULL, dlc DATE DEFAULT NULL, ddm DATE DEFAULT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_4B365660FB88E14F (utilisateur_id), INDEX IDX_4B365660F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, photo_profil VARCHAR(255) DEFAULT NULL, date_inscription DATETIME NOT NULL, role JSON NOT NULL, questionnaire_complete TINYINT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE allergie_categorie ADD CONSTRAINT FK_6D37F3D67C86304A FOREIGN KEY (allergie_id) REFERENCES allergie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE allergie_categorie ADD CONSTRAINT FK_6D37F3D6BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alternative ADD CONSTRAINT FK_EFF5DFA933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alternative ADD CONSTRAINT FK_EFF5DFABB0B8227 FOREIGN KEY (produit_substitut_id) REFERENCES produit (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE article_liste ADD CONSTRAINT FK_8B4558E5E85441D8 FOREIGN KEY (liste_id) REFERENCES liste_courses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_liste ADD CONSTRAINT FK_8B4558E5F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F02FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creneau ADD CONSTRAINT FK_F9668B5F3D865311 FOREIGN KEY (planning_id) REFERENCES planning (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creneau ADD CONSTRAINT FK_F9668B5F89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE etape_recette ADD CONSTRAINT FK_12D2C04B89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favori ADD CONSTRAINT FK_EF85A2CC89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF787089312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE ingredient_categorie ADD CONSTRAINT FK_430F7E43933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient_ref (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_categorie ADD CONSTRAINT FK_430F7E43BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_regime_alimentaire ADD CONSTRAINT FK_6FCAC5E7933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient_ref (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient_regime_alimentaire ADD CONSTRAINT FK_6FCAC5E735E7D534 FOREIGN KEY (regime_id) REFERENCES regime_alimentaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE liste_courses ADD CONSTRAINT FK_189FC21DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1489312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preference_gout ADD CONSTRAINT FK_B6E9FC01FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preference_gout ADD CONSTRAINT FK_B6E9FC01CDCD81E8 FOREIGN KEY (ingredient_ref_id) REFERENCES ingredient_ref (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences ADD CONSTRAINT FK_E931A6F5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_allergie ADD CONSTRAINT FK_BF0457587CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES preferences (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_allergie ADD CONSTRAINT FK_BF0457587C86304A FOREIGN KEY (allergie_id) REFERENCES allergie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_equipement ADD CONSTRAINT FK_22F86DE87CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES preferences (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_equipement ADD CONSTRAINT FK_22F86DE8806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_regime ADD CONSTRAINT FK_9D80D5F67CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES preferences (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences_regime ADD CONSTRAINT FK_9D80D5F635E7D534 FOREIGN KEY (regime_id) REFERENCES regime_alimentaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB639060BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recette ADD CONSTRAINT FK_49BB639087998E FOREIGN KEY (origine_id) REFERENCES recette (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recette_equipement ADD CONSTRAINT FK_C4DBFF2A89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette_equipement ADD CONSTRAINT FK_C4DBFF2A806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergie_categorie DROP FOREIGN KEY FK_6D37F3D67C86304A');
        $this->addSql('ALTER TABLE allergie_categorie DROP FOREIGN KEY FK_6D37F3D6BCF5E72D');
        $this->addSql('ALTER TABLE alternative DROP FOREIGN KEY FK_EFF5DFA933FE08C');
        $this->addSql('ALTER TABLE alternative DROP FOREIGN KEY FK_EFF5DFABB0B8227');
        $this->addSql('ALTER TABLE article_liste DROP FOREIGN KEY FK_8B4558E5E85441D8');
        $this->addSql('ALTER TABLE article_liste DROP FOREIGN KEY FK_8B4558E5F347EFB');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC89312FE9');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC60BB6FE6');
        $this->addSql('ALTER TABLE coupon DROP FOREIGN KEY FK_64BF3F02FB88E14F');
        $this->addSql('ALTER TABLE creneau DROP FOREIGN KEY FK_F9668B5F3D865311');
        $this->addSql('ALTER TABLE creneau DROP FOREIGN KEY FK_F9668B5F89312FE9');
        $this->addSql('ALTER TABLE etape_recette DROP FOREIGN KEY FK_12D2C04B89312FE9');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CCFB88E14F');
        $this->addSql('ALTER TABLE favori DROP FOREIGN KEY FK_EF85A2CC89312FE9');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF787089312FE9');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870F347EFB');
        $this->addSql('ALTER TABLE ingredient_categorie DROP FOREIGN KEY FK_430F7E43933FE08C');
        $this->addSql('ALTER TABLE ingredient_categorie DROP FOREIGN KEY FK_430F7E43BCF5E72D');
        $this->addSql('ALTER TABLE ingredient_regime_alimentaire DROP FOREIGN KEY FK_6FCAC5E7933FE08C');
        $this->addSql('ALTER TABLE ingredient_regime_alimentaire DROP FOREIGN KEY FK_6FCAC5E735E7D534');
        $this->addSql('ALTER TABLE liste_courses DROP FOREIGN KEY FK_189FC21DFB88E14F');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA1489312FE9');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14FB88E14F');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6FB88E14F');
        $this->addSql('ALTER TABLE preference_gout DROP FOREIGN KEY FK_B6E9FC01FB88E14F');
        $this->addSql('ALTER TABLE preference_gout DROP FOREIGN KEY FK_B6E9FC01CDCD81E8');
        $this->addSql('ALTER TABLE preferences DROP FOREIGN KEY FK_E931A6F5FB88E14F');
        $this->addSql('ALTER TABLE preferences_allergie DROP FOREIGN KEY FK_BF0457587CCD6FB7');
        $this->addSql('ALTER TABLE preferences_allergie DROP FOREIGN KEY FK_BF0457587C86304A');
        $this->addSql('ALTER TABLE preferences_equipement DROP FOREIGN KEY FK_22F86DE87CCD6FB7');
        $this->addSql('ALTER TABLE preferences_equipement DROP FOREIGN KEY FK_22F86DE8806F0F5C');
        $this->addSql('ALTER TABLE preferences_regime DROP FOREIGN KEY FK_9D80D5F67CCD6FB7');
        $this->addSql('ALTER TABLE preferences_regime DROP FOREIGN KEY FK_9D80D5F635E7D534');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB639060BB6FE6');
        $this->addSql('ALTER TABLE recette DROP FOREIGN KEY FK_49BB639087998E');
        $this->addSql('ALTER TABLE recette_equipement DROP FOREIGN KEY FK_C4DBFF2A89312FE9');
        $this->addSql('ALTER TABLE recette_equipement DROP FOREIGN KEY FK_C4DBFF2A806F0F5C');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195FB88E14F');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660FB88E14F');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660F347EFB');
        $this->addSql('DROP TABLE allergie');
        $this->addSql('DROP TABLE allergie_categorie');
        $this->addSql('DROP TABLE alternative');
        $this->addSql('DROP TABLE article_liste');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE creneau');
        $this->addSql('DROP TABLE equipement');
        $this->addSql('DROP TABLE etape_recette');
        $this->addSql('DROP TABLE favori');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE ingredient_ref');
        $this->addSql('DROP TABLE ingredient_categorie');
        $this->addSql('DROP TABLE ingredient_regime_alimentaire');
        $this->addSql('DROP TABLE liste_courses');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE preference_gout');
        $this->addSql('DROP TABLE preferences');
        $this->addSql('DROP TABLE preferences_allergie');
        $this->addSql('DROP TABLE preferences_equipement');
        $this->addSql('DROP TABLE preferences_regime');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE recette');
        $this->addSql('DROP TABLE recette_equipement');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE regime_alimentaire');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
