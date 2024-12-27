<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'fe_depreciations')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_depreciations` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(200) NOT NULL,
		`term` double NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_locations')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_locations` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`location_name` varchar(200) NOT NULL,
		`parent` int(11) NULL,
		`manager` int(11) NULL,
		`location_currency` varchar(50) NULL,
		`address` text NULL,
		`city` varchar(50) NULL,
		`state` varchar(50) NULL,
		`country` varchar(50) NULL,
		`zip` varchar(50) NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_suppliers')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_suppliers` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`supplier_name` varchar(200) NOT NULL,
	`address` text NULL,
	`city` varchar(50) NULL,
	`state` varchar(50) NULL,
	`country` varchar(50) NULL,
	`zip` varchar(50) NULL,
	`contact_name` varchar(200) NULL,
	`phone` varchar(50) NULL,
	`fax` varchar(100) NULL,
	`email` varchar(100) NULL,
	`url` text NULL,
	`note` text NULL,
	`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_asset_manufacturers')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_asset_manufacturers` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(200) NOT NULL,
	`url` text NULL,
	`support_url` text NULL,
	`support_phone` varchar(50) NULL,
	`support_email` varchar(100) NULL,
	`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'fe_categories')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_categories` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`category_name` varchar(200) NOT NULL,
		`type` varchar(30) NULL,
		`category_eula` text NULL,
		`primary_default_eula` bit NOT NULL DEFAULT 0,
		`confirm_acceptance` bit NOT NULL DEFAULT 0,
		`send_mail_to_user` bit NOT NULL DEFAULT 0,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_models')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_models` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`model_name` varchar(200) NOT NULL,
	`manufacturer` int(11) NULL,
	`category` int(11) NULL,
	`model_no` varchar(200) NULL,
	`depreciation` int(11) NULL,
	`eol` int(11) NULL,
	`note` text NULL,
	`custom_field` text NULL,
	`may_request` bit NOT NULL DEFAULT 0,
	`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'fe_status_labels')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_status_labels` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(200) NOT NULL,
	`status_type` varchar(30) NOT NULL,
	`chart_color` varchar(30) NOT NULL,
	`note` text NULL,
	`show_in_side_nav` bit NOT NULL DEFAULT 0,
	`default_label` bit NOT NULL DEFAULT 0,
	`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_assets')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_assets` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`assets_code` VARCHAR(20) NULL,
		`assets_name` VARCHAR(255) NULL,
		`series` VARCHAR(200) NULL,
		`asset_group` INT(11) NULL,
		`asset_location` INT(11) NULL,
		`model_id` INT(11) NULL,
		`date_buy` DATE NULL,
		`warranty_period` INT(11) NULL,
		`unit_price` DECIMAL(15,2) NULL,
		`depreciation` INT(11) NULL,
		`supplier_id` INT(11) NULL,
		`order_number` VARCHAR(150) NULL,
		`description` TEXT NULL,
		`requestable` INT(11) NULL DEFAULT 0,
		`qr_code` VARCHAR(300) NULL,
		`type` VARCHAR(50) NOT NULL DEFAULT "asset",
		`status` INT(11) NOT NULL DEFAULT "1",
		`checkin_out` INT(11) NOT NULL DEFAULT "1",
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('quantity' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `quantity` INT(11) NULL,
	ADD COLUMN `min_quantity` INT(11) NULL
	');
}

