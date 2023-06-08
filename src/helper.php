<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-08 07:09:51
 * @modify date 2023-06-08 07:42:55
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Url;

if (!function_exists('url'))
{
    function url(array $additionalQuery = [], bool $reset = false)
    {
        return Url::getSelf(function(string $url) use($additionalQuery, $reset) {
            $baseQuery = '?id=' . $_GET['id'] . '&mod=' . $_GET['mod'];
            
            // reset mod
            if ($reset) return $url . $baseQuery;

            return $url .'?' . http_build_query(array_unique(array_merge($_GET, $additionalQuery)));
        });
    }
}