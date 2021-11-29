<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20211105183351 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contact_category (id INT AUTO_INCREMENT NOT NULL, uri VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, recipient_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', public TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE announcement_to_user CHANGE announcement_id announcement_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE dismissedAt dismissedAt DATETIME DEFAULT NULL, CHANGE displayedAt displayedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE country_information CHANGE country country VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE listing CHANGE location_id location_id INT DEFAULT NULL, CHANGE type type SMALLINT DEFAULT NULL, CHANGE certified certified TINYINT(1) DEFAULT NULL, CHANGE admin_notation admin_notation NUMERIC(3, 1) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL, CHANGE valid_from valid_from DATE DEFAULT NULL, CHANGE valid_to valid_to DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_category CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE category_pin_id category_pin_id INT DEFAULT NULL, CHANGE root root INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_category_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_characteristic_group_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_characteristic_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_characteristic_value_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_listing_characteristic CHANGE listing_characteristic_value_id listing_characteristic_value_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_location CHANGE listing_id listing_id INT DEFAULT NULL, CHANGE zip zip VARCHAR(20) DEFAULT NULL, CHANGE route route VARCHAR(120) DEFAULT NULL, CHANGE street_number street_number VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE member_organization CHANGE country country VARCHAR(3) DEFAULT NULL, CHANGE abstract abstract VARCHAR(255) DEFAULT NULL, CHANGE user_identifier_description user_identifier_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access CHANGE user_id user_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_invitation CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_login CHANGE user_id user_id INT DEFAULT NULL, CHANGE ip ip VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE verified_domain CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_area CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_area_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_city CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_city_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_coordinate CHANGE area_id area_id INT DEFAULT NULL, CHANGE department_id department_id INT DEFAULT NULL, CHANGE city_id city_id INT DEFAULT NULL, CHANGE zip zip VARCHAR(30) DEFAULT NULL, CHANGE route route VARCHAR(200) DEFAULT NULL, CHANGE street_number street_number VARCHAR(20) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_country CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_country_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_department CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_department_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE email_verified email_verified TINYINT(1) DEFAULT NULL, CHANGE trusted trusted TINYINT(1) DEFAULT NULL, CHANGE trusted_email_sent trusted_email_sent TINYINT(1) DEFAULT NULL, CHANGE mother_tongue mother_tongue VARCHAR(5) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL, CHANGE organizationIdentifier organizationIdentifier VARCHAR(50) DEFAULT NULL, CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL, CHANGE scout_since scout_since INT DEFAULT NULL, CHANGE scout_name scout_name VARCHAR(100) DEFAULT NULL, CHANGE country country VARCHAR(3) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL, CHANGE gender gender VARCHAR(10) DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page CHANGE published published TINYINT(1) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE page_translation CHANGE translatable_id translatable_id BIGINT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE footer CHANGE published published TINYINT(1) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE footer_translation CHANGE translatable_id translatable_id BIGINT DEFAULT NULL, CHANGE url url VARCHAR(2000) DEFAULT NULL, CHANGE url_hash url_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE sender_id sender_id INT DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message_metadata CHANGE message_id message_id INT DEFAULT NULL, CHANGE participant_id participant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message_thread CHANGE listing_id listing_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE createdBy_id createdBy_id INT DEFAULT NULL, CHANGE from_date from_date DATE DEFAULT NULL, CHANGE to_date to_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE message_thread_metadata CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE participant_id participant_id INT DEFAULT NULL, CHANGE last_participant_message_date last_participant_message_date DATETIME DEFAULT NULL, CHANGE last_message_date last_message_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE contact CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE parameter CHANGE value value VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE parameter_audit CHANGE name name VARCHAR(50) DEFAULT NULL, CHANGE value value VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE revisions CHANGE username username VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contact_category');
        $this->addSql('ALTER TABLE announcement_to_user CHANGE announcement_id announcement_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE dismissedAt dismissedAt DATETIME DEFAULT \'NULL\', CHANGE displayedAt displayedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE contact CHANGE phone phone VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE country_information CHANGE country country VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE footer CHANGE published published TINYINT(1) DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE footer_translation CHANGE translatable_id translatable_id BIGINT DEFAULT NULL, CHANGE url url VARCHAR(2000) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE url_hash url_hash VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE geo_area CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_area_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE geo_city CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_city_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE geo_coordinate CHANGE area_id area_id INT DEFAULT NULL, CHANGE department_id department_id INT DEFAULT NULL, CHANGE city_id city_id INT DEFAULT NULL, CHANGE zip zip VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE route route VARCHAR(200) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE street_number street_number VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE geo_country CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_country_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE geo_department CHANGE geocoding_id geocoding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_department_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE listing CHANGE location_id location_id INT DEFAULT NULL, CHANGE valid_from valid_from DATE DEFAULT \'NULL\', CHANGE valid_to valid_to DATE DEFAULT \'NULL\', CHANGE type type SMALLINT DEFAULT NULL, CHANGE certified certified TINYINT(1) DEFAULT \'NULL\', CHANGE admin_notation admin_notation NUMERIC(3, 1) DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE listing_category CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE category_pin_id category_pin_id INT DEFAULT NULL, CHANGE root root INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_category_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE listing_characteristic_group_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_characteristic_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_characteristic_value_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_listing_characteristic CHANGE listing_characteristic_value_id listing_characteristic_value_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_location CHANGE listing_id listing_id INT DEFAULT NULL, CHANGE zip zip VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE route route VARCHAR(120) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE street_number street_number VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE listing_translation CHANGE translatable_id translatable_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE member_organization CHANGE country country VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE abstract abstract VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE user_identifier_description user_identifier_description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE message CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE sender_id sender_id INT DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE message_metadata CHANGE message_id message_id INT DEFAULT NULL, CHANGE participant_id participant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message_thread CHANGE listing_id listing_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE from_date from_date DATE DEFAULT \'NULL\', CHANGE to_date to_date DATE DEFAULT \'NULL\', CHANGE createdBy_id createdBy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message_thread_metadata CHANGE thread_id thread_id INT DEFAULT NULL, CHANGE participant_id participant_id INT DEFAULT NULL, CHANGE last_participant_message_date last_participant_message_date DATETIME DEFAULT \'NULL\', CHANGE last_message_date last_message_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page CHANGE published published TINYINT(1) DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page_access CHANGE user_id user_id INT DEFAULT NULL, CHANGE slug slug VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE page_translation CHANGE translatable_id translatable_id BIGINT DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE parameter CHANGE value value VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE parameter_audit CHANGE name name VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE value value VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE revisions CHANGE username username VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE `user` CHANGE salt salt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE last_login last_login DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE scout_name scout_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE country country VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE location location VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE gender gender VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE email_verified email_verified TINYINT(1) DEFAULT \'NULL\', CHANGE trusted trusted TINYINT(1) DEFAULT \'NULL\', CHANGE trusted_email_sent trusted_email_sent TINYINT(1) DEFAULT \'NULL\', CHANGE mother_tongue mother_tongue VARCHAR(5) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE organizationIdentifier organizationIdentifier VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE scout_since scout_since INT DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\', CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_invitation CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_login CHANGE user_id user_id INT DEFAULT NULL, CHANGE ip ip VARCHAR(60) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE verified_domain CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
    }
}
