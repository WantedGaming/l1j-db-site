
-- --------------------------------------------------------

--
-- Table structure for table `npc_night`
--

CREATE TABLE `npc_night` (
  `npcId` int(9) NOT NULL DEFAULT 0,
  `name` varchar(50) DEFAULT NULL,
  `targetMapId` int(5) NOT NULL DEFAULT 0,
  `targetId` int(9) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `npc_night`:
--
