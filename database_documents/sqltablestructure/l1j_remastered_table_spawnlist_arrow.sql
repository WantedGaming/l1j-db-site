
-- --------------------------------------------------------

--
-- Table structure for table `spawnlist_arrow`
--

CREATE TABLE `spawnlist_arrow` (
  `npc_id` int(10) UNSIGNED NOT NULL,
  `locx` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locy` int(10) NOT NULL DEFAULT 0,
  `tarx` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tary` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `mapid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `start_delay` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `spawnlist_arrow`:
--
