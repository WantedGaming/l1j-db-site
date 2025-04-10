
-- --------------------------------------------------------

--
-- Table structure for table `proto_packet`
--

CREATE TABLE `proto_packet` (
  `code` varchar(6) NOT NULL,
  `code_val` int(6) NOT NULL DEFAULT 0,
  `className` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `proto_packet`:
--
