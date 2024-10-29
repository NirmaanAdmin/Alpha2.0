<?php
defined('BASEPATH') or exit('No direct script access allowed');
if (!$CI->db->table_exists(db_prefix() . 'dmg_items')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "dmg_items` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(300) NOT NULL,
    `dateadded` datetime  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approve` int(11) NOT NULL DEFAULT 0,
    `version` varchar(50) NULL,
    `filetype` varchar(40) NOT NULL DEFAULT 'folder',
    `parent_id` int(11) NULL,
    `hash` varchar(32) NULL,
    `creator_id` int(11) NULL,
    `signed_by` longtext NULL,
    `tag` longtext NULL,
    `note` longtext NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
  }

  if (!$CI->db->field_exists('master_id' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `master_id` int(11) NOT NULL DEFAULT 0
    ');
  }

  if ($CI->db->field_exists('signed_by' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    MODIFY `signed_by` longtext NULL
    ');
  }

  if (!$CI->db->field_exists('ocr_language' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `document_number` varchar(70) NULL,
    ADD COLUMN `ocr_language` varchar(30) NULL,
    ADD COLUMN `custom_field` longtext NULL,
    ADD COLUMN `related_file` text NULL,
    ADD COLUMN `duedate` datetime NULL
    ');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_file_versions')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "dmg_file_versions` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` varchar(300) NOT NULL,
      `dateadded` datetime NULL,
      `version` varchar(50) NULL,
      `filetype` varchar(40) NULL,
      `parent_id` int(11) NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_custom_fields')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'dmg_custom_fields` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(300) NULL,
    `type` varchar(30) NULL,
    `option` text NULL,
    `required` INT NOT NULL DEFAULT 1,
    `default_value` text NULL,
    `date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`));');
  }


  if (!$CI->db->table_exists(db_prefix() . 'dmg_audit_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "dmg_audit_logs` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `date` datetime NULL,
    `user_id` int(11) NULL,
    `user_name` varchar(300) NULL,
    `action` varchar(300) NOT NULL,
    `item_id` int(11) NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_remiders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "dmg_remiders` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_id` int(11) NULL,
    `email` varchar(300) NOT NULL,
    `date` datetime NULL,
    `message` longtext NULL,
    `dateadded` datetime NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
  }

  create_email_template('Reminder', 'This is to remind you of a document: {link}<br>Your note:<br>{message}<br><br>Have a great day!', 'document_management', 'Reminder', 'reminder');

  if (!$CI->db->field_exists('locked' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `locked` int(11) NOT NULL DEFAULT 0
    ');
  }

  if (!$CI->db->field_exists('lock_user' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `lock_user` int(11) NULL
    ');
  }

  if (!$CI->db->field_exists('is_primary' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `is_primary` int(11) NOT NULL DEFAULT 0
    ');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_share_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "dmg_share_logs` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `item_id` int(11) NULL,
      `share_to` varchar(30) NULL,
      `customer` longtext NULL,
      `staff` longtext NULL,
      `customer_group` longtext NULL,
      `expiration` int(11) NULL,
      `expiration_date` datetime NULL,
      PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
  }

  if (!$CI->db->field_exists('permission' ,db_prefix() . 'dmg_share_logs')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_share_logs`
    ADD COLUMN `permission` varchar(30) NOT NULL DEFAULT \'preview\'
    ');
  }

  if (!$CI->db->field_exists('creator_type' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `creator_type` varchar(40) NOT NULL DEFAULT \'staff\'
    ');
  }

  if (!$CI->db->field_exists('sign_approve' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN  `sign_approve` int(11) NOT NULL DEFAULT 0
    ');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_approval_setting')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'dmg_approval_setting` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `related` VARCHAR(255) NOT NULL,
    `setting` LONGTEXT NOT NULL,
    `choose_when_approving` INT NOT NULL DEFAULT 0,
    `notification_recipient` LONGTEXT  NULL,
    `number_day_approval` INT(11) NULL,
    `departments` TEXT NULL,
    `job_positions` TEXT NULL,
    PRIMARY KEY (`id`));');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_approval_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'dmg_approval_details` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `rel_id` INT(11) NOT NULL,
    `rel_type` VARCHAR(45) NOT NULL,
    `staffid` VARCHAR(45) NULL,
    `approve` VARCHAR(45) NULL,
    `note` TEXT NULL,
    `date` DATETIME NULL,
    `approve_action` VARCHAR(255) NULL,
    `reject_action` VARCHAR(255) NULL,
    `approve_value` VARCHAR(255) NULL,
    `reject_value` VARCHAR(255) NULL,
    `staff_approve` INT(11) NULL,
    `action` VARCHAR(45) NULL,
    `sender` INT(11) NULL,
    `date_send` DATETIME NULL,
    `notification_recipient` LONGTEXT NULL,
    `approval_deadline` DATE NULL,
    PRIMARY KEY (`id`));');
  }

  add_option('dmg_allows_customers_to_manage_documents', 1);

  if (!$CI->db->field_exists('resolution' ,db_prefix() . 'dmg_items')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'dmg_items`
    ADD COLUMN `resolution` longtext NULL,
    ADD COLUMN `move_after_approval` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `show_files_metadata` INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN `folder_after_approval` INT(11) NULL
    ');
  }

  if (!$CI->db->table_exists(db_prefix() . 'dmg_approval_detail_eids')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'dmg_approval_detail_eids` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `rel_id` INT(11) NOT NULL,
    `rel_type` VARCHAR(45) NOT NULL,
    `staffid` VARCHAR(45) NULL,
    `approve` VARCHAR(45) NULL,
    `note` TEXT NULL,
    `date` DATETIME NULL,
    `approve_action` VARCHAR(255) NULL,
    `reject_action` VARCHAR(255) NULL,
    `approve_value` VARCHAR(255) NULL,
    `reject_value` VARCHAR(255) NULL,
    `staff_approve` INT(11) NULL,
    `action` VARCHAR(45) NULL,
    `sender` INT(11) NULL,
    `date_send` DATETIME NULL,
    `notification_recipient` LONGTEXT NULL,
    `approval_deadline` DATE NULL,

    `ip_address` varchar(100) NULL,
    `firstname` varchar(50) NULL,
    `lastname` varchar(50) NULL,
    `email` varchar(100) NULL,
    `date_of_signing` datetime NULL,
    PRIMARY KEY (`id`));');
  }

