--
-- Database: `remaining_budget`
--

-- --------------------------------------------------------

--
-- Table structure for table `remaining_budget_free_click_cost`
--

CREATE TABLE `remaining_budget_free_click_cost` (
  `id` int(11) NOT NULL,
  `month` varchar(10) DEFAULT NULL,
  `year` int(10) DEFAULT NULL,
  `customer_id` varchar(100) DEFAULT NULL,
  `clearing` varchar(100) DEFAULT NULL,
  `pay_date` datetime DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `coupon` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;