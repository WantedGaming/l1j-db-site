
-- --------------------------------------------------------

--
-- Table structure for table `weapon_damege`
--

CREATE TABLE `weapon_damege` (
  `item_id` int(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `addDamege` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- RELATIONSHIPS FOR TABLE `weapon_damege`:
--
