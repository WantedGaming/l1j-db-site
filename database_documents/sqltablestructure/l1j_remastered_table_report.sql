
-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `target` varchar(100) NOT NULL,
  `reporter` varchar(100) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `report`:
--
