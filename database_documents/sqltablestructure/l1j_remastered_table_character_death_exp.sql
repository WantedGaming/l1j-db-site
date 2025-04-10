
-- --------------------------------------------------------

--
-- Table structure for table `character_death_exp`
--

CREATE TABLE `character_death_exp` (
  `char_id` int(10) NOT NULL DEFAULT 0,
  `delete_time` datetime NOT NULL,
  `death_level` int(3) NOT NULL DEFAULT 0,
  `exp_value` int(11) NOT NULL DEFAULT 0,
  `recovery_cost` int(7) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `character_death_exp`:
--
