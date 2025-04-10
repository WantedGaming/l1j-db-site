
-- --------------------------------------------------------

--
-- Table structure for table `app_engine_log`
--

CREATE TABLE `app_engine_log` (
  `id` int(10) NOT NULL DEFAULT 0,
  `account` varchar(50) NOT NULL,
  `engine` varchar(100) NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `app_engine_log`:
--
