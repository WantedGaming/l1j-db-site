
-- --------------------------------------------------------

--
-- Table structure for table `spr_info`
--

CREATE TABLE `spr_info` (
  `spr_id` int(10) NOT NULL,
  `spr_name` varchar(200) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `shadow` int(6) NOT NULL DEFAULT 0,
  `type` int(6) NOT NULL DEFAULT 0,
  `attr` int(3) NOT NULL DEFAULT 0,
  `width` int(6) NOT NULL DEFAULT 0,
  `height` int(6) NOT NULL DEFAULT 0,
  `flying_type` int(3) NOT NULL DEFAULT 0,
  `action_count` int(10) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `spr_info`:
--
