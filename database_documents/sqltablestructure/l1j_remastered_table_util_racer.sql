
-- --------------------------------------------------------

--
-- Table structure for table `util_racer`
--

CREATE TABLE `util_racer` (
  `Num` int(10) UNSIGNED NOT NULL,
  `WinCount` int(10) NOT NULL DEFAULT 0,
  `LoseCount` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `util_racer`:
--
