
-- --------------------------------------------------------

--
-- Table structure for table `robot_fishing`
--

CREATE TABLE `robot_fishing` (
  `x` int(8) DEFAULT NULL,
  `y` int(8) DEFAULT NULL,
  `mapid` int(5) DEFAULT NULL,
  `heading` int(5) DEFAULT NULL,
  `fishingX` int(8) DEFAULT NULL,
  `fishingY` int(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `robot_fishing`:
--
