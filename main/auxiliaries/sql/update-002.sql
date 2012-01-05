ALTER TABLE  `p_cups` CHANGE  `type`  `type` ENUM(  'playoff',  'one-lap',  'two-laps',  'undefined' );

CREATE TABLE  `p_tournament` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 256 ) NOT NULL ,
 `wiki` TEXT NOT NULL ,
 PRIMARY KEY (  `id` )
);

ALTER TABLE  `p_competitions` ADD  `tournament_id` INT DEFAULT  '0' NOT NULL AFTER  `league_id` ;

ALTER TABLE  `p_tournament` RENAME  `p_tournaments` ;

ALTER TABLE  `p_competitions` CHANGE  `coef`  `coef` DOUBLE


--
-- Дамп данных таблицы `p_competitions`
--

INSERT INTO `p_competitions` VALUES (1, 1, 1, 'betacompany первый чемпионат по пайпу 2008', '2008', '2008-11-01', 200, 'Это первый, может быть, в чем-то экспериментальный турнир по пайпу. Мы даже не могли представить, каким захватывающим он получится: до последнего тура была интрига, и исход любого матч мог поменять турнирное положение.<br>\r\nБонусы к WPR: за первое место +1.00, за второе +0.75, за третье +0.50, за четвертое +0.25.');
INSERT INTO `p_competitions` VALUES (2, 1, 2, 'Лемболово Open 2008', '2008', '2008-09-28', 87.104, '27-28 сентября 2008 года. Чемпионат Лемболово по пайпу на открытом воздухе. Подобное соревнование ранее не проводилось ни разу.');
INSERT INTO `p_competitions` VALUES (3, 1, 3, 'День рождения пайпа. Александровская Open 2008. Первая Лига', '2008-1', '2008-11-02', 94.795, 'Турнир, посвященный первому дню рождения пайпа. В честной борьбе сошлись недавние соперники по чемпионату betacompany 2008.');
INSERT INTO `p_competitions` VALUES (4, 1, 3, 'День рождения пайпа. Александровская Open 2008. Вторая Лига', '2008-2', '2008-11-02', 110, 'Турнир, в который попали игроки занявшие в своих группах первые и вторые места. Таблица с очками ничего не отражает!');
INSERT INTO `p_competitions` VALUES (5, 1, 4, 'Кубок Красного Курсанта 6/9 ''09', '2009', '2009-04-11', 242, 'Открытый чемпионат по пайпу проходит на территории <a class="text" href="http://610.ru" target="_blank">Санкт-Петербургской классической гимназии</a>, расположенной по адресу: ул. Красного Курсанта,&nbsp;6/9<br>\r\n<br>\r\n<u>Регламент</u><br>\r\n<br>\r\n21 заявившийся участник разбит на 3 группы по семь человек в каждой. Внутри групп они играют по два матча с каждым соперником (на своей и чужой первой подаче). По завершении регулярной части Кубка двое лучших из каждой группы, а также двое лучших по средним очкам среди третьих мест попадают в 1/4 финала, где жребием распределяются на пары. Все матчи регулярной части идут до 5 очков, а в плей-офф &#151; до 10 очков.');
INSERT INTO `p_competitions` VALUES (6, 1, 5, 'Abaza Championship 2009', '2009', '2009-02-22', 69, 'Турнир в честь совершеннолетия знаменитого пайп-мэна Фёдора Абаза!');
INSERT INTO `p_competitions` VALUES (7, 1, 0, 'Тёма Cup 2009', '2009', '2009-04-26', 200, '<a href="/temacup.php">подробная информация</a><br /><br />\r\nГрандиозный open-air турнир по pipe-in-pipe в Александровской, а чтобы не без повода — празднование дня рождения Артёма Григорьева.');
INSERT INTO `p_competitions` VALUES (8, 1, 6, 'Открытие сезона 2009/2010. Комарово Open 2009', '2009', '2009-08-22', 142.82, 'Турнир-открытие нового спортивного пайп-сезона');
INSERT INTO `p_competitions` VALUES (9, 1, 2, 'Лемболово Open 2009', '2009', '2009-08-29', 114.34, 'Второй розыгрыш ежегодного турнира в лесу недалеко от станции Лемболово.');
INSERT INTO `p_competitions` VALUES (10, 1, 7, 'Открытый чемпионат матмеха по пайпу 2009', '2009', '2009-09-13', 58.02, 'Первый розыгрыш открытого чемпионата матмеха СПбГУ по пайпу.');
INSERT INTO `p_competitions` VALUES (11, 1, 3, 'Второй День рождения пайпа. Александровская Open 2009', '2009', '2009-11-01', 299.44, 'Традиционный турнир-праздник в честь Дня рождения пайпа.');
INSERT INTO `p_competitions` VALUES (12, 1, 4, 'Кубок Красного Курсанта 6/9 ''10', '2010', '2010-04-10', 476.21, '<style> \r\n#schedule { width: 100%; }\r\n#schedule td { border: 1px #cccccc solid; padding: 2px; width: 25%; }\r\n#schedule th { border: 1px #cccccc solid; background-color: #cccccc; padding: 2px; width: 25%; }\r\n</style> \r\n \r\nКонференции:\r\n<ul class="text"> \r\n<li>Первая: группы &#920;, &#923;, &#931;;</li> \r\n<li>Вторая: группы &#926;, &#936;, &#937;.</li> \r\n</ul> \r\n \r\n<table id="schedule" class="normal text"> \r\n<thead> \r\n<tr> \r\n<th style=""></th> \r\n<th style="">Этапы</th> \r\n<th style="">Конф-ции</th> \r\n<th style=";">Туры</th> \r\n</tr> \r\n</thead> \r\n<tbody> \r\n<tr><td>06 февраля</td><td>Первый</td><td>обе</td><td>1, 2</td></tr> \r\n<tr><td>13 февраля</td><td>Второй</td><td>I</td><td>3, 4, 5</td></tr> \r\n<tr><td>20 февраля</td><td>Игр нет</td><td></td><td></td></tr> \r\n<tr><td>27 февраля</td><td>Второй</td><td>II</td><td>3, 4, 5</td></tr> \r\n<tr><td>06 марта</td><td>Третий</td><td>I</td><td>6, 7, 8</td></tr> \r\n<tr><td>13 марта</td><td>Третий</td><td>II</td><td>6, 7, 8</td></tr> \r\n<tr><td>20 марта</td><td>Четвёртый</td><td>обе</td><td>9, 10, доигровки</td></tr> \r\n<tr><td>27 марта</td><td>Игр нет</td><td></td><td></td></tr> \r\n<tr><td>03 апреля</td><td>Плей-офф</td><td>обе</td><td>1/8</td></tr> \r\n<tr><td>10 апреля</td><td>Плей-офф</td><td>обе</td><td>1/4, 1/2, 3М, Ф</td></tr> \r\n</tbody> \r\n</table> \r\n<br /> \r\nВ плей-офф выходят пайпмены, занявшие по итогам регулярной части турнира первые и вторые места в группах, а также четверо лучших по (средним) очкам среди занявших третьи места.<br /><br /> \r\nОпределены добавки за места в плей-офф: I&nbsp;место&nbsp;+2.00, II&nbsp;место&nbsp;+1.50, III&nbsp;место&nbsp;+1.25, IV&nbsp;место&nbsp;+1.00, проигравшие в 1/4-финалах&nbsp;+0.75, проигравшие в 1/8-финалах&nbsp;+0.50.');
INSERT INTO `p_competitions` VALUES (13, 1, 5, 'Abaza All Stars 2010', '2010', '2010-02-20', 0, 'Товарищеский турнир, посвящённый 19-тилетию пайпмена Фёдора Абаза. Впервые, 20 февраля 2010 года, в пайп играли ниже уровня земли: underground! Победитель турнира, Иннокентий Шувалов, получил 69 баллов в WPR, остальные не получили ничего.');
INSERT INTO `p_competitions` VALUES (14, 1, 7, 'Первый чемпионат матмеха по пайпу 2010', '2010', '2010-04-21', 73.54, 'Первый розыгрыш чемпионата математико-мехнического факультета СПбГУ по пайпу.');
INSERT INTO `p_competitions` VALUES (15, 1, 0, 'Pipecelebrity Cup', '2010', '2010-05-23', 296.97, '');
INSERT INTO `p_competitions` VALUES (16, 1, 8, 'Закрытие сезона 2009/2010. Пайпфест в Лемболово', '2010', '2010-07-03', 420.84, '');


