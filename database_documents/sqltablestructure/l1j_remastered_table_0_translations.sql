
-- --------------------------------------------------------

--
-- Table structure for table `0_translations`
--

CREATE TABLE `0_translations` (
  `id` int(11) NOT NULL,
  `text_korean` text NOT NULL,
  `text_english` text NOT NULL,
  `source` varchar(200) DEFAULT NULL,
  `line_number` int(11) DEFAULT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `0_translations`:
--
