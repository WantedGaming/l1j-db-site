
-- --------------------------------------------------------

--
-- Table structure for table `house`
--

CREATE TABLE `house` (
  `house_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `house_name` varchar(45) NOT NULL DEFAULT '',
  `house_area` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `location` varchar(45) NOT NULL DEFAULT '',
  `keeper_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_on_sale` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_purchase_basement` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tax_deadline` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `house`:
--
