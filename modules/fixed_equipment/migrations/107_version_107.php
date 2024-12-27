<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
	public function up()
	{    
		$CI = &get_instance();
		if (!$CI->db->field_exists('creator_id' ,db_prefix() . 'fe_checkin_assets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
            ADD COLUMN `creator_id` INT(11) NULL
            ');
        }
        if (!$CI->db->field_exists('creator_id' ,db_prefix() . 'fe_audit_requests')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_audit_requests`
            ADD COLUMN `creator_id` INT(11) NULL
            ');
        }
        if (!$CI->db->field_exists('creator_id' ,db_prefix() . 'fe_goods_receipt')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_goods_receipt`
            ADD COLUMN `creator_id` int(11) NULL
            ');
        }

	}
}


