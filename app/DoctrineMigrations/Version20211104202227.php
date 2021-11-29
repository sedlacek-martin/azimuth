<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20211104202227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE booking_bank_wire DROP FOREIGN KEY FK_8929A0B83301C60');
        $this->addSql('ALTER TABLE booking_payin_refund DROP FOREIGN KEY FK_2CD4E82D3301C60');
        $this->addSql('ALTER TABLE booking_user_address DROP FOREIGN KEY FK_B7DC90443301C60');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C63301C60');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE booking_bank_wire');
        $this->addSql('DROP TABLE booking_payin_refund');
        $this->addSql('DROP TABLE booking_user_address');
        $this->addSql('DROP TABLE listing_discount');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('DROP TABLE user_facebook');
        $this->addSql('ALTER TABLE announcement_to_user CHANGE announcement_id announcement_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE dismissedAt dismissedAt DATETIME DEFAULT NULL, CHANGE displayedAt displayedAt DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE country_information CHANGE country country VARCHAR(3) DEFAULT NULL');
        $this->addSql('DROP INDEX min_duration_idx ON listing');
        $this->addSql('DROP INDEX max_duration_idx ON listing');
        $this->addSql('DROP INDEX average_rating_idx ON listing');
        $this->addSql('DROP INDEX admin_notation_idx ON listing');
        $this->addSql('ALTER TABLE listing DROP min_duration, DROP max_duration, DROP average_rating, DROP comment_count, DROP availabilities_updated_at, DROP public, CHANGE location_id location_id INT DEFAULT NULL, CHANGE type type SMALLINT DEFAULT NULL, CHANGE certified certified TINYINT(1) DEFAULT NULL, CHANGE admin_notation admin_notation NUMERIC(3, 1) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL, CHANGE valid_from valid_from DATE DEFAULT NULL, CHANGE valid_to valid_to DATE DEFAULT NULL');
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
        $this->addSql('ALTER TABLE user_login DROP FOREIGN KEY FK_48CA3048A76ED395');
        $this->addSql('ALTER TABLE user_login CHANGE user_id user_id INT DEFAULT NULL, CHANGE ip ip VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_login ADD CONSTRAINT FK_48CA3048A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
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
        $this->addSql('ALTER TABLE user DROP phone_prefix, DROP phone, DROP country_of_residence, DROP profession, DROP iban, DROP bic, DROP bank_owner_name, DROP bank_owner_address, DROP annual_income, DROP id_card_verified, DROP nb_bookings_offerer, DROP nb_bookings_asker, DROP fee_as_asker, DROP fee_as_offerer, DROP average_rating_as_asker, DROP average_rating_as_offerer, DROP answer_delay, DROP skaut_is_id, CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE email_verified email_verified TINYINT(1) DEFAULT NULL, CHANGE trusted trusted TINYINT(1) DEFAULT NULL, CHANGE trusted_email_sent trusted_email_sent TINYINT(1) DEFAULT NULL, CHANGE mother_tongue mother_tongue VARCHAR(5) DEFAULT NULL, CHANGE createdAt createdAt DATETIME DEFAULT NULL, CHANGE updatedAt updatedAt DATETIME DEFAULT NULL, CHANGE slug slug VARCHAR(255) DEFAULT NULL, CHANGE organizationIdentifier organizationIdentifier VARCHAR(50) DEFAULT NULL, CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL, CHANGE scout_since scout_since INT DEFAULT NULL, CHANGE scout_name scout_name VARCHAR(100) DEFAULT NULL, CHANGE country country VARCHAR(3) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL, CHANGE gender gender VARCHAR(10) DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT NULL');
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

        $this->addSql('CREATE TABLE booking (id INT NOT NULL, user_id INT NOT NULL, listing_id INT NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, status SMALLINT NOT NULL, validated TINYINT(1) NOT NULL, new_booking_at DATETIME DEFAULT \'NULL\', payed_booking_at DATETIME DEFAULT \'NULL\', refused_booking_at DATETIME DEFAULT \'NULL\', canceled_asker_booking_at DATETIME DEFAULT \'NULL\', alerted_expiring TINYINT(1) NOT NULL, alerted_imminent TINYINT(1) NOT NULL, message TEXT NOT NULL COLLATE utf8_unicode_ci, time_zone_asker VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, time_zone_offerer VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', INDEX IDX_E00CEDDEA76ED395 (user_id), INDEX IDX_E00CEDDED4619D1A (listing_id), INDEX start_idx (start), INDEX end_idx (end), INDEX start_time_idx (start_time), INDEX end_time_idx (end_time), INDEX status_idx (status), INDEX validated_idx (validated), INDEX new_booking_at_idx (new_booking_at), INDEX alerted_expiring_idx (alerted_expiring), INDEX alerted_imminent_idx (alerted_imminent), INDEX created_at_idx (createdAt), INDEX updated_at_idx (updatedAt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_bank_wire (id INT NOT NULL, user_id INT NOT NULL, booking_id INT NOT NULL, status SMALLINT NOT NULL, amount NUMERIC(8, 0) NOT NULL, payed_at DATETIME DEFAULT \'NULL\', createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_8929A0B83301C60 (booking_id), INDEX IDX_8929A0B8A76ED395 (user_id), INDEX status_bbw_idx (status), INDEX created_at_bbw_idx (createdAt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_payin_refund (id INT NOT NULL, user_id INT NOT NULL, booking_id INT NOT NULL, status SMALLINT NOT NULL, amount NUMERIC(8, 0) NOT NULL, payed_at DATETIME DEFAULT \'NULL\', createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_2CD4E82D3301C60 (booking_id), INDEX IDX_2CD4E82DA76ED395 (user_id), INDEX status_pr_idx (status), INDEX created_at_pr_idx (createdAt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_user_address (id INT AUTO_INCREMENT NOT NULL, booking_id INT NOT NULL, address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, zip VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, country VARCHAR(3) NOT NULL COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_B7DC90443301C60 (booking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_discount (id INT AUTO_INCREMENT NOT NULL, listing_id INT NOT NULL, discount SMALLINT NOT NULL, from_quantity SMALLINT NOT NULL, UNIQUE INDEX discount_unique (listing_id, from_quantity), INDEX IDX_79CD674D4619D1A (listing_id), INDEX discount_idx (discount), INDEX from_quantity_idx (from_quantity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id BIGINT AUTO_INCREMENT NOT NULL, booking_id INT NOT NULL, review_by INT NOT NULL, review_to INT NOT NULL, rating SMALLINT NOT NULL, comment TEXT NOT NULL COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', INDEX IDX_794381C63301C60 (booking_id), INDEX IDX_794381C6BEDC2389 (review_by), INDEX IDX_794381C65690230F (review_to), INDEX created_at_r_idx (createdAt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type SMALLINT DEFAULT 1 NOT NULL, address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, city VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, zip VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, country VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', INDEX IDX_5543718BA76ED395 (user_id), INDEX user_address_type_idx (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_facebook (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, facebook_id VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, link VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, last_name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, first_name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, birthday DATE DEFAULT \'NULL\', address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, verified VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, location VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, location_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, hometown VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, hometown_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, gender VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, locale VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, timezone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, nb_friends VARCHAR(15) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, picture VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, createdAt DATETIME DEFAULT \'NULL\', updatedAt DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_8BF92CE0A76ED395 (user_id), INDEX facebook_id_idx (facebook_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDED4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_bank_wire ADD CONSTRAINT FK_8929A0B83301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_bank_wire ADD CONSTRAINT FK_8929A0B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_payin_refund ADD CONSTRAINT FK_2CD4E82D3301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_payin_refund ADD CONSTRAINT FK_2CD4E82DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_user_address ADD CONSTRAINT FK_B7DC90443301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE listing_discount ADD CONSTRAINT FK_79CD674D4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C63301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C65690230F FOREIGN KEY (review_to) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6BEDC2389 FOREIGN KEY (review_by) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_facebook ADD CONSTRAINT FK_8BF92CE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
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
        $this->addSql('ALTER TABLE listing ADD min_duration SMALLINT DEFAULT NULL, ADD max_duration SMALLINT DEFAULT NULL, ADD average_rating SMALLINT DEFAULT NULL, ADD comment_count INT DEFAULT NULL, ADD availabilities_updated_at DATETIME DEFAULT \'NULL\', ADD public TINYINT(1) NOT NULL, CHANGE location_id location_id INT DEFAULT NULL, CHANGE valid_from valid_from DATE DEFAULT \'NULL\', CHANGE valid_to valid_to DATE DEFAULT \'NULL\', CHANGE type type SMALLINT DEFAULT NULL, CHANGE certified certified TINYINT(1) DEFAULT \'NULL\', CHANGE admin_notation admin_notation NUMERIC(3, 1) DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX min_duration_idx ON listing (min_duration)');
        $this->addSql('CREATE INDEX max_duration_idx ON listing (max_duration)');
        $this->addSql('CREATE INDEX average_rating_idx ON listing (average_rating)');
        $this->addSql('CREATE INDEX admin_notation_idx ON listing (admin_notation)');
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
        $this->addSql('ALTER TABLE `user` ADD phone_prefix VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD phone VARCHAR(16) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD country_of_residence VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD profession VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD iban VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD bic VARCHAR(25) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD bank_owner_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD bank_owner_address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, ADD annual_income NUMERIC(10, 2) DEFAULT \'NULL\', ADD id_card_verified TINYINT(1) DEFAULT \'NULL\', ADD nb_bookings_offerer SMALLINT DEFAULT NULL, ADD nb_bookings_asker SMALLINT DEFAULT NULL, ADD fee_as_asker SMALLINT DEFAULT NULL, ADD fee_as_offerer SMALLINT DEFAULT NULL, ADD average_rating_as_asker SMALLINT DEFAULT NULL, ADD average_rating_as_offerer SMALLINT DEFAULT NULL, ADD answer_delay INT DEFAULT NULL, ADD skaut_is_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE salt salt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE last_login last_login DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE scout_name scout_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE country country VARCHAR(3) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE location location VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE gender gender VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE email_verified email_verified TINYINT(1) DEFAULT \'NULL\', CHANGE trusted trusted TINYINT(1) DEFAULT \'NULL\', CHANGE trusted_email_sent trusted_email_sent TINYINT(1) DEFAULT \'NULL\', CHANGE mother_tongue mother_tongue VARCHAR(5) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE organizationIdentifier organizationIdentifier VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE scout_since scout_since INT DEFAULT NULL, CHANGE verified_at verified_at DATETIME DEFAULT \'NULL\', CHANGE createdAt createdAt DATETIME DEFAULT \'NULL\', CHANGE updatedAt updatedAt DATETIME DEFAULT \'NULL\', CHANGE slug slug VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_invitation CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_login DROP FOREIGN KEY FK_48CA3048A76ED395');
        $this->addSql('ALTER TABLE user_login CHANGE user_id user_id INT DEFAULT NULL, CHANGE ip ip VARCHAR(60) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE user_login ADD CONSTRAINT FK_48CA3048A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_translation CHANGE translatable_id translatable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE verified_domain CHANGE memberOrganization_id memberOrganization_id INT DEFAULT NULL');
    }
}
