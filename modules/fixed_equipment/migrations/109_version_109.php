<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
	public function up()
	{    
		$CI = &get_instance();
		// V1.0.9
        add_option('fe_next_serial_number', 1, 1);
        add_option('fe_serial_number_format', 1, 1);
        add_option('fe_show_customer_asset', 0);

        if (!$CI->db->field_exists('available_quantity' ,db_prefix() . 'fe_cart_detailt')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_cart_detailt`
                ADD COLUMN `available_quantity` int(11) NULL DEFAULT "0"
                ');
        }

        add_option('fe_issue_prefix', 'ISSUE', 1);
        add_option('fe_next_issue_number', 1, 1);
        add_option('fe_issue_number_format', 1, 1);


        if (!$CI->db->table_exists(db_prefix() . "fe_tickets")) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "fe_tickets` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `created_id` int(11) NULL,
                `created_type` VARCHAR(20) NULL DEFAULT 'staff',
                `client_id` int(11) NULL,
                `cart_id` int(11) NULL,
                `asset_id` int(11) NULL,
                `ticket_source` TEXT NULL,
                `assigned_id` INT(11) NULL,
                `time_spent` DECIMAL(15,2) NULL,
                `due_date` datetime NULL,
                `code` TEXT NULL,
                `ticket_subject` TEXT NULL,
                `issue_summary` TEXT NULL,
                `priority_level` TEXT NULL,
                `ticket_type` TEXT NULL,
                `internal_note` TEXT NULL,

                `last_message_time` datetime NULL,
                `last_response_time` datetime NULL,
                `first_reply_time` datetime NULL,
                `last_update_time` datetime NULL,
                `resolution` LONGTEXT NULL,
                `status` TEXT NULL,

                `datecreated` datetime NULL,
                `dateupdated` datetime NULL,
                `staffid` int(11) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (!$CI->db->table_exists(db_prefix() . "fe_ticket_action_post_internal_notes")) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "fe_ticket_action_post_internal_notes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ticket_id` int(11) NULL,
                `note_title` TEXT NULL,
                `note_details` TEXT NULL,
                `ticket_status` TEXT NULL COMMENT 'select workflow progess Resolved Closed',
                `resolution` TEXT NULL COMMENT 'Set Reply as Resolution if you want the message entered fix issue',
                `created_type` VARCHAR(20) NULL DEFAULT 'staff',
                `datecreated` datetime NULL,
                `dateupdated` datetime NULL,
                `staffid` int(11) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (!$CI->db->table_exists(db_prefix() . "fe_ticket_timeline_logs")) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "fe_ticket_timeline_logs` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `rel_id` int NULL ,
                `rel_type` varchar(100) NULL ,
                `description` mediumtext NULL,
                `additional_data` text NULL,
                `date` datetime NULL,
                `staffid` int(11) NULL,
                `full_name` varchar(100) NULL,
                `from_date` DATETIME NULL ,
                `to_date` DATETIME NULL ,
                `duration` DECIMAL(15,2) DEFAULT '0',
                `created_type` VARCHAR(200) NULL DEFAULT 'System',

                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (!$CI->db->field_exists('model_id' ,db_prefix() . 'fe_tickets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_tickets`
                ADD COLUMN `model_id` int(11) NULL
                ');
        }

    }
}


