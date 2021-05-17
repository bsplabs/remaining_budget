--
-- Table structure for table `remaining_budget_gl_cash_advance`
--

CREATE TABLE `remaining_budget_gl_cash_advance` (
  `id` int(11) NOT NULL,
  `remaining_budget_customer_id` varchar(50) DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `series` varchar(100) DEFAULT NULL,
  `doc_no` varchar(100) DEFAULT NULL,
  `trans_no` varchar(100) DEFAULT NULL,
  `gl_code` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `offset_acct` varchar(100) DEFAULT NULL,
  `offset_acct_name` varchar(255) DEFAULT NULL,
  `indicator` varchar(255) DEFAULT NULL,
  `debit_lc` decimal(12,2) DEFAULT NULL,
  `credit_lc` decimal(12,2) DEFAULT NULL,
  `cumulative_balance_lc` decimal(12,2) DEFAULT NULL,
  `series_code` varchar(100) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `remaining_budget_gl_cash_advance`
--
ALTER TABLE `remaining_budget_gl_cash_advance`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `remaining_budget_gl_cash_advance`
--
ALTER TABLE `remaining_budget_gl_cash_advance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;