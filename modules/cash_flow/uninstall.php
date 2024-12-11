<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
 if ($CI->db->table_exists(db_prefix() . 'cf_expenses')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'cf_expenses`');
  }
  if ($CI->db->table_exists(db_prefix() . 'cf_buisness_types')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'cf_buisness_types`');
  }
  if ($CI->db->table_exists(db_prefix() . 'cf_buisness_assignee')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'cf_buisness_assignee`');
  }