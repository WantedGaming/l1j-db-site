
-- --------------------------------------------------------

--
-- Table structure for table `npcaction`
--

CREATE TABLE `npcaction` (
  `npcid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `normal_action` varchar(45) NOT NULL DEFAULT '',
  `caotic_action` varchar(45) NOT NULL DEFAULT '',
  `teleport_url` varchar(45) NOT NULL DEFAULT '',
  `teleport_urla` varchar(45) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `npcaction`:
--
