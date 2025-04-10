
-- --------------------------------------------------------

--
-- Table structure for table `letter_command`
--

CREATE TABLE `letter_command` (
  `id` int(10) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `content` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `letter_command`:
--
