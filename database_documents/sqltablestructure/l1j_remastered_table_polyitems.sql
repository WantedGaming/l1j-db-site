
-- --------------------------------------------------------

--
-- Table structure for table `polyitems`
--

CREATE TABLE `polyitems` (
  `itemId` int(10) NOT NULL DEFAULT 0,
  `name` varchar(50) DEFAULT NULL,
  `polyId` int(6) NOT NULL DEFAULT 0,
  `duration` int(6) NOT NULL DEFAULT 1800,
  `type` enum('domination','normal') NOT NULL DEFAULT 'normal',
  `delete` enum('false','true') NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `polyitems`:
--
