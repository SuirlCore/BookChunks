<?php
function bleach($strIn){
    $strIn = str_irreplace("=", "!#eq", $strIn);
    $strIn = str_irreplace("or", "!#0r", $strIn);
    $strIn = str_irreplace("and", "!#&", $strIn);
    $strIn = str_irreplace("select", "!#slct", $strIn);
    $strIn = str_irreplace("drop", "!#drp", $strIn);
    $strIn = str_irreplace("insert", "!#ins", $strIn);
    $strIn = str_irreplace("delete", "!#del", $strIn);
    $strIn = str_irreplace("alter", "!#alt", $strIn);
    $strIn = str_irreplace("create", "!#cre", $strIn);
    $strIn = str_irreplace("where", "!#whe", $strIn);
    $strIn = str_irreplace("+", "!#plu", $strIn);
    $strIn = str_irreplace("-", "!#min", $strIn);
    $strIn = str_irreplace("/", "!#fsl", $strIn);
    $strIn = str_irreplace("\\", "!#bsl", $strIn);
    $strIn = str_irreplace("*", "!#sta", $strIn);
    $strIn = str_irreplace("(", "!#lper", $strIn);
    $strIn = str_irreplace(")", "!#rper", $strIn);
    $strIn = str_irreplace(";", "!#scol", $strIn);

    return $strIn;
}

function contaminate($strIn){
    $strIn = str_irreplace("!#eq", "=", $strIn);
    $strIn = str_irreplace("!#0r", "or", $strIn);
    $strIn = str_irreplace("!#&", "and", $strIn);
    $strIn = str_irreplace("!#slct", "select", $strIn);
    $strIn = str_irreplace("!#drp", "drop", $strIn);
    $strIn = str_irreplace("!#ins", "insert", $strIn);
    $strIn = str_irreplace("!#del", "delete", $strIn);
    $strIn = str_irreplace("!#alt", "alter", $strIn);
    $strIn = str_irreplace("!#cre", "create", $strIn);
    $strIn = str_irreplace("!#whe", "where", $strIn);
    $strIn = str_irreplace("!#plu", "+", $strIn);
    $strIn = str_irreplace("!#min","-", $strIn);
    $strIn = str_irreplace("!#fsl", "/", $strIn);
    $strIn = str_irreplace("!#bsl", "\\", $strIn);
    $strIn = str_irreplace("!#sta", "*", $strIn);
    $strIn = str_irreplace("!#lper", "(", $strIn);
    $strIn = str_irreplace("!#rper", ")", $strIn);
    $strIn = str_irreplace("!#scol", ";", $strIn);

    return $strIn;
}

?>

