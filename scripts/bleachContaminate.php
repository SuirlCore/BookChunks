<?php

// Function to sanitize a string for MySQL
function bleach($string) {
    // Define a mapping of characters and SQL keywords to their replacements
    $search = [
        ';', '(', ')', '\'', '"', '\\', 
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'WHERE', 
        'DROP', 'CREATE', 'ALTER', 'TABLE', 'FROM', 
        'JOIN', 'UNION', 'NULL', 'AND', 'OR', 'NOT', 
        'LIKE', 'IN', 'AS', 'ORDER', 'GROUP', 'BY', 
        'HAVING', 'LIMIT', 'DISTINCT', 'VALUES', 'SET'
    ];
    $replace = [
        '[SEMICOLON]', '[OPEN_PAREN]', '[CLOSE_PAREN]', '[SINGLE_QUOTE]', '[DOUBLE_QUOTE]', '[BACKSLASH]', 
        '[SQL_SELECT]', '[SQL_INSERT]', '[SQL_UPDATE]', '[SQL_DELETE]', '[SQL_WHERE]', 
        '[SQL_DROP]', '[SQL_CREATE]', '[SQL_ALTER]', '[SQL_TABLE]', '[SQL_FROM]', 
        '[SQL_JOIN]', '[SQL_UNION]', '[SQL_NULL]', '[SQL_AND]', '[SQL_OR]', '[SQL_NOT]', 
        '[SQL_LIKE]', '[SQL_IN]', '[SQL_AS]', '[SQL_ORDER]', '[SQL_GROUP]', '[SQL_BY]', 
        '[SQL_HAVING]', '[SQL_LIMIT]', '[SQL_DISTINCT]', '[SQL_VALUES]', '[SQL_SET]'
    ];

    // Replace each special character and keyword with its placeholder
    return str_ireplace($search, $replace, $string);
}

// Function to reverse the sanitization
function contaminate($string) {
    // Define the reverse mapping of placeholders to their original characters and keywords
    $replace = [
        ';', '(', ')', '\'', '"', '\\', 
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'WHERE', 
        'DROP', 'CREATE', 'ALTER', 'TABLE', 'FROM', 
        'JOIN', 'UNION', 'NULL', 'AND', 'OR', 'NOT', 
        'LIKE', 'IN', 'AS', 'ORDER', 'GROUP', 'BY', 
        'HAVING', 'LIMIT', 'DISTINCT', 'VALUES', 'SET'
    ];
    $search = [
        '[SEMICOLON]', '[OPEN_PAREN]', '[CLOSE_PAREN]', '[SINGLE_QUOTE]', '[DOUBLE_QUOTE]', '[BACKSLASH]', 
        '[SQL_SELECT]', '[SQL_INSERT]', '[SQL_UPDATE]', '[SQL_DELETE]', '[SQL_WHERE]', 
        '[SQL_DROP]', '[SQL_CREATE]', '[SQL_ALTER]', '[SQL_TABLE]', '[SQL_FROM]', 
        '[SQL_JOIN]', '[SQL_UNION]', '[SQL_NULL]', '[SQL_AND]', '[SQL_OR]', '[SQL_NOT]', 
        '[SQL_LIKE]', '[SQL_IN]', '[SQL_AS]', '[SQL_ORDER]', '[SQL_GROUP]', '[SQL_BY]', 
        '[SQL_HAVING]', '[SQL_LIMIT]', '[SQL_DISTINCT]', '[SQL_VALUES]', '[SQL_SET]'
    ];

    // Replace each placeholder with the original character or keyword
    return str_ireplace($search, $replace, $string);
}

// Example usage
$originalString = "SELECT * FROM users WHERE name = 'John' AND age > 30;";
$sanitized = sanitizeForSQL($originalString);
echo "Sanitized: " . $sanitized . PHP_EOL;

$reversed = reverseSanitizeFromSQL($sanitized);
echo "Reversed: " . $reversed . PHP_EOL;

?>
