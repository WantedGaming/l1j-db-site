
-- --------------------------------------------------------

--
-- Table structure for table `item_click_message`
--

CREATE TABLE `item_click_message` (
  `itemId` int(10) NOT NULL,
  `type` enum('true','false') NOT NULL DEFAULT 'false',
  `msg` varchar(500) DEFAULT NULL,
  `delete` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `item_click_message`:
--
