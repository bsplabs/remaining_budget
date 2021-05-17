--
-- Database: `remaining_budget`
--

-- --------------------------------------------------------

--
-- Table structure for table `remaining_budget_status`
--

CREATE TABLE `remaining_budget_status` (
  `id` int(11) NOT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `media_wallet` tinyint(1) NOT NULL DEFAULT 0,
  `withholding_tax` tinyint(1) NOT NULL DEFAULT 0,
  `free_click_cost` tinyint(1) NOT NULL DEFAULT 0,
  `ice` tinyint(1) NOT NULL DEFAULT 0,
  `gl_revenue` tinyint(1) NOT NULL DEFAULT 0,
  `transfer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `remaining_budget_status`
--
ALTER TABLE `remaining_budget_status`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `remaining_budget_status`
--
ALTER TABLE `remaining_budget_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;