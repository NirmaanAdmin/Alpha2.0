<?php defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
if (!$CI->db->table_exists(db_prefix() . 'cf_expenses')) {

    $CI->db->query("CREATE TABLE " . db_prefix() . "cf_expenses (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `buisness_id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `currency` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `tax` int(11) DEFAULT NULL,
  `tax2` int(11) NOT NULL DEFAULT 0,
  `reference_no` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `expense_name` varchar(191) DEFAULT NULL,
  `clientid` int(11) NOT NULL,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `billable` int(11) DEFAULT 0,
  `invoiceid` int(11) DEFAULT NULL,
  `paymentmode` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `recurring_type` varchar(10) DEFAULT NULL,
  `repeat_every` int(11) DEFAULT NULL,
  `recurring` int(11) NOT NULL DEFAULT 0,
  `cycles` int(11) NOT NULL DEFAULT 0,
  `total_cycles` int(11) NOT NULL DEFAULT 0,
  `custom_recurring` int(11) NOT NULL DEFAULT 0,
  `last_recurring_date` date DEFAULT NULL,
  `create_invoice_billable` tinyint(1) DEFAULT NULL,
  `send_invoice_to_customer` tinyint(1) NOT NULL,
  `recurring_from` int(11) DEFAULT NULL,
  `dateadded` datetime NOT NULL,
  `addedfrom` int(11) NOT NULL,
  `paid_to` varchar(30) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT 0,
  `operation` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
}

if (!$CI->db->table_exists(db_prefix() . 'cf_buisness_types')) {
    $CI->db->query("CREATE TABLE " . db_prefix() . "cf_buisness_types (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `buisness_name` varchar(200) NOT NULL,
      `buisness_color` varchar(50) DEFAULT NULL,
      `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}
if (!$CI->db->table_exists(db_prefix() . 'cf_buisness_assignee')) {
  $CI->db->query("CREATE TABLE " . db_prefix() . "cf_buisness_assignee (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `buisness_id` int(11) NOT NULL,
    `assignee_id` int(11) NOT NULL,
    `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

