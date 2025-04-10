
-- --------------------------------------------------------

--
-- Table structure for table `character_quests`
--

CREATE TABLE `character_quests` (
  `char_id` int(10) UNSIGNED NOT NULL,
  `quest_id` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `quest_step` int(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `character_quests`:
--
