
-- --------------------------------------------------------

--
-- Table structure for table `repair_item_cost`
--

CREATE TABLE `repair_item_cost` (
  `itemId` int(11) NOT NULL DEFAULT 0,
  `name` varchar(45) DEFAULT NULL,
  `cost` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `repair_item_cost`:
--
