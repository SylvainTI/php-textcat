<?php

/*
 * Author: Christophe Dri
 *
 *  Might be commented soon, or, please contribute.
 */

define('DIR_INPUT', 'samples/');
define('DIR_OUTPUT', 'languages/');
define('EXTENSION_IN', '.txt');
define('EXTENSION_OUT', '.lng');
define('N_GRAM_LENGTH', 6);
define('N_GRAM_COUNT', 400);


/*
 *   Recursively checking for files to parse
 */

function fetchdir($path, $callback = null) {
    $excludes = array('.', '..'); // directories to exclude

    $files = scandir($path);
    $files = array_diff($files, $excludes);

    foreach ($files as $file) {
        if (is_dir($path . '/' . $file))
            fetchdir($path . '/' . $file, $callback);
        // match only target extension files
        else if (!preg_match('/^.*\\' . EXTENSION_IN . '$/', $file))
            continue;
        else if (is_callable($callback, false, $call_name))
            $call_name($path . '/' . $file);
        else
            echo($path . '/' . $file . "\n");
    }
}


/*
 *    Analysing text files using the N-Gram-Based Text Categorization
 *    You might need to generate/regenerate your own language files for better accuracy / other needs
 *    cf. 	http://text-analysis.googlecode.com/files/n-gram_based_text_categorization.pdf
 */

function analyze($file) {
    $file_content = file_get_contents($file);
    $file_content = preg_replace('/[^\w\s\']+/', '', $file_content);
    preg_match_all('/[\S]+/', $file_content, $words);
    $words = $words[0];

    $tokens = array();
    foreach ($words as $word) {
        $word = '_' . $word . '_';
        for ($i = 1; $i <= min(N_GRAM_LENGTH, strlen($word)); $i++) {
            for ($j = 0; $j <= strlen($word) - $i; $j++) {
                $token = strtolower(substr($word, $j, $i));
                if (trim($token, '_'))
                    $tokens[] = $token;
            }
        }
    }
    $tokens = array_count_values($tokens);
    arsort($tokens);
    $ngrams = array_slice(array_keys($tokens), 0, N_GRAM_COUNT);

    file_put_contents(DIR_OUTPUT . str_replace(EXTENSION_IN, EXTENSION_OUT, basename($file)), implode(PHP_EOL, $ngrams));
}

fetchdir(DIR_INPUT, 'analyze');