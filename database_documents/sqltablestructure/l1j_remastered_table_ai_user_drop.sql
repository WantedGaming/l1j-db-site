
-- --------------------------------------------------------

--
-- Table structure for table `ai_user_drop`
--

CREATE TABLE `ai_user_drop` (
  `class` enum('lancer','fencer','warrior','illusionist','dragonknight','darkelf','wizard','elf','knight','crown','all') NOT NULL DEFAULT 'all',
  `itemId` int(10) NOT NULL DEFAULT 0,
  `name` varchar(100) DEFAULT NULL,
  `count` int(10) NOT NULL DEFAULT 1,
  `chance` int(3) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `ai_user_drop`:
--
