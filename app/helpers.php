<?php

function removeSpaces(string $string)
{
    return preg_replace('/\s+/', '', $string);
}
