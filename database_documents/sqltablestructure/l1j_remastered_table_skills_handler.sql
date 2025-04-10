
-- --------------------------------------------------------

--
-- Table structure for table `skills_handler`
--

CREATE TABLE `skills_handler` (
  `skillId` int(9) NOT NULL DEFAULT -1,
  `name` varchar(100) DEFAULT NULL,
  `className` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `skills_handler`:
--
