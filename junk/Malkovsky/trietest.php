<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../main/classes/utils/Trie.php';


$tr = new Trie('%');

$tr->addstring("abracadabra");
$tr->addstring("abrbr");

$tr->draw();
?>
