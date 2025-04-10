
-- --------------------------------------------------------

--
-- Table structure for table `ban_board`
--

CREATE TABLE `ban_board` (
  `number` varchar(100) NOT NULL,
  `account` varchar(50) NOT NULL,
  `registTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `ban_board`:
--
