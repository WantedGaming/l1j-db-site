
-- --------------------------------------------------------

--
-- Table structure for table `tj_coupon`
--

CREATE TABLE `tj_coupon` (
  `objId` int(10) NOT NULL DEFAULT 0,
  `charId` int(10) NOT NULL DEFAULT 0,
  `itemId` int(10) NOT NULL DEFAULT 0,
  `count` int(10) NOT NULL DEFAULT 0,
  `enchantLevel` int(9) NOT NULL DEFAULT 0,
  `attrLevel` int(3) NOT NULL DEFAULT 0,
  `bless` int(3) NOT NULL DEFAULT 1,
  `lostTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=euckr COLLATE=euckr_korean_ci;

--
-- RELATIONSHIPS FOR TABLE `tj_coupon`:
--
