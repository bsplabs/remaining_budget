--
-- Database: `remaining_budget`
--

-- --------------------------------------------------------

--
-- Table structure for table `remaining_budget_value`
--

CREATE TABLE `remaining_budget_value` (
  `id` int(11) NOT NULL,
  `remaining_budget_customer_id` varchar(50) NOT NULL,
  `month` varchar(100) DEFAULT NULL,
  `year` varchar(100) DEFAULT NULL,
  `adjustment_remain` decimal(12,2) DEFAULT NULL,
  `receive` decimal(12,2) DEFAULT NULL,
  `invoice` decimal(12,2) DEFAULT NULL,
  `transfer` decimal(12,2) DEFAULT NULL,
  `ads_credit_note` decimal(12,2) DEFAULT NULL,
  `spending_invoice` decimal(12,2) DEFAULT NULL,
  `je` decimal(12,2) DEFAULT NULL,
  `free_click_cost` decimal(12,2) DEFAULT NULL,
  `free_click_cost_old` decimal(12,2) DEFAULT NULL,
  `adjustment_cash_advance` decimal(12,2) DEFAULT NULL,
  `max` decimal(12,2) DEFAULT NULL,
  `cash_advance` decimal(12,2) DEFAULT NULL,
  `remaining_ice` decimal(12,2) DEFAULT NULL,
  `wallet` decimal(12,2) DEFAULT NULL,
  `wallet_free_click_cost` decimal(12,2) DEFAULT NULL,
  `withholding_tax` decimal(12,2) DEFAULT NULL,
  `adjustment_front_end` decimal(12,2) DEFAULT NULL,
  `remaining_budget` decimal(12,2) DEFAULT NULL,
  `difference` decimal(12,2) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `remaining_budget_value`
--
ALTER TABLE `remaining_budget_value`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `remaining_budget_value`
--
ALTER TABLE `remaining_budget_value`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;