if (!$CI->db->field_exists('category_id' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `category_id` INT(11) NULL,
	ADD COLUMN `product_key` text NULL,
	ADD COLUMN `seats` varchar(50) NULL,
	ADD COLUMN `model_no` varchar(80) NULL,
	ADD COLUMN `location_id` INT(11) NULL,
	ADD COLUMN `manufacturer_id` INT(11) NULL,
	ADD COLUMN `licensed_to_name` text NULL,
	ADD COLUMN `licensed_to_email` text NULL,
	ADD COLUMN `reassignable` INT(11) NOT NULL DEFAULT 0,
	ADD COLUMN `termination_date` DATE NULL,
	ADD COLUMN `expiration_date` DATE NULL,
	ADD COLUMN `purchase_order_number` VARCHAR(150) NULL,
	ADD COLUMN `maintained` INT(11) NOT NULL DEFAULT 0              
	');
}

if (!$CI->db->field_exists('item_no' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `item_no` varchar(80) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_log_assets')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_log_assets` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`admin_id` INT(11) NULL,
		`action` VARCHAR(200) NULL,
		`item_id` INT(11) NULL,
		`target` VARCHAR(200) NULL,
		`changed` VARCHAR(200) NULL,
		`to` VARCHAR(20) NULL,
		`to_id` INT(11) NULL,
		`notes` text NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_checkin_assets` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`model` VARCHAR(200) NULL,
		`asset_name` VARCHAR(200) NULL,
		`item_id` INT(11) NULL,
		`status` INT(11) NULL,
		`quantity` INT NULL,
		`checkout_to` VARCHAR(20) NULL,
		`location_id` INT(11) NULL,
		`asset_id` INT(11) NULL,
		`staff_id` INT(11) NULL,
		`checkin_date` date NULL,
		`expected_checkin_date` date NULL,
		`type` VARCHAR(50) NULL,
		`notes` text NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_seats')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_seats` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`seat_name` VARCHAR(200) NULL,
		`to` VARCHAR(20) NULL,
		`to_id` INT(11) NULL,
		`license_id` INT(11) NOT NULL,
		`status` INT(11) NOT NULL DEFAULT 1,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_model_predefined_kits')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_model_predefined_kits` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`parent_id` INT(11) NULL,
		`model_id` INT(11) NULL,
		`quantity` INT NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_asset_maintenances')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_asset_maintenances` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`asset_id` INT(11) NULL,
		`supplier_id` INT(11) NULL,
		`maintenance_type` varchar(30) NULL,
		`title` varchar(250) NULL,
		`start_date` DATE NULL,
		`completion_date` DATE NULL,
		`cost` DECIMAL(15,2) NULL,
		`notes` text NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('warranty_improvement' ,db_prefix() . 'fe_asset_maintenances')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_asset_maintenances`
	ADD COLUMN `warranty_improvement` INT(11) NOT NULL DEFAULT 0
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_approval_setting')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_approval_setting` (
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

if (!$CI->db->table_exists(db_prefix() . 'fe_approval_details')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_approval_details` (
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

if (!$CI->db->field_exists('check_status' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `check_status` INT(11) NOT NULL DEFAULT 2
	');
}

if (!$CI->db->field_exists('requestable' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `requestable` INT(11) NOT NULL DEFAULT 0
	');
}

if (!$CI->db->field_exists('request_status' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `request_status` INT(11) NOT NULL DEFAULT 0
	');
}

if (!$CI->db->field_exists('checkin_out_id' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `checkin_out_id` INT(11) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_fieldsets')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_fieldsets` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(300) NULL,
		`notes` text NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_custom_fields')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_custom_fields` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`title` varchar(300) NULL,
		`type` varchar(30) NULL,
		`option` text NULL,
		`required` INT NOT NULL DEFAULT 1,
		`default_value` text NULL,
		`fieldset_id` INT(11) NOT NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('fieldset_id' ,db_prefix() . 'fe_models')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_models`
	ADD COLUMN `fieldset_id` INT(11) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_custom_field_values')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_custom_field_values` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`title` varchar(300) NULL,
		`type` varchar(30) NULL,
		`option` text NULL,
		`required` INT NOT NULL DEFAULT 1,
		`value` text NULL,
		`fieldset_id` INT(11) NOT NULL,
		`custom_field_id` INT(11) NOT NULL,
		`asset_id` INT(11) NOT NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_audit_requests')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_audit_requests` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`title` varchar(300) NULL,
		`audit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`auditor` INT(11) NULL,
		`asset_location` INT(11) NULL,
		`model_id` INT(11) NULL,
		`asset_id` text NULL,
		`checkin_checkout_status` INT(11) NULL,
		`status` INT NOT NULL DEFAULT 0,
		`closed` INT NOT NULL DEFAULT 0,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_audit_detail_requests')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_audit_detail_requests` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`asset_id` INT(11) NULL,
		`asset_name` varchar(300) NULL,
		`type` varchar(30) NULL,
		`quantity` INT NULL,
		`adjusted` INT NULL,
		`accept` INT NOT NULL DEFAULT 0,
		`audit_id` INT(11) NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}
if (!$CI->db->field_exists('active' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `active` INT NOT NULL DEFAULT 1
	');
}
if (!$CI->db->field_exists('request_title' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `request_title` varchar(300) NULL
	');
}
add_option('fe_googlemap_api_key', '');

if (!$CI->db->field_exists('predefined_kit_id' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `predefined_kit_id` INT(11) NULL
	');
}

if (!$CI->db->field_exists('item_type' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `item_type` varchar(30) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_sign_documents')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_sign_documents` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`checkin_out_id` text NULL,
		`status` INT NOT NULL DEFAULT 1,
		`check_to_staff` INT(11) NULL,
		`reference` varchar(30) NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_signers')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_signers` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`sign_document_id` INT(11) NULL,
		`staff_id` INT(11) NULL,
		`ip_address` varchar(100) NULL,
		`date_of_signing` datetime NULL,
		`date_creator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->field_exists('firstname' ,db_prefix() . 'fe_signers')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_signers`
	ADD COLUMN `firstname` varchar(50) NULL,
	ADD COLUMN `lastname` varchar(50) NULL,
	ADD COLUMN `email` varchar(100) NULL
	');
}

if ($CI->db->table_exists(db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('UPDATE '.db_prefix().'fe_checkin_assets INNER JOIN '.db_prefix().'fe_assets ON  '.db_prefix().'fe_assets.checkin_out_id = '.db_prefix().'fe_checkin_assets.id set item_type = '.db_prefix().'fe_assets.type');
}

if (!$CI->db->field_exists('maintenance' ,db_prefix() . 'fe_audit_detail_requests')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_audit_detail_requests`
	ADD COLUMN `maintenance` INT(11) NOT NULL DEFAULT 0
	');
}
if (!$CI->db->field_exists('maintenance_id' ,db_prefix() . 'fe_audit_detail_requests')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_audit_detail_requests`
	ADD COLUMN `maintenance_id` INT(11) NOT NULL DEFAULT 0
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_depreciation_items')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_depreciation_items` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`item_id` INT(11) NOT NULL,
		`value` DECIMAL(15,2) NOT NULL DEFAULT 0,
		`date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_cron_log')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_cron_log` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`cron_name` varchar(200) NUll,
		`rel_id` int(11) NULL,
		`rel_type` varchar(45) NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if ($CI->db->field_exists('value', db_prefix() . 'fe_depreciation_items')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_depreciation_items`
        MODIFY `value` float NULL'
	);
}
add_option('fe_show_public_page', 1);

if (!$CI->db->field_exists('selling_price' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `selling_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
	ADD COLUMN `rental_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
	ADD COLUMN `for_rent` INT(11) NOT NULL DEFAULT 0,
	ADD COLUMN `for_sell` INT(11) NOT NULL DEFAULT 0,
	ADD COLUMN `renting_period` DECIMAL(15,2) NULL,
	ADD COLUMN `renting_unit` VARCHAR(10) NULL COMMENT \'hour day week month year\'
  ');
}


if (!$CI->db->table_exists(db_prefix() . 'fe_warehouse')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_warehouse` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` varchar(100) NULL,
      `name` text NULL,
      `address` text NULL,
      `city` TEXT  NULL,
      `state` TEXT  NULL,
      `zip_code` TEXT  NULL,
      `country` TEXT  NULL,
      `order` int(10) NULL,
      `display` int(1) NULL COMMENT  'display 1: display (yes)  0: not displayed (no)',
      `note` text NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

add_option('fe_inventory_receiving_prefix', 'IR', 1);
add_option('fe_next_inventory_receiving_mumber', 1, 1);

add_option('fe_inventory_delivery_prefix', 'ID', 1);
add_option('fe_next_inventory_delivery_mumber', 1, 1);

add_option('fe_packing_list_prefix', 'PL', 1);
add_option('fe_next_packing_list_number', 1, 1);


if (!$CI->db->table_exists(db_prefix() . 'fe_goods_receipt')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_receipt` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `supplier_code` varchar(100) NULL,
      `supplier_name` text NULL,
      `deliver_name` text NULL,
      `buyer_id` int(11) NULL,
      `description` text NULL,
      `pr_order_id` int(11) NULL COMMENT 'code puchase request agree',
      `date_c` date NULL ,
      `date_add` date NULL,
      `goods_receipt_code` varchar(100) NULL,
      `total_tax_money` varchar(100) NULL,
      `total_goods_money` varchar(100) NULL,
      `value_of_inventory` varchar(100) NULL,
      `invoice_no` text NULL,
      `warehouse_id` int(11) NULL,
      `total_money` varchar(100) NULL COMMENT 'total_money = total_tax_money +total_goods_money ',
      `approval` INT(11) NULL DEFAULT 0,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_goods_receipt_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_receipt_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `goods_receipt_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` text NULL,
      `warehouse_id` text NULL,
      `unit_id` text NULL,
      `quantities` text NULL,
      `unit_price` varchar(100) NULL,
      `tax` varchar(100) NULL,
      `tax_money` varchar(100) NULL,
      `goods_money` varchar(100) NULL ,
      `note` text NULL ,
      `tax_name` TEXT NULL,
      `sub_total` DECIMAL(15,2) NULL DEFAULT '0',
      `tax_rate` TEXT NULL,
      `serial_number` VARCHAR(255) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_goods_transaction_detail')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_transaction_detail` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`goods_receipt_id` int(11)  NULL COMMENT 'id_goods_receipt_id or goods_delivery_id',
	`goods_id` int(11) NOT NULL COMMENT ' is id commodity',
	`quantity` varchar(100) NULL,
	`date_add` DATETIME NULL,
	`commodity_id` int(11) NOT NULL,
	`warehouse_id` int(11) NOT NULL,
	`note`  text null,
	`status` int(2) NULL COMMENT '1:Goods receipt note 2:Goods delivery note',
	`old_quantity` varchar(100) NULL,
	`purchase_price` varchar(100),
	`price` varchar(100),
	`expiry_date` text NULL ,
	`lot_number` text NULL,
	`from_stock_name` int(11),
	`to_stock_name` int(11),
	`serial_number` VARCHAR(255),
	PRIMARY KEY (`id`, `commodity_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


//goods delivery invoice
if (!$CI->db->table_exists(db_prefix() . 'fe_goods_delivery_invoices_pr_orders')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_delivery_invoices_pr_orders` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rel_id` int(11) NULL COMMENT  'goods_delivery_id',
    `rel_type` int(11) NULL COMMENT 'invoice_id or purchase order id',
    `type` varchar(100) NULL COMMENT'invoice,  purchase_orders',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('warehouse_id' ,db_prefix() . 'fe_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_assets`
	ADD COLUMN `warehouse_id` INT(11) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_activity_log')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'fe_activity_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `rel_id` INT(11) NOT NULL,
  `rel_type` VARCHAR(45) NOT NULL,
  `staffid` INT(11) NULL,
  `date` DATETIME NULL,
  `note` TEXT NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_goods_delivery')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_delivery` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`rel_type` int(11) NULL COMMENT 'type goods delivery',
		`rel_document` int(11) NULL COMMENT 'document id of goods delivery',
		`customer_code` text NULL,
		`customer_name` varchar(100) NULL,
		`to_` varchar(100) NULL,
		`address` varchar(100) NULL,
		`description` text NULL COMMENT 'the reason delivery',
		`staff_id` int(11) NULL COMMENT 'salesman',
		`date_c` date NULL ,
		`date_add` date NULL,
		`goods_delivery_code` varchar(100) NULL COMMENT 'số chứng từ xuất kho',
		`approval` INT(11) NULL DEFAULT 0 COMMENT 'status approval ',
		`addedfrom` INT(11) ,
		`total_money` varchar(200) NULL,
		`warehouse_id` int(11) NULL,
		`total_discount` varchar(100),
		`after_discount` varchar(100),
		`invoice_id` varchar(100),
		`project` TEXT  NULL,
		`type` TEXT  NULL,
		`department` int(11)  NULL,
		`requester` int(11)  NULL,
		`invoice_no` text NULL,
		`pr_order_id` int(11) NULL,
		`type_of_delivery` VARCHAR(100)  NULL DEFAULT 'total',
		`additional_discount` DECIMAL(15,2) NULL DEFAULT '0',
		`sub_total` DECIMAL(15,2) NULL DEFAULT '0',
		`delivery_status` VARCHAR(100)  NULL DEFAULT 'ready_for_packing',
		`shipping_fee` DECIMAL(15,2) NULL DEFAULT '0.00',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}



if (!$CI->db->table_exists(db_prefix() . 'fe_goods_delivery_detail')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_delivery_detail` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `goods_delivery_id` int(11) NOT NULL,
      `commodity_code` varchar(100) NULL,
      `commodity_name` text NULL,
      `warehouse_id` text NULL,
      `unit_id` text NULL,
      `quantities` text NULL,
      `unit_price` varchar(100) NULL,
      `total_money` varchar(200) NULL,
      `discount` varchar(100),
      `discount_money` varchar(100),
      `available_quantity` varchar(100),
      `tax_id` varchar(100),
      `total_after_discount` varchar(100),
      `expiry_date` text  NULL ,
      `lot_number` text NULL,
      `guarantee_period` text  NULL ,
      `tax_rate` TEXT NULL,
      `tax_name` TEXT NULL,
      `sub_total` DECIMAL(15,2) NULL DEFAULT '0',
      `packing_qty` DECIMAL(15,2) NULL DEFAULT '0.00',
      `serial_number` VARCHAR(255) NULL,
      `note` text NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_packing_lists')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_packing_lists` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `delivery_note_id` INT(11) NULL,
    `packing_list_number` VARCHAR(100) NULL,
    `packing_list_name` VARCHAR(200) NULL,
    `width` DECIMAL(15,2) NULL DEFAULT '0.00',
    `height` DECIMAL(15,2) NULL DEFAULT '0.00',
    `lenght` DECIMAL(15,2) NULL DEFAULT '0.00',
    `weight` DECIMAL(15,2) NULL DEFAULT '0.00',
    `volume` DECIMAL(15,2) NULL DEFAULT '0.00',
    `clientid` INT(11) NULL,
    `subtotal` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `additional_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `billing_street` varchar(200) DEFAULT NULL,
    `billing_city` varchar(100) DEFAULT NULL,
    `billing_state` varchar(100) DEFAULT NULL,
    `billing_zip` varchar(100) DEFAULT NULL,
    `billing_country` int(11) DEFAULT NULL,
    `shipping_street` varchar(200) DEFAULT NULL,
    `shipping_city` varchar(100) DEFAULT NULL,
    `shipping_state` varchar(100) DEFAULT NULL,
    `shipping_zip` varchar(100) DEFAULT NULL,
    `shipping_country` int(11) DEFAULT NULL,
    `client_note` TEXT NULL,
    `admin_note` TEXT NULL,
    `approval` INT(11) NULL DEFAULT 0,
    `datecreated` DATETIME NULL,
    `staff_id` INT(11) NULL,
    `type_of_packing_list` VARCHAR(100) NULL DEFAULT 'total',
    `delivery_status` VARCHAR(100) NULL DEFAULT 'wh_ready_to_deliver',
    `shipping_fee` DECIMAL(15,2) NULL DEFAULT '0.00',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_packing_list_details')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_packing_list_details` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `packing_list_id` INT(11) NOT NULL,
    `delivery_detail_id` INT(11) NULL,
    `commodity_code` INT(11) NULL,
    `commodity_name` TEXT NULL,
    `quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
    `unit_id` INT(11) NULL,
    `unit_price` DECIMAL(15,2) NULL DEFAULT '0.00',
    `sub_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `tax_id`  TEXT NULL,
    `tax_rate`  TEXT NULL,
    `tax_name`  TEXT NULL,
    `total_amount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `discount_total` DECIMAL(15,2) NULL DEFAULT '0.00',
    `total_after_discount` DECIMAL(15,2) NULL DEFAULT '0.00',
    `serial_number` VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_goods_delivery_activity_log')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_delivery_activity_log` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`rel_id` int NULL ,
	`rel_type` varchar(100) NULL ,
	`description` mediumtext NULL,
	`additional_data` text NULL,
	`date` datetime NULL,
	`staffid` int(11) NULL,
	`full_name` varchar(100) NULL,

	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'fe_omni_shipments')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_omni_shipments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `cart_id` INT(11) NULL,
    `goods_delivery_id` INT(11) NULL,
    `shipment_hash` VARCHAR(32) NULL,
    `order_id` INT(11) NULL DEFAULT '0',
    `shipment_number` VARCHAR(100) NULL,
    `planned_shipping_date` DATETIME NULL,
    `shipment_status` VARCHAR(50) NULL,
    `datecreated` DATETIME NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}



if (!$CI->db->table_exists(db_prefix() . 'fe_cart')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_cart` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`id_contact` int(11) NOT NULL,
		`name` varchar(120) NOT NULL,
		`address` varchar(250) NOT NULL,
		`phone_number` varchar(20) NOT NULL,
		`voucher` varchar(100) NOT NULL,
		`status` int(11) null DEFAULT 0,
		`complete` int(11) null DEFAULT 0,
		`order_number` varchar(100) NULL,
		`channel_id` int(11) NULL,
		`channel` varchar(150) NULL,
		`first_name` varchar(60) NULL,
		`last_name` varchar(60) NULL,
		`email` varchar(150) NULL,
		`hash` VARCHAR(32) NULL,
		`tax_id` TEXT NULL,
		`tax_rate` TEXT NULL,
		`tax_name` TEXT NULL,
		`unit_id` int(11) NULL,
		`unit_name` VARCHAR(255) NULL,
		`add_discount` DECIMAL(15,2) DEFAULT '0.00',
		`company` varchar(150) null,                  
		`phonenumber` varchar(15) null,                 
		`city` varchar(50) null,
		`state` varchar(50) null,                  
		`country` varchar(50) null,
		`zip` varchar(50) null,          
		`billing_street` varchar(150) null,                 
		`billing_city` varchar(50) null, 
		`billing_state` varchar(50) null,                 
		`billing_country` varchar(50) null,
		`billing_zip` varchar(50) null,
		`shipping_street` varchar(150) null,
		`shipping_city` varchar(50) null,
		`shipping_state` varchar(50) null,                
		`shipping_country` varchar(50) null,
		`shipping_zip` varchar(50) null,
		`userid` int(11) null,                
		`notes` text null,                
		`reason` varchar(250) NULL,
		`admin_action` int NULL DEFAULT 0,
		`discount` varchar(250) NULL,
		`discount_type` int NULL DEFAULT 0,
		`total` varchar(250) NULL,
		`sub_total` varchar(250) NULL,
		`discount_total` DECIMAL(15,2) NOT NULL DEFAULT 0,
		`invoice` varchar(250) NOT NULL DEFAULT '',
		`number_invoice` varchar(250) NOT NULL DEFAULT '',
		`stock_export_number` varchar(250) NOT NULL DEFAULT '',
		`create_invoice` varchar(5) NOT NULL DEFAULT 'off',
		`stock_export` varchar(5) NOT NULL DEFAULT 'off',
		`customers_pay` DECIMAL(15,2) NOT NULL DEFAULT 0,
		`amount_returned` DECIMAL(15,2) NOT NULL DEFAULT 0,
		`tax` DECIMAL(15,2) NOT NULL DEFAULT 0,
		`seller` int(11) NULL,
		`staff_note` text null,                  
		`payment_note` text null,                 
		`allowed_payment_modes` varchar(200) null,                 
		`warehouse_id` INT null,                 
		`shipping` DECIMAL(15,2) not null default '0.00',                
		`payment_method_title` varchar(250) null,                
		`discount_type_str` text null,
		`discount_percent` DECIMAL(15,2) null,
		`adjustment` DECIMAL(15,2) null,
		`currency` INT(11) null,
		`terms` TEXT null,
		`shipping_tax` DECIMAL(15,2) null,
		`enable` int not null default 1,
		`duedate` date NULL,
		`shipping_tax_json` varchar(150) NULL,
		`discount_voucher` varchar(150) NULL,
		`original_order_id` int(11) NULL,
		`return_reason` longtext NULL,
		`approve_status` int(11) NOT NULL DEFAULT 0,
		`process_invoice` varchar(5) NOT NULL DEFAULT 'off',
		`stock_import_number` int(11) NOT NULL DEFAULT 0,
		`fee_for_return_order` DECIMAL(15,2) NULL,
		`estimate_id` int(11) NULL,
		`shipping_form` VARCHAR(50) null DEFAULT 'fixed',   
		`shipping_value` DECIMAL(15,2) null DEFAULT '0.00', 
		`type` VARCHAR(30) NOT NULL DEFAULT 'order', 
		`datecreator` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_cart_detailt')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_cart_detailt` (
	`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL,
	`quantity` int(11) NOT NULL,
	`classify` varchar(30) NULL,      
	`cart_id` int(11) NOT NULL,
	`product_name` VARCHAR(150) NULL,
	`prices` DECIMAL(15,2) NULL,
	`long_description` text NULL,
	`sku` text not null,                
	`percent_discount`  float not null,                
	`prices_discount`  DECIMAL(15,2) not null,                
	`tax` text NULL,
	`tax_id` TEXT NULL,
	`tax_rate` TEXT NULL,
	`tax_name` TEXT NULL,
	`unit_id` int(11) NULL,
	`unit_name` VARCHAR(255) NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('pickup_time', db_prefix() . 'fe_cart_detailt')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_cart_detailt`
		ADD COLUMN `pickup_time` VARCHAR(10) NULL,
		ADD COLUMN `dropoff_time` VARCHAR(10) NULL,
		ADD COLUMN `rental_start_date` DATE NULL,
		ADD COLUMN `rental_end_date` DATE NULL,
		ADD COLUMN `number_date` INT(11) NULL,
		ADD COLUMN `rental_value` DECIMAL(15,2) NULL,
		ADD COLUMN `status` INT(11) NULL,
		ADD COLUMN `renting_period` DECIMAL(15,2) NULL,
		ADD COLUMN `renting_unit` VARCHAR(10) NULL COMMENT 'hour day week month year'
		;");
}

if (!$CI->db->table_exists(db_prefix() . 'fe_refunds')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_refunds` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `order_id` INT(11) NULL,
      `staff_id` INT(11) NULL,
      `refunded_on` date NULL,
      `payment_mode` varchar(40) NULL,
      `note` text NULL,
      `amount` decimal(15,2) NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_asset_manufacturers')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_asset_manufacturers`
		ADD COLUMN `creator_id` INT(11) NULL
	;");
}

if (!$CI->db->field_exists('creator_id', db_prefix() . 'fe_models')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_models`
		ADD COLUMN `creator_id` INT(11) NULL
	;");
}

if (!$CI->db->field_exists('warehouse_id' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `warehouse_id` int(11) NULL
	');
}

if (!$CI->db->field_exists('warehouse_id' ,db_prefix() . 'fe_seats')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_seats`
	ADD COLUMN `warehouse_id` int(11) NULL
	');
}

if (!$CI->db->field_exists('customer_id' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `customer_id` int(11) NULL
	');
}

if (!$CI->db->field_exists('return_reason_type' ,db_prefix() . 'fe_cart')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_cart`
	ADD COLUMN `return_reason_type` varchar(50) NULL
	');
}

if (!$CI->db->field_exists('return_type' ,db_prefix() . 'fe_cart')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_cart`
	ADD COLUMN `return_type` varchar(30) NULL
	');
}

if (!$CI->db->field_exists('from_order' ,db_prefix() . 'fe_goods_receipt')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_goods_receipt`
	ADD COLUMN `from_order` int(11) NULL
	');
}

if (!$CI->db->table_exists(db_prefix() . 'fe_goods_transaction_details')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_goods_transaction_details` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`rel_type` varchar(100) NULL COMMENT 'goods_receipt or goods_delivery or loss_adjustment or internal_delivery',
		`rel_id` int(11) NOT NULL COMMENT ' goods_receipt_id or goods_delivery_id or loss_adjustment_id or internal_delivery_id',
		`rel_id_detail` int(11) NOT NULL COMMENT ' goods_receipt_id or goods_delivery_id or loss_adjustment_id or internal_delivery_id',
		`item_id` int(11) NOT NULL,
		`old_quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
		`quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
		`rate` DECIMAL(15,2) NULL DEFAULT '0.00',
		`expiry_date` date NULL ,
		`lot_number` text NULL ,
		`from_warehouse_id`INT(11) NULL,
		`to_warehouse_id`INT(11) NULL,
		`date_add` DATETIME NULL,
		`added_from_id` int(11) NULL,
		`added_from_type` varchar(30) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('from_order' ,db_prefix() . 'fe_audit_requests')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_audit_requests`
	ADD COLUMN `from_order` int(11) NULL
	');
}

if (!$CI->db->field_exists('audit_id' ,db_prefix() . 'fe_cart')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_cart`
	ADD COLUMN `audit_id` int(11) NULL,
	ADD COLUMN `maintenance_id` int(11) NULL
	');
}


if (!$CI->db->field_exists('maintenance_id', db_prefix() . 'fe_cart_detailt')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_cart_detailt`
		ADD COLUMN `maintenance_id` INT(11) NULL
	;");
}

if (!$CI->db->field_exists('estimate_id', db_prefix() . 'fe_cart_detailt')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_cart_detailt`
		ADD COLUMN `estimate_id` INT(11) NULL
	;");
}

if (!$CI->db->field_exists('credit_note_id' ,db_prefix() . 'fe_cart')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_cart`
	ADD COLUMN `credit_note_id` int(11) NULL
  ');
}

if (!$CI->db->field_exists('estimate_id', db_prefix() . 'fe_cart_detailt')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_cart_detailt`
		ADD COLUMN `estimate_id` INT(11) NULL
	;");
}

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

if (!$CI->db->field_exists('sales_order_reference', db_prefix() . 'fe_packing_lists')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . "fe_packing_lists`
		ADD COLUMN `sales_order_reference` varchar(100) NULL
	;");
}
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

if (!$CI->db->table_exists(db_prefix() . 'fe_assign_asset_predefined_kits')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_assign_asset_predefined_kits` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NULL,
		`assign_data` text NULL DEFAULT '',
		`parent_id` int(11) NULL,
		`datecreated` DATETIME NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('project_id' ,db_prefix() . 'fe_checkin_assets')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
	ADD COLUMN `project_id` int(11) NULL
	');
}

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
