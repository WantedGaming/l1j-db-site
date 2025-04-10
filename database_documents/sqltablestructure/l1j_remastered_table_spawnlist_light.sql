
-- --------------------------------------------------------

--
-- Table structure for table `spawnlist_light`
--

CREATE TABLE `spawnlist_light` (
  `id` int(10) UNSIGNED NOT NULL,
  `npcid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locx` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locy` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `mapid` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `spawnlist_light`:
--
