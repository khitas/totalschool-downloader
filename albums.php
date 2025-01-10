<?php
include "functions.php";

$page = 1;
$continue = true;
$album_index = 0;
$skip_if_folder_exists = true;

$cookie = array();

$fp = @fopen("cookie.txt", "r");

if ($fp) {
    while (($line_string = fgets($fp, 4096)) !== false) {
        $line_parts = preg_split("/:/", $line_string);
        $cookie[$line_parts[0]] = trim(preg_replace("/\r|\n/", "", isset($line_parts[1]) ? $line_parts[1] : ''));
    }

    if (!feof($fp)) {
        echo "Error: unexpected fgets() fail\n";
    }

    fclose($fp);
}

echo PHP_EOL . PHP_EOL;
echo 'Collecting Image Albums Infos...';

$albums_curl_parms = "--compressed -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:132.0) Gecko/20100101 Firefox/132.0'" .
    " -H 'Accept: application/json'" .
    " -H 'Accept-Language: en-GB,en;q=0.5'" .
    " -H 'Accept-Encoding: gzip, deflate, br, zstd'" .
    " -H 'X-CSRF-TOKEN: " . $cookie["X-CSRF-TOKEN"] . "'" .
    " -H 'X-SCHOOL-ID: 335'" .
    " -H 'X-CLIENT-ID: TS-WEB'" .
    " -H 'X-XSRF-TOKEN: " . $cookie["X-XSRF-TOKEN"] . "'" .
    " -H 'Alt-Used: my.totalschool.io'" .
    " -H 'Connection: keep-alive'" .
    " -H 'Referer: https://my.totalschool.io/u/photo-gallery'" .
    " -H 'Cookie: " . $cookie["Cookie"] . "'" .
    " -H 'Sec-Fetch-Dest: empty'" .
    " -H 'Sec-Fetch-Mode: cors'" .
    " -H 'Sec-Fetch-Site: same-origin'" .
    " -H 'TE: trailers'";

