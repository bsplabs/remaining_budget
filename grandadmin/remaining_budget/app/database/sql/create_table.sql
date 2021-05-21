CREATE TABLE `remaining_budget_close_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `remaining_budget_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `offset_acct` varchar(50) DEFAULT NULL,
  `offset_acct_name` varchar(255) DEFAULT NULL,
  `company` varchar(50) DEFAULT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `is_parent` tinyint(1) NOT NULL DEFAULT '0',
  `main_business` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grand_customer_id` (`grandadmin_customer_id`,`grandadmin_customer_name`(191)),
  KEY `offset_acct` (`offset_acct`,`offset_acct_name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_facebook_spending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `billing_period` date DEFAULT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `spending_total_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_first_remaining` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `remain_value` decimal(12,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_free_click_cost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `clearing_id` varchar(255) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `coupon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_gl_cash_advance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `series` varchar(50) DEFAULT NULL,
  `doc_no` varchar(50) DEFAULT NULL,
  `trans_no` varchar(50) DEFAULT NULL,
  `gl_code` varchar(50) DEFAULT NULL,
  `remarks` text,
  `offset_acct` varchar(50) DEFAULT NULL,
  `offset_acct_name` varchar(255) DEFAULT NULL,
  `indicator` varchar(255) DEFAULT NULL,
  `debit_lc` decimal(12,2) DEFAULT NULL,
  `credit_lc` decimal(12,2) DEFAULT NULL,
  `cumulative_balance_lc` decimal(12,2) DEFAULT NULL,
  `series_code` varchar(50) DEFAULT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_google_spending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_account` varchar(255) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `account_budget` varchar(255) DEFAULT NULL,
  `purchase_order` text,
  `campaign` text,
  `volume` int(11) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `spending_total_price` decimal(12,2) DEFAULT NULL,
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;



CREATE TABLE `remaining_budget_media_wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `remaining_wallet` decimal(12,2) NOT NULL DEFAULT '0.00',
  `previous_clearing` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`),
  KEY `customer_id` (`grandadmin_customer_id`,`grandadmin_customer_name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_remaining_ice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `account_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `remaining_ice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) NOT NULL,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `last_month_remaining` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_remain` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_remain_note` text,
  `receive` decimal(12,2) NOT NULL DEFAULT '0.00',
  `invoice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `transfer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ads_credit_note` decimal(12,2) NOT NULL DEFAULT '0.00',
  `spending_invoice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_je` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_je_note` text,
  `adjustment_free_click_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_free_click_cost_note` text,
  `adjustment_free_click_cost_old` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_free_click_cost_old_note` text,
  `adjustment_cash_advance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_cash_advance_note` text,
  `adjustment_max` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_max_note` text,
  `cash_advance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `remaining_ice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `wallet` decimal(12,2) NOT NULL DEFAULT '0.00',
  `wallet_free_click_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `withholding_tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_front_end` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_front_end_note` text,
  `remaining_budget` decimal(12,2) NOT NULL DEFAULT '0.00',
  `difference` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  `is_reconcile` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_report_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `cash_advance` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `media_wallet` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `withholding_tax` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `free_click_cost` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `google_spending` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `facebook_spending` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `remaining_ice` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `gl_cash_advance` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `transfer` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'no',
  `type` enum('default','update') NOT NULL DEFAULT 'default',
  `overall_status` enum('no','in_progress','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_wallet_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_remaining_budget_customer_id` int(11) DEFAULT NULL,
  `destination_remaining_budget_customer_id` int(11) DEFAULT NULL,
  `source_grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `source_grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `destination_grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `destination_grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `month` varchar(10) NOT NULL,
  `year` varchar(10) NOT NULL,
  `source_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` text,
  `clearing` varchar(50) DEFAULT NULL,
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_remaining_budget_customer_id`),
  KEY `destination_id` (`destination_remaining_budget_customer_id`),
  KEY `source_customer_id` (`source_grandadmin_customer_id`,`source_grandadmin_customer_name`(191)),
  KEY `destination_customer_id` (`destination_grandadmin_customer_id`,`destination_grandadmin_customer_name`(191)),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_withholding_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` int(11) DEFAULT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `grandadmin_customer_id` varchar(50) DEFAULT NULL,
  `grandadmin_customer_name` varchar(255) DEFAULT NULL,
  `clearing_id` varchar(50) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `wallet_insert_date` date DEFAULT NULL,
  `wait` varchar(50) DEFAULT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `is_reconcile` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remaining_id` (`remaining_budget_customer_id`),
  KEY `month_year` (`month`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;














