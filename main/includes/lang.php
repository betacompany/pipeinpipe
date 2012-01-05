<?php

function lang_sclon($count, $nom_sg, $gen_sg, $gen_pl) {
	return (
				$count % 10 == 1 && $count % 100 != 11 ?
				$nom_sg : 
				($count % 10 >= 2 && $count % 10 <= 4 && ($count % 100 < 10 || $count % 100 >= 20) ? $gen_sg : $gen_pl)
			);
}

function lang_number_sclon($count, $nom_sg, $gen_sg, $gen_pl) {
	if ($count == 0) return "нет&nbsp;$gen_pl";
	return $count . '&nbsp;' . lang_sclon($count, $nom_sg, $gen_sg, $gen_pl);
}

?>