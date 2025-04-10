
-- --------------------------------------------------------

--
-- Table structure for table `ai_user_buff`
--

CREATE TABLE `ai_user_buff` (
  `class` enum('lancer','fencer','warrior','illusionist','dragonknight','darkelf','wizard','elf','knight','crown') NOT NULL DEFAULT 'crown',
  `elfAttr` int(2) NOT NULL DEFAULT 0,
  `buff` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `ai_user_buff`:
--