while ($continue) {
    echo PHP_EOL . PHP_EOL;
    exec("/usr/bin/curl 'https://my.totalschool.io/api/v1/photo-gallery/albums?page=" . $page . "&include=links,permissions' " . $albums_curl_parms . " --output 'data_albums.json' ");
    sleep(1);

    $albums_json_data = file_get_contents('data_albums.json');

    $albums = json_decode($albums_json_data, false);

    if (isset($albums->error)) {
        echo PHP_EOL . PHP_EOL;
        echo $albums->error;
        echo PHP_EOL . PHP_EOL;
        echo "Goto Firefox => Network, Search for albums page, Copy Value => Copy Request Headers";
        echo PHP_EOL . PHP_EOL;
        exit;
    }

    $pagination = $albums->meta->pagination;

    if ($page == 1) {
        echo PHP_EOL . PHP_EOL;
        echo 'Found total ' . $pagination->total . ' Photo Albums in ' . $pagination->total_pages . ' page(s)...';
        sleep(1);
    }

    echo PHP_EOL . PHP_EOL;
    echo 'Collecting Photo Albums from page ' . $pagination->current_page . '/' . $pagination->total_pages;
    sleep(1);

    foreach ($albums->data as $album) {
        $album_index++;
        $album_name = $album->name;
        $album_name = str_replace('"', '', $album_name);
        $album_name = str_replace('/', '', $album_name);
        $album_name = str_replace(':', '', $album_name);
        $album_name_date = $album_name . ' (' . $album->date . ')';
        $album_title = '' . $album_index . '/' . $albums->meta->pagination->total . ' page ' . $pagination->current_page . '/' . $pagination->total_pages . ' : ' . $album->id . ' : ' . $album_name_date;
        $album_path = 'gallery/' . date("Ymd", strtotime($album->date)) . '_' . str_replace(' ', '_', $album_name).'_photos';
        $album_file_json = $album_path . '/' . $album->id . '.json';

        echo PHP_EOL . PHP_EOL;
        echo 'Collecting Images for Photo Album ' . $album_title;

        if (file_exists($album_path)) {
            echo ' ('.count(glob($album_path . "/*.*")). ' files)';
            if (count(glob($album_path . "/*.*")) <= 1){
                deleteDirectory($album_path);
            }
        }

        sleep(1);

        if (!$skip_if_folder_exists || !file_exists($album_path)) {

            if (!file_exists($album_path)) {
                mkdir($album_path, 0777, true);
            }

            $album_curl_parms = "--compressed -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:132.0) Gecko/20100101 Firefox/132.0'" .
                " -H 'Accept: application/json'" .
                " -H 'Accept-Language: en-GB,en;q=0.5'" .
                " -H 'Accept-Encoding: gzip, deflate, br, zstd'" .
                " -H 'X-CSRF-TOKEN: " . $cookie["X-CSRF-TOKEN"] . "'" .
                " -H 'X-SCHOOL-ID: 335'" .
                " -H 'X-CLIENT-ID: TS-WEB'" .
                " -H 'X-XSRF-TOKEN: " . $cookie["X-XSRF-TOKEN"] . "'" .
                " -H 'Alt-Used: my.totalschool.io'" .
                " -H 'Connection: keep-alive'" .
                " -H 'Referer: https://my.totalschool.io/u/photo-gallery/" . $album->id . "'" .
                " -H 'Cookie: " . $cookie["Cookie"] . "'" .
                " -H 'Sec-Fetch-Dest: empty'" .
                " -H 'Sec-Fetch-Mode: cors'" .
                " -H 'Sec-Fetch-Site: same-origin'" .
                " -H 'TE: trailers'";

            $album_continue = true;
            $album_page = 1;
            while ($album_continue) {

                echo PHP_EOL . PHP_EOL;
                exec("/usr/bin/curl 'https://my.totalschool.io/api/v1/photo-gallery/albums/" . $album->id . "/images?page=" . $album_page . "=1&include=links,permissions' " . $album_curl_parms . " --output '" . $album_file_json . "'");
                sleep(1);

                $gallery_json_data = file_get_contents($album_file_json);
                $gallery = json_decode($gallery_json_data, false);

                $gpagination = $gallery->meta->pagination;

                if ($album_page == 1) {
                    echo PHP_EOL . PHP_EOL;
                    echo 'Found total ' . $gpagination->total . ' Images in ' . $gpagination->total_pages . ' page(s)...';
                    sleep(1);
                }

                echo PHP_EOL . PHP_EOL;
                echo 'Collecting Images from page ' . $gpagination->current_page . '/' . $gpagination->total_pages . ' for Photo Albums ' . $album_title;
                sleep(1);

                $image_curl_parms = " -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:132.0) Gecko/20100101 Firefox/132.0'" .
                    " -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'" .
                    " -H 'Accept-Language: en-GB,en;q=0.5'" .
                    " -H 'Accept-Encoding: gzip, deflate, br, zstd'" .
                    " -H 'Connection: keep-alive'" .
                    " -H 'Cookie: " . $cookie["Cookie"] . "'" .
                    " -H 'Upgrade-Insecure-Requests: 1'" .
                    " -H 'Sec-Fetch-Dest: document'" .
                    " -H 'Sec-Fetch-Mode: navigate'" .
                    " -H 'Sec-Fetch-Site: none'" .
                    " -H 'TE: trailers'";

                $image_index = 0;
                foreach ($gallery->data as $image) {
                    $image_path = $album_path . '/' . $image->id . '.jpeg';
                    $image_index++;
                    $image_name = '' . $image_index . '/' . $gpagination->total . ' page ' . $gpagination->current_page . '/' . $gpagination->total_pages . ' : ' . $image->id . ' for Album ' . $album_title;

                    echo PHP_EOL . PHP_EOL;
                    echo 'Downloading Image ' . $image_name;
                    sleep(1);

                    echo PHP_EOL . PHP_EOL;
                    exec("/usr/bin/curl '" . $image->src . "' " . $image_curl_parms . " --output '" . $image_path . "'");
                    sleep(1);

                }

                $album_page++;
                $album_continue = ($gpagination->current_page < $gpagination->total_pages);
            }


        } else {

            echo ' ==> Skip Checking';
        }

    }

    $page++;
    $continue = ($albums->meta->pagination->current_page < $albums->meta->pagination->total_pages);
}

echo PHP_EOL . PHP_EOL;