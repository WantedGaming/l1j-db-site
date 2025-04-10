
-- --------------------------------------------------------

--
-- Table structure for table `character_companion_buff`
--

CREATE TABLE `character_companion_buff` (
  `objid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `buff_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `character_companion_buff`:
--
