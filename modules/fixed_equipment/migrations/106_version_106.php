<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
	public function up()
	{    
		$CI = &get_instance();
		if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_suppliers')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "fe_suppliers`
                ADD COLUMN `creator_id` INT(11) NULL
            ;");
        }
        
        if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_fieldsets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "fe_fieldsets`
                ADD COLUMN `creator_id` INT(11) NULL
            ;");
        }
        
        if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_status_labels')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "fe_status_labels`
                ADD COLUMN `creator_id` INT(11) NULL
            ;");
        }
        
        if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_categories')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "fe_categories`
                ADD COLUMN `creator_id` INT(11) NULL
            ;");
        }
        
        if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_depreciations')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "fe_depreciations`
                ADD COLUMN `creator_id` INT(11) NULL
            ;");
        }
	}
}


