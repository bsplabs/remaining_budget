CREATE TABLE `remaining_budget_first_remaining` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `remain_value` DECIMAL(12,2) NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_report_status` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `media_wallet` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `withholding_tax` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `free_click_cost` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `google_spending` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `facebook_spending` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `remaining_ice` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `gl_cash_advance` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `transfer` enum('no','pending','waiting','completed') NOT NULL DEFAULT 'no',
  `type` ENUM('default', 'update') NOT NULL DEFAULT 'default',
  `overall_status` ENUM('pending','waiting','completed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_customers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `offset_acct` VARCHAR(50) NULL,
  `offset_acct_name` VARCHAR(255) NULL,
  `company` VARCHAR(50) NULL,
  `parent_id` VARCHAR(50) NULL,
  `payment_method` VARCHAR(100) NULL,
  `is_parent` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_report` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NOT NULL,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `last_month_remaining` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_remain` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_remain_note` TEXT NULL,
  `receive` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `invoice` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `transfer` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `ads_credit_note` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `spending_invoice` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_je` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_je_note` TEXT NULL,
  `adjustment_free_click_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_free_click_cost_note` TEXT NULL,
  `adjustment_free_click_cost_old` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_free_click_cost_old_note` TEXT NULL,
  `adjustment_cash_advance` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_cash_advance_note` TEXT NULL,
  `adjustment_max` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_max_note` TEXT NULL,
  `cash_advance` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `remaining_ice` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `wallet` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `wallet_free_click_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `withholding_tax` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_front_end` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `adjustment_front_end_note` TEXT NULL,
  `remaining_budget` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `difference` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `note` TEXT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_gl_cash_advance` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `posting_date` DATE NULL,
  `due_date` DATE NULL,
  `series` VARCHAR(50) NULL,
  `doc_no` VARCHAR(50) NULL,
  `trans_no` VARCHAR(50) NULL,
  `gl_code` VARCHAR(50) NULL,
  `remarks` TEXT NULL,
  `offset_acct` VARCHAR(50) NULL,
  `offset_acct_name` VARCHAR(255) NULL,
  `indicator` VARCHAR(255) NULL,
  `debit_lc` DECIMAL(12,2) NULL,
  `credit_lc` DECIMAL(12,2) NULL,
  `cumulative_balance_lc` DECIMAL(12,2) NULL,
  `series_code` VARCHAR(50) NULL,
  `month` VARCHAR(10) NULL,
  `year` VARCHAR(10) NULL,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_google_spending` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining__budget_customer_id` INT NULL,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `google_id` VARCHAR(255) NULL,
  `google_account` VARCHAR(255) NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `budget` VARCHAR(255) NULL,
  `purchase_order` TEXT NULL,
  `campaign` TEXT NULL,
  `volume` INT NULL,
  `unit` VARCHAR(255) NULL,
  `spending_total_price` DECIMAL(12,2) NULL,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_facebook_spending` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `facebook_id` VARCHAR(255) NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `billing_period` DATE NULL,
  `currency` VARCHAR(50) NULL,
  `payment_status` VARCHAR(50) NULL,
  `spending_total_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_wallet_transfer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source_remaining_budget_customer_id` INT NULL,
  `destination_remaining_budget_customer_id` INT NULL,
  `source_grandadmin_customer_id` VARCHAR(50) NULL,
  `source_grandadmin_customer_name` VARCHAR(255) NULL,
  `destination_grandadmin_customer_id` VARCHAR(50) NULL,
  `destination_grandadmin_customer_name` VARCHAR(255) NULL,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `source_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `note` TEXT NULL,
  `clearing` VARCHAR(50) NULL,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_remaining_ice` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `month` VARCHAR(10) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `service` VARCHAR(255) NULL,
  `account_id` VARCHAR(100) NULL,
  `payment_method` VARCHAR(255) NULL,
  `remaining_ice` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget_media_wallet` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `month` VARCHAR(10) NULL,
  `year` VARCHAR(10) NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `service` VARCHAR(255) NULL,
  `remaining_wallet` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `previous_clearing` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `remaining_budget_free_click_cost` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `month` VARCHAR(10) NULL,
  `year` VARCHAR(10) NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `clearing_id` VARCHAR(255) NULL,
  `pay_date` DATE NULL,
  `service` VARCHAR(100) NULL,
  `coupon` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `remaining_budget`.`remaining_budget_withholding_tax` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `remaining_budget_customer_id` INT NULL,
  `month` VARCHAR(10) NULL,
  `year` VARCHAR(10) NULL,
  `grandadmin_customer_id` VARCHAR(50) NULL,
  `grandadmin_customer_name` VARCHAR(255) NULL,
  `clearing_id` VARCHAR(50) NULL,
  `service` VARCHAR(100) NULL,
  `amount` DECIMAL(12,2) NULL,
  `wallet_insert_date` DATE NULL,
  `wait` VARCHAR(50) NULL,
  `admin_name` VARCHAR(255) NULL,
  `is_reconcile` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






