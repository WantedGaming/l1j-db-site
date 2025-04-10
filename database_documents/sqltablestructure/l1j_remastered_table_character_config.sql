
-- --------------------------------------------------------

--
-- Table structure for table `character_config`
--

CREATE TABLE `character_config` (
  `object_id` int(10) NOT NULL DEFAULT 0,
  `length` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `character_config`:
--
