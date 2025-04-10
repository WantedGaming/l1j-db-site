
-- --------------------------------------------------------

--
-- Table structure for table `util_fighter`
--

CREATE TABLE `util_fighter` (
  `Num` int(10) UNSIGNED NOT NULL,
  `WinCount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `LoseCount` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `util_fighter`:
--
