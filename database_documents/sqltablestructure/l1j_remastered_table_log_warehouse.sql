
-- --------------------------------------------------------

--
-- Table structure for table `log_warehouse`
--

CREATE TABLE `log_warehouse` (
  `id` int(10) NOT NULL,
  `datetime` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `type` varchar(45) NOT NULL,
  `account` varchar(45) DEFAULT NULL,
  `char_id` int(10) DEFAULT NULL,
  `char_name` varchar(45) DEFAULT NULL,
  `item_id` varchar(45) DEFAULT NULL,
  `item_name` varchar(45) DEFAULT NULL,
  `item_enchantlvl` varchar(45) DEFAULT NULL,
  `item_count` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `log_warehouse`:
--
