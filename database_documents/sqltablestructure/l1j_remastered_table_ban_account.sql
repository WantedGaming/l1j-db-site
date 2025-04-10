
-- --------------------------------------------------------

--
-- Table structure for table `ban_account`
--

CREATE TABLE `ban_account` (
  `account` varchar(100) NOT NULL,
  `reason` enum('BUG_ABOUS','CHAT_ABOUS','CHEAT','ETC') NOT NULL DEFAULT 'ETC',
  `counter` int(3) NOT NULL DEFAULT 1,
  `limitTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `ban_account`:
--
