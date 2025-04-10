
-- --------------------------------------------------------

--
-- Table structure for table `map_type`
--

CREATE TABLE `map_type` (
  `mapId` int(6) NOT NULL DEFAULT 0,
  `desc` varchar(50) DEFAULT NULL,
  `type` enum('COMBAT','SAFETY','NORMAL') NOT NULL DEFAULT 'NORMAL'
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `map_type`:
--
