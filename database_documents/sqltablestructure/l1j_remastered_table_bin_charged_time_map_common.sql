
-- --------------------------------------------------------

--
-- Table structure for table `bin_charged_time_map_common`
--

CREATE TABLE `bin_charged_time_map_common` (
  `id` int(1) NOT NULL DEFAULT 0,
  `groups` text DEFAULT NULL,
  `multi_group_list` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `bin_charged_time_map_common`:
--
