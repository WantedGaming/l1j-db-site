
-- --------------------------------------------------------

--
-- Table structure for table `weapon_skill_spell_def`
--

CREATE TABLE `weapon_skill_spell_def` (
  `id` int(10) NOT NULL,
  `def_dmg` int(5) DEFAULT NULL,
  `def_ratio` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci ROW_FORMAT=COMPACT;

--
-- RELATIONSHIPS FOR TABLE `weapon_skill_spell_def`:
--
