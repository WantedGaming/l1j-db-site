
-- --------------------------------------------------------

--
-- Table structure for table `character_skills_active`
--

CREATE TABLE `character_skills_active` (
  `char_obj_id` int(10) NOT NULL DEFAULT 0,
  `skill_id` int(10) NOT NULL DEFAULT -1,
  `skill_name` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `character_skills_active`:
--
