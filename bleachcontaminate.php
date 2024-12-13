<?php
function bleach($strIn){
    str_irreplace("=", "!#eq", $strIn);
    str_irreplace("or", "!#0r", $strIn);
    str_irreplace("and", "!#&", $strIn);
    str_irreplace("select", "!#slct", $strIn);
    str_irreplace("drop", "!#drp", $strIn);
    str_irreplace("insert", "!#ins", $strIn);
    str_irreplace("delete", "!#del", $strIn);
    str_irreplace("alter", "!#alt", $strIn);
    str_irreplace("create", "!#cre", $strIn);
    str_irreplace("where", "!#whe", $strIn);
    str_irreplace("+", "!#plu", $strIn);
    str_irreplace("-", "!#min", $strIn);
    str_irreplace("/", "!#fsl", $strIn);
    str_irreplace("\\", "!#bsl", $strIn);
    str_irreplace("*", "!#sta", $strIn);
    str_irreplace("(", "!#lper", $strIn);
    str_irreplace(")", "!#rper", $strIn);
    str_irreplace(";", "!#scol", $strIn);
}

function contaminate($strIn){
    str_irreplace("!#eq", "=", $strIn);
    str_irreplace("!#0r", "or", $strIn);
    str_irreplace("!#&", "and", $strIn);
    str_irreplace("!#slct", "select", $strIn);
    str_irreplace("!#drp", "drop", $strIn);
    str_irreplace("!#ins", "insert", $strIn);
    str_irreplace("!#del", "delete", $strIn);
    str_irreplace("!#alt", "alter", $strIn);
    str_irreplace("!#cre", "create", $strIn);
    str_irreplace("!#whe", "where", $strIn);
    str_irreplace("!#plu", "+", $strIn);
    str_irreplace("!#min","-", $strIn);
    str_irreplace("!#fsl", "/", $strIn);
    str_irreplace("!#bsl", "\\", $strIn);
    str_irreplace("!#sta", "*", $strIn);
    str_irreplace("!#lper", "(", $strIn);
    str_irreplace("!#rper", ")", $strIn);
    str_irreplace("!#scol", ";", $strIn);
}
?>

