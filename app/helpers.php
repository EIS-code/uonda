<?php

function removeSpaces(string $string)
{
    return preg_replace('/\s+/', '', $string);
}

function truncate($string, $length, $dots = "...")
{
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}

function uniqueValues(array $array)
{
    return array_unique($array);
}
