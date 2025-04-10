
-- --------------------------------------------------------

--
-- Table structure for table `castle_present`
--

CREATE TABLE `castle_present` (
  `itemid` int(20) NOT NULL,
  `count` int(20) NOT NULL DEFAULT 0,
  `memo` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `castle_present`:
--
