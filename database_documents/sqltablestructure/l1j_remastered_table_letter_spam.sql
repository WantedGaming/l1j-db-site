
-- --------------------------------------------------------

--
-- Table structure for table `letter_spam`
--

CREATE TABLE `letter_spam` (
  `no` int(10) NOT NULL DEFAULT 0,
  `name` varchar(16) DEFAULT NULL,
  `spamname` varchar(16) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `letter_spam`:
--
