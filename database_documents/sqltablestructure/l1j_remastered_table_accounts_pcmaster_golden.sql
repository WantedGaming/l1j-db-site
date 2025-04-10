
-- --------------------------------------------------------

--
-- Table structure for table `accounts_pcmaster_golden`
--

CREATE TABLE `accounts_pcmaster_golden` (
  `account_name` varchar(50) NOT NULL,
  `index_id` int(1) NOT NULL DEFAULT 0,
  `type` int(1) NOT NULL DEFAULT 1,
  `grade` blob DEFAULT NULL,
  `remain_time` int(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `accounts_pcmaster_golden`:
--
