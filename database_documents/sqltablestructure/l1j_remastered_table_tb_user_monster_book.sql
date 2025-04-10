
-- --------------------------------------------------------

--
-- Table structure for table `tb_user_monster_book`
--

CREATE TABLE `tb_user_monster_book` (
  `char_id` int(10) NOT NULL,
  `book_id` int(10) NOT NULL,
  `difficulty` int(3) DEFAULT 1,
  `step` int(10) DEFAULT 0,
  `completed` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `tb_user_monster_book`:
--
