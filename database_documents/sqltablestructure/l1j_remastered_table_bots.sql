
-- --------------------------------------------------------

--
-- Table structure for table `bots`
--

CREATE TABLE `bots` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `x` int(2) NOT NULL DEFAULT 0,
  `y` int(2) NOT NULL DEFAULT 0,
  `heading` int(2) NOT NULL DEFAULT 0,
  `mapId` int(10) NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `bots`:
--
