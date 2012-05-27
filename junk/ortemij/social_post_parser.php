<?php

require_once dirname(__FILE__) . '/../../main/classes/content/Parser.php';

echo Parser::parseSocialPost("Это [id397095|Артём Григорьев]!!!", ISocialWeb::VKONTAKTE);

echo "<br/>";
echo Parser::parseSocialPost("Это @ortemij!!!", ISocialWeb::TWITTER);

echo "<br/>";
echo Parser::parseSocialPost("Это #тег!!!", ISocialWeb::TWITTER);

echo "<br/>";
echo Parser::parseSocialPost("Это @alert('hacked!')!!!", ISocialWeb::TWITTER);

echo "<br/>";
echo Parser::parseSocialPost("Это [club36766958|Артём Григорьев]!!!", ISocialWeb::VKONTAKTE);

echo "<br/>";
echo Parser::parseSocialPost("Благодаря помощи [id453252|Михаила Шигарова], [id341824|Михаила Толстого], [id362923|Александра Ланчева], Бориса Смирнова и особенно [id443127|Дмитря Бородина] сегодня вышел сюжет на канале СТО!", ISocialWeb::VKONTAKTE);

?>