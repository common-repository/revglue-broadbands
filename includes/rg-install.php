<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
global $wpdb;
global $rg_db_version;
$rg_db_version = '1.0.0';
add_option("rg_db_version", $rg_db_version);
require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix.'rg_projects';
$sql= "CREATE TABLE IF NOT EXISTS `$table_name` 
(
  `rg_project_id` int(11) NOT NULL AUTO_INCREMENT,
  `subcription_id` varchar(255) NOT NULL,
  `partner_iframe_id` varchar(255) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `project` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `expiry_date` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  PRIMARY KEY (`rg_project_id`)
) $charset_collate;";
dbDelta($sql);
$table_name = $wpdb->prefix.'rg_stores'; 
$sql = "CREATE TABLE IF NOT EXISTS `$table_name` 
(
  `rg_store_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `url_key` varchar(255) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL,
  `affiliate_network` varchar(255) DEFAULT NULL,
  `affiliate_network_link` varchar(255) DEFAULT NULL,
  `store_base_currency` varchar(255) DEFAULT NULL,
  `store_base_country` varchar(255) DEFAULT NULL,
  `category_ids` varchar(128) DEFAULT NULL,
  `homepage_store_tag` enum('yes','no') NOT NULL DEFAULT 'no',
  `popular_store_tag` enum('yes','no') NOT NULL DEFAULT 'no',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','in-active') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`rg_store_id`)
) $charset_collate;";
dbDelta($sql);
$table_name = $wpdb->prefix.'rg_banner'; 
$sql= "CREATE TABLE IF NOT EXISTS `$table_name` 
(
  `rg_id` int(11) NOT NULL AUTO_INCREMENT,
  `rg_store_banner_id` int(11),
  `rg_store_id` int(11),
  `title` varchar(255) NOT NULL,
  `rg_store_name` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `placement` varchar(100) NOT NULL,
  `rg_size` varchar(50) NOT NULL,
  `banner_type` enum('local','imported') NOT NULL DEFAULT 'local',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`rg_id`)
) $charset_collate;";
dbDelta($sql);
$table_name = $wpdb->prefix.'rg_broadband'; 
$sql= "CREATE TABLE `$table_name` (
  `broadband_id` int(11) NOT NULL AUTO_INCREMENT,
  `broadband_title` varchar(255) NOT NULL,
  `rg_store_id` int(11) NOT NULL,
  `broadband_category_id` int(3) NOT NULL,
  `services` varchar(25) NOT NULL,
  `deal_type` enum('Business','Home') NOT NULL,
  `speed` varchar(25) NOT NULL,
  `speed_for_filters` float NOT NULL,
  `download_limit` varchar(25) NOT NULL,
  `setup_cost` float NOT NULL,
  `cost_per_month` varchar(100) NOT NULL,
  `cost_per_month_for_filter` float NOT NULL,
  `no_of_contract_month` int(3) NOT NULL,
  `total_contract_cost` float NOT NULL,
  `first_month_cost` float NOT NULL,
  `cost_after_first_month` float NOT NULL,
  `cost_after_x_month` float NOT NULL,
  `standard_cost` float NOT NULL,
  `upfront_cost` float NOT NULL,
  `router` enum('yes','no') NOT NULL,
  `router_detail` varchar(255) NOT NULL,
  `router_price` float NOT NULL,
  `online_discount` float NOT NULL,
  `phone_line` enum('yes','no') NOT NULL,
  `line_rental` varchar(25) NOT NULL,
  `promotion` enum('yes','no') NOT NULL,
  `promotion_detail` text NOT NULL,
  `promotion_issue_date` date NOT NULL,
  `promotion_expiry_date` date NOT NULL,
  `cashback` float NOT NULL,
  `delivery_charges` varchar(50) NOT NULL,
  `tech_support` varchar(255) NOT NULL,
  `tech_telephone` varchar(15) NOT NULL,
  `broadband_type` varchar(10) NOT NULL,
  `tv` varchar(25) NOT NULL,
  `movies` varchar(25) NOT NULL,
  `sports_channel` varchar(100) NOT NULL,
  `tv_quality` varchar(25) NOT NULL,
  `deeplink` varchar(1000) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`broadband_id`)
) $charset_collate;";
dbDelta($sql);
$table_name = $wpdb->prefix.'rg_broadband_category'; 
$sql ="CREATE TABLE `$table_name` (
 `broadband_category_id` int(11) NOT NULL AUTO_INCREMENT,
 `title` varchar(100) NOT NULL,
 `status` enum('active','inactive') NOT NULL DEFAULT 'active',
 `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `date_updated` datetime NOT NULL,
  PRIMARY KEY (`broadband_category_id`)
)  $charset_collate";
dbDelta($sql);
