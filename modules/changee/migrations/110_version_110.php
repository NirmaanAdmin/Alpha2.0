<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'co_comments')) {
		    $CI->db->query('CREATE TABLE `' . db_prefix() . "co_comments` (
		      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		      `content` MEDIUMTEXT NULL,
		      `rel_type` VARCHAR(50) NOT NULL,
		      `rel_id` INT(11) NULL,
		      `staffid` INT(11) NOT NULL,
		      `dateadded` DATETIME NOT NULL,
		      PRIMARY KEY (`id`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
		}

		//version 1.10 Changee request detail
		if (!$CI->db->field_exists('tax' ,db_prefix() . 'co_request_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request_detail`
		      ADD COLUMN `tax` TEXT  NULL
		  ;");
		}

		//version 1.10 Changee request detail
		if (!$CI->db->field_exists('tax_rate' ,db_prefix() . 'co_request_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request_detail`
		      ADD COLUMN `tax_rate` TEXT  NULL
		  ;");
		}

		//version 1.10 Changee request detail
		if (!$CI->db->field_exists('tax_value' ,db_prefix() . 'co_request_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request_detail`
		      ADD COLUMN `tax_value` DECIMAL(15,2)  NULL
		  ;");
		}

		//version 1.10 Changee request detail
		if (!$CI->db->field_exists('total' ,db_prefix() . 'co_request_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request_detail`
		      ADD COLUMN `total` DECIMAL(15,2)  NULL
		  ;");
		}

		if (!$CI->db->field_exists('subtotal' ,db_prefix() . 'co_request')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request`
		      ADD COLUMN `subtotal` DECIMAL(15,2) NULL
		  ;");
		}

		if (!$CI->db->field_exists('total_tax' ,db_prefix() . 'co_request')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request`
		      ADD COLUMN `total_tax` DECIMAL(15,2) NULL
		  ;");
		}

		if (!$CI->db->field_exists('total' ,db_prefix() . 'co_request')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request`
		      ADD COLUMN `total` DECIMAL(15,2) NULL
		  ;");
		}

		if (!$CI->db->field_exists('sale_invoice' ,db_prefix() . 'co_request')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_request`
		      ADD COLUMN `sale_invoice` int(11) NULL
		  ;");
		}

		if (!$CI->db->field_exists('sale_invoice' ,db_prefix() . 'co_orders')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_orders`
		      ADD COLUMN `sale_invoice` int(11) NULL
		  ;");
		}

		if (!$CI->db->field_exists('tax_value' ,db_prefix() . 'co_order_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_order_detail`
		      ADD COLUMN `tax_value` DECIMAL(15,2) NULL
		  ;");
		}

		if (!$CI->db->field_exists('tax_rate' ,db_prefix() . 'co_order_detail')) { 
		  $CI->db->query('ALTER TABLE `' . db_prefix() . "co_order_detail`
		      ADD COLUMN `tax_rate` TEXT NULL
		  ;");
		}
    }
}