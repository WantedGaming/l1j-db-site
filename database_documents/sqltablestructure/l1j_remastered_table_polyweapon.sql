
-- --------------------------------------------------------

--
-- Table structure for table `polyweapon`
--

CREATE TABLE `polyweapon` (
  `polyId` int(5) NOT NULL DEFAULT 0,
  `weapon` enum('bow','spear','both') NOT NULL DEFAULT 'both'
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `polyweapon`:
--