--
-- Дамп данных таблицы `p_cups`
--

INSERT INTO `p_cups` VALUES (1, 1, 0, 'undefined');
INSERT INTO `p_cups` VALUES (2, 2, 0, 'two-laps');
INSERT INTO `p_cups` VALUES (3, 3, 0, 'undefined');
INSERT INTO `p_cups` VALUES (4, 3, 6, 'one-lap');
INSERT INTO `p_cups` VALUES (5, 3, 6, 'one-lap');
INSERT INTO `p_cups` VALUES (6, 4, 0, 'undefined');
INSERT INTO `p_cups` VALUES (7, 5, 0, 'playoff');
INSERT INTO `p_cups` VALUES (8, 5, 7, 'two-laps');
INSERT INTO `p_cups` VALUES (9, 5, 7, 'two-laps');
INSERT INTO `p_cups` VALUES (10, 5, 7, 'two-laps');
INSERT INTO `p_cups` VALUES (11, 6, 0, 'undefined');
INSERT INTO `p_cups` VALUES (12, 6, 11, 'one-lap');
INSERT INTO `p_cups` VALUES (13, 6, 11, 'one-lap');
INSERT INTO `p_cups` VALUES (14, 6, 11, 'one-lap');
INSERT INTO `p_cups` VALUES (15, 6, 11, 'one-lap');
INSERT INTO `p_cups` VALUES (16, 7, 0, 'playoff');
INSERT INTO `p_cups` VALUES (17, 7, 16, 'one-lap');
INSERT INTO `p_cups` VALUES (18, 7, 16, 'one-lap');
INSERT INTO `p_cups` VALUES (19, 8, 0, 'playoff');
INSERT INTO `p_cups` VALUES (20, 8, 19, 'one-lap');
INSERT INTO `p_cups` VALUES (21, 8, 19, 'one-lap');
INSERT INTO `p_cups` VALUES (22, 8, 19, 'one-lap');
INSERT INTO `p_cups` VALUES (23, 9, 0, 'undefined');
INSERT INTO `p_cups` VALUES (24, 9, 23, 'one-lap');
INSERT INTO `p_cups` VALUES (25, 9, 23, 'one-lap');
INSERT INTO `p_cups` VALUES (26, 10, 0, 'undefined');
INSERT INTO `p_cups` VALUES (27, 10, 26, 'one-lap');
INSERT INTO `p_cups` VALUES (28, 10, 26, 'one-lap');
INSERT INTO `p_cups` VALUES (31, 11, 0, 'playoff');
INSERT INTO `p_cups` VALUES (32, 11, 31, 'one-lap');
INSERT INTO `p_cups` VALUES (33, 11, 31, 'one-lap');
INSERT INTO `p_cups` VALUES (34, 11, 31, 'one-lap');
INSERT INTO `p_cups` VALUES (35, 11, 31, 'one-lap');
INSERT INTO `p_cups` VALUES (36, 12, 0, 'playoff');
INSERT INTO `p_cups` VALUES (37, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (38, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (39, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (40, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (41, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (42, 12, 36, 'two-laps');
INSERT INTO `p_cups` VALUES (43, 13, 0, 'one-lap');
INSERT INTO `p_cups` VALUES (44, 14, 0, 'playoff');
INSERT INTO `p_cups` VALUES (45, 14, 44, 'one-lap');
INSERT INTO `p_cups` VALUES (46, 14, 44, 'one-lap');
INSERT INTO `p_cups` VALUES (47, 14, 44, 'one-lap');
INSERT INTO `p_cups` VALUES (48, 14, 44, 'one-lap');
INSERT INTO `p_cups` VALUES (49, 15, 0, 'playoff');
INSERT INTO `p_cups` VALUES (50, 15, 49, 'one-lap');
INSERT INTO `p_cups` VALUES (51, 15, 49, 'one-lap');
INSERT INTO `p_cups` VALUES (52, 15, 49, 'one-lap');
INSERT INTO `p_cups` VALUES (53, 15, 49, 'one-lap');
INSERT INTO `p_cups` VALUES (54, 16, 0, 'playoff');
INSERT INTO `p_cups` VALUES (55, 16, 54, 'one-lap');
INSERT INTO `p_cups` VALUES (56, 16, 54, 'one-lap');
INSERT INTO `p_cups` VALUES (57, 16, 54, 'one-lap');
INSERT INTO `p_cups` VALUES (58, 16, 54, 'one-lap');

--
-- Дамп данных таблицы `p_tournaments`
--

INSERT INTO `p_tournaments` VALUES (1, 'Чемпионат betacompany', '');
INSERT INTO `p_tournaments` VALUES (2, 'Лемболово Open', '');
INSERT INTO `p_tournaments` VALUES (3, 'День рождения пайпа', '');
INSERT INTO `p_tournaments` VALUES (4, 'Кубок Красного Курсанта', '');
INSERT INTO `p_tournaments` VALUES (5, 'Abaza Tournament', '');
INSERT INTO `p_tournaments` VALUES (6, 'Открытие сезона', '');
INSERT INTO `p_tournaments` VALUES (7, 'Чемпионат матмеха СПбГУ', '');
INSERT INTO `p_tournaments` VALUES (8, 'Закрытие сезона', '');