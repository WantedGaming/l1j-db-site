
-- --------------------------------------------------------

--
-- Table structure for table `app_customer_normal`
--

CREATE TABLE `app_customer_normal` (
  `id` int(10) NOT NULL DEFAULT 0,
  `title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `app_customer_normal`:
--
