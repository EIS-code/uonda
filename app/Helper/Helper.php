<?php

namespace App\Helper;

class Helper
{
    public static function paginationSummary($currentPage, $perPage, $totalRecords)
    {
        return 'Showing ' . ((($currentPage - 1) * $perPage) + 1) . ' to ' . (($currentPage * $perPage) < $totalRecords ? ($currentPage * $perPage) : $totalRecords) . ' of ' . $totalRecords . ' entries';
    }

    public static function listIndex($currentPage, $perPage, $listKey)
    {
        return (($currentPage - 1) * $perPage) + 1 + $listKey;
    }

    public static function generateURLWithFilter($pageUrl, $pageNo, $sortBy,$sortOrder,$search)
    {
        return "$pageUrl?page=$pageNo&sortBy=$sortBy&sortOrder=$sortOrder&search=$search";
    }

    public static function sortingDesign($sortParam,$sortBy,$sortOrder)
    {
        return $sortBy == $sortParam?($sortOrder=='asc'?'<span class="success material-icons list-sort-icon table-icons">arrow_upward</span>':'<span class="success material-icons list-sort-icon table-icons">arrow_downward</span>'):'';
    }
    

}