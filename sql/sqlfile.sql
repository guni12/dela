-- Setup
SET NAMES utf8;

--
-- Table structure for table `comm` and table `user`
--

DROP TABLE IF EXISTS `comm`;
DROP TABLE IF EXISTS `user`;

CREATE TABLE `comm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(80) COLLATE utf8_swedish_ci NOT NULL,
  `title` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  `comment` varchar(1000) COLLATE utf8_swedish_ci NOT NULL,
  `parentid` varchar(80) COLLATE utf8_swedish_ci DEFAULT NULL,
  `iscomment` tinyint(1) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `hasvoted` varchar(1000) COLLATE utf8_swedish_ci DEFAULT NULL,
  `accept` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;


--
-- Dumping data for table `comm`
--

INSERT INTO `comm` VALUES 
(1,'1','Huvudsäkring','{\"frontmatter\":{\"title\":\"Huvuds\\u00e4kring\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Jag har t\\u00e4nkt att k\\u00f6pa en Nissan Leaf elbil med 40 mils r\\u00e4ckvidd! Hur kan jag ladda den hemma p\\u00e5 garageuppfarten?<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-24 20:45:33',NULL),
(2,'6','Tjockskallar','{\"frontmatter\":{\"title\":\"Tjockskallar\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Eln\\u00e4tsbolagen \\u00e4r f\\u00f6r dumma f\\u00f6r att h\\u00e5lla p\\u00e5 med smarta eln\\u00e4t<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-24 20:57:16',NULL),
(3,'8','Vi är inte alls tjockskalliga','{\"frontmatter\":{\"title\":\"Vi \\u00e4r inte alls tjockskalliga\",\"tags\":\"answer\"},\"text\":\"<p>tror jag<\\/p>\\n\"}','2',NULL,NULL,NULL,NULL,'2018-01-24 21:00:35',NULL),
(4,'8','Vi är inte alls tjockskalliga','{\"frontmatter\":{\"title\":\"Vi \\u00e4r inte alls tjockskalliga\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>fast - en del \\u00e4r tjockskalliga<\\/p>\\n\"}','2',1,NULL,NULL,NULL,'2018-01-24 21:02:03','2018-01-25 16:46:17'),
(5,'2','Problem med luftvärmepump','{\"frontmatter\":{\"title\":\"Problem med luftv\\u00e4rmepump\",\"tags\":[null,null,null,\"heat\"]},\"text\":\"<p>Jag f\\u00f6rs\\u00f6ker styra min luftv\\u00e4rmepump via tillverkarens app - Gree smart, men p\\u00e5 sista tiden har det inte fungerat. Jag kan styra via mitt tr\\u00e5dl\\u00f6sa n\\u00e4tverk. Jag vet att internet fungerar. F\\u00f6re jul gick det bra.<\\/p>\\n\\n<p>Nu s\\u00e4ger appen Network access delay. Please check network again&#8230;???<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 14:59:35',NULL),
(6,'2','Philips hue i badrum?','{\"frontmatter\":{\"title\":\"Philips hue i badrum?\",\"tags\":[null,null,\"light\",null]},\"text\":\"<p>Ska renovera mitt badrum och vill ha spotlights i taket. Finns det philips hue lampor med tillr\\u00e4ckligt h\\u00f6g ip-klass?<\\/p>\\n\"}',NULL,NULL,NULL,NULL,85,'2018-01-25 15:05:41',NULL),
(7,'7','Inte lämpliga','{\"frontmatter\":{\"title\":\"Inte l\\u00e4mpliga\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Jag har Philips Hue GU10 downlights i hallen och de har ingen utbytbar ljusk\\u00e4lla. De kr\\u00e4ver ocks\\u00e5 stort inbyggnadsdjup - kan vara v\\u00e4rt att kolla upp f\\u00f6rst.<\\/p>\\n\"}','6',NULL,1,'[0,2]',NULL,'2018-01-25 15:09:13','2018-01-25 17:46:46'),
(8,'2','Tack','{\"frontmatter\":{\"title\":\"Tack\",\"tags\":\"comment\"},\"text\":\"<p>Precis s\\u00e5n input jag vill ha!!!<\\/p>\\n\"}','7',1,NULL,NULL,NULL,'2018-01-25 15:10:03',NULL),
(9,'7','Beslutsångest','{\"frontmatter\":{\"title\":\"Besluts\\u00e5ngest\",\"tags\":[null,\"safety\",\"light\",null]},\"text\":\"<p>Jag har k\\u00f6pt hem lite nya grejer - phulips hue lampor och larm fr\\u00e5n aqara bl.a.\\nSka man k\\u00f6ra genom Xiaomi&#8217;s gateway eller genom Raspberry?<\\/p>\\n\\n<p>F\\u00f6rdelen med RP \\u00e4r att jag slipper ha en kinesisk 240v-enhet p\\u00e5 dygnet runt, men det skanas st\\u00f6d f\\u00f6r brandlarmet och det kr\\u00e4vs en del meck f\\u00f6r att para lamprona utan GW.\\nTredje m\\u00f6jligheten&#8230; g\\u00f6ra ingenting.<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 15:25:43',NULL),
(10,'3','Gateway','{\"frontmatter\":{\"title\":\"Gateway\",\"tags\":\"answer\"},\"text\":\"<p>K\\u00f6r med Gateway. Den funkar hur bra som helst. Xiaomi har h\\u00f6g kvalitet p\\u00e5 sina grejer s\\u00e5 skulle s\\u00e4ga att det \\u00e4r l\\u00e5g risk f\\u00f6r brand pga elfel.<\\/p>\\n\\n<p>Du b\\u00f6r dock byta ut den medf\\u00f6ljande adaptern, den verkar livsfarlig.<\\/p>\\n\"}','9',NULL,NULL,NULL,NULL,'2018-01-25 15:29:09',NULL),
(11,'3','Ellås till ytterdörren','{\"frontmatter\":{\"title\":\"Ell\\u00e5s till ytterd\\u00f6rren\",\"tags\":[null,\"safety\",\"light\",null]},\"text\":\"<p>Vi ska k\\u00f6pa radhus och vill ha ett ell\\u00e5s p\\u00e5 ytterd\\u00f6rren typ Yale - integrerat med larm. Det ska inte vara securitas.<\\/p>\\n\\n<p>Vill ha s\\u00e5 att n\\u00e4r man l\\u00e5ser upp d\\u00f6rren ska larmet st\\u00e4ngas av och ljuset g\\u00e5 p\\u00e5. Vill \\u00e4ven kunna styra p\\u00e5 distans.<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 15:40:37',NULL),
(12,'5','Puzzelbitar','{\"frontmatter\":{\"title\":\"Puzzelbitar\",\"tags\":\"answer\"},\"text\":\"<p>Kan bara ge tips p\\u00e5 delar hellre \\u00e4n helhet. Med hue och harmony l\\u00f6ser ni lamporna n\\u00e4r andra aktiviteter drar ig\\u00e5n, kolla p\\u00e5 tv t.ex.<\\/p>\\n\"}','11',NULL,NULL,NULL,NULL,'2018-01-25 15:43:35',NULL),
(13,'5','Kanske','{\"frontmatter\":{\"title\":\"Kanske\",\"tags\":\"answer\"},\"text\":\"<p>starta om routern i sommarstugan? Vilket ip f\\u00f6rs\\u00f6ker du n\\u00e5? Det kanske bara g\\u00e5r att n\\u00e5 inom stugan?<\\/p>\\n\\n<p>Skaffa vpn server s\\u00e5 kommer du \\u00e5t via insidan av routern. Telldus kommunicerar till molnet och du till molnet, s\\u00e5 det \\u00e4r inte samma sak.<\\/p>\\n\"}','83',NULL,NULL,NULL,NULL,'2018-01-25 15:46:59',NULL),
(14,'5','Laddstolpar','{\"frontmatter\":{\"title\":\"Laddstolpar\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Kanske dum fr\\u00e5ga&#8230;<\\/p>\\n\\n<p>Kan man ladda 2 bilar samtidigt p\\u00e5 en clever laddstolpe om man anv\\u00e4nder olika kontakter? Bilen bredvid \\u00e4r s\\u00e5 seg och snart missar jag ett m\\u00f6te om jag inte f\\u00e5r fylla p\\u00e5!<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 15:55:54',NULL),
(15,'4','Jo','{\"frontmatter\":{\"title\":\"Jo\",\"tags\":\"answer\"},\"text\":\"<p>Det g\\u00e5r bra, men det g\\u00e5r v\\u00e4ldigt l\\u00e5\\u00e5\\u00e5\\u00e5\\u00e5\\u00e5ngsamt.<\\/p>\\n\"}','14',NULL,NULL,NULL,NULL,'2018-01-25 15:57:38',NULL),
(16,'4','Hej gruppen!','{\"frontmatter\":{\"title\":\"Hej gruppen!\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Kollar p\\u00e5 laddbox. 3-fas, 16A. Min bil (eGolf) klarar 2-faser(!!) Nybygge. Vissa har effektvakt som g\\u00f6r att laddboxen inte drar ut huvuds\\u00e4kringen (prioriterar hush\\u00e5ll framf\\u00f6r box). L\\u00e5ter ju smart&#8230; Men \\u00e4r det n\\u00e5got som man har ett reellt behov av? Eller g\\u00e4ller det att dimensionera huvuds\\u00e4kringen ordentligt?<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 16:01:16',NULL),
(95,'6','Installera','{\"frontmatter\":{\"title\":\"Installera\",\"tags\":\"answer\"},\"text\":\"<p>Installera laddboxen! Det handlar om s\\u00e4kerhet och effektbalans f\\u00f6r hela ditt hus!<\\/p>\\n\"}','16',NULL,NULL,NULL,NULL,'2018-01-25 16:02:37',NULL),
(96,'6','2020','{\"frontmatter\":{\"title\":\"2020\",\"tags\":[\"elcar\",null,null,null]},\"text\":\"<p>Hur m\\u00e5nga laddbara bilar tror ni vi har i landet 2020? Motivera g\\u00e4rna svaret.<\\/p>\\n\"}',NULL,NULL,NULL,NULL,NULL,'2018-01-25 16:03:41',NULL),
(98,'7','Via politiken','{\"frontmatter\":{\"title\":\"Via politiken\",\"tags\":\"answer\"},\"text\":\"<p>En motion om b\\u00e4ttre elm\\u00e4tare finns p\\u00e5 agendan<\\/p>\\n\"}','1',NULL,NULL,NULL,NULL,'2018-01-25 17:08:32',NULL),
(99,'1','Men','{\"frontmatter\":{\"title\":\"Men\",\"tags\":\"answer\"},\"text\":\"<p>smarta nog att medverka till att l\\u00e5ta andra spelare ta plats!<\\/p>\\n\\n<p><em>Det \\u00e4r v\\u00e4rt stor heder!<\\/em><\\/p>\\n\"}','78',NULL,NULL,NULL,NULL,'2018-01-25 17:30:20',NULL),
(100,'1','Nej','{\"frontmatter\":{\"title\":\"Nej\",\"tags\":\"comment\"},\"text\":\"<p>Ni \\u00e4r inte tjockskalliga<\\/p>\\n\"}','3',1,NULL,NULL,NULL,'2018-01-25 17:31:35',NULL),
(109,'4','Hmm','{\"frontmatter\":{\"title\":\"Hmm\",\"tags\":\"comment\"},\"text\":\"<p>Uppenbarligen fungerar ju inte molntj\\u00e4nsten<\\/p>\\n\"}','13',1,NULL,NULL,NULL,'2018-01-25 18:21:59',NULL),
(110,'4','Viktig fråga','{\"frontmatter\":{\"title\":\"Viktig fr\\u00e5ga\",\"tags\":\"comment\"},\"text\":\"<p>Jag vill ju inte att systemet brinner upp<\\/p>\\n\"}','1',1,NULL,NULL,NULL,'2018-01-25 20:21:34',NULL);


--
-- Table structure for table `user`
--


CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acronym` varchar(80) COLLATE utf8_swedish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `email` varchar(80) COLLATE utf8_swedish_ci DEFAULT NULL,
  `profile` varchar(256) COLLATE utf8_swedish_ci DEFAULT NULL,
  `isadmin` tinyint(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acronym` (`acronym`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;


--
-- Dumping data for table `user`
--

INSERT INTO `user` VALUES 
(1,'guni','$2y$10$qDKEnCiq.V8Fe/PhcoXHKejnwdeQnkZg8QQf4mVKQYQ9I67kXKS4y','gunvor@behovsbo.se','Kuddkulla',1,'2017-10-23 07:56:13','2018-01-25 00:00:00',NULL),
(2,'Anders','$2y$10$Q5.Ld1kd1CKK4XyLa7n4TubLMDk1q9a2JjqhMuWBVDyb75rsARP7i','anders@electrotest.se','Effektdala',0,'2017-10-23 10:17:00','2018-01-25 00:00:00',NULL),
(3,'Nestor','$2y$10$RVJkD6.7ixHq4v5Lavy2XePuokPCUWDGa/aIHF1ko5Hv0xTdH9qgq','mos@dbwebb.se','Helghult',0,'2017-10-24 07:05:23','2018-01-25 00:00:00',NULL),
(4,'Arne','$2y$10$UuVMyTq0kQtJCFZnACNcbOUtjBPYYRM7ycHcPNXjFZmchjg4RQzsK','nhdandersson@gmail.com','Flygeborg',0,'2017-10-24 07:17:06','2018-01-25 00:00:00',NULL),
(5,'Cyklist','$2y$10$7K2IGV/dD0KD1lKyrzpU7OFwGl3hyAffFCa9YUh3Xx7Li79Iy/vqG','magnusandersson076@gmail.com','Cykelholm',0,'2017-10-24 07:18:24','2018-01-13 00:00:00',NULL),
(6,'Torpare','$2y$10$rM9TkwP2dEjJFLaAvIJog.IwUelF9Ch/JCAmDLLWxTlX2qHR2U6Se','marcusgu@hotmail.com','Skogen',0,'2017-10-25 12:19:06','2018-01-13 00:00:00',NULL),
(7,'Nils','$2y$10$OO2zE5M3Ln1SHB.38tqR8.1YZ60WamqqBcoAXYMkIT2Y/L6B95Ipy','niso16@student.bth.se','Niklasberg',0,'2018-01-13 12:22:26','2018-01-25 00:00:00',NULL),
(8,'Magnum','$2y$10$nqIXqiQdatehH6O5SfsBQO6KmoqVc5EuFOEWfrhQlxmGzXw25iH/.','magnusandersson076@gmail.com','Magisterhamn',0,'2018-01-13 12:25:12','2018-01-14 00:00:00',NULL);

