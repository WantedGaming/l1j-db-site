
-- --------------------------------------------------------

--
-- Table structure for table `spawnlist_clandungeon`
--

CREATE TABLE `spawnlist_clandungeon` (
  `id` int(2) UNSIGNED NOT NULL,
  `type` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `stage` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(45) NOT NULL DEFAULT '',
  `npc_templateid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `count` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `boss` enum('true','false') DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `spawnlist_clandungeon`:
--
