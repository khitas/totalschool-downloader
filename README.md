# totalschool-downloader
Batch downloader tool for my.totalschool (for photos and videos) written with PHP.

## Setup

To run this tool you will need PHP 8.x. and an existing account for my.totalschool platform.

- Login into my.totalschool with your account
- Get Request Headers from you browser
- Paste Headers into cookie.txt file
- Run the tool


How to Get Request Headers in Firefox

- Select Photos from left menu (main menu)
- Right click on page
- Click Inspect to open the developer console.
- Select Network tab and reload the page (Ctrl + r)
- Inside list with requests, search for request with "albums?page" in Column File
- Right click on selected request and select Copy Value > Copy Request Headers
- Paste the Headers inside cookie.txt file

Example of Headers
> GET /api/v1/photo-gallery/albums?page=1&include=links,permissions HTTP/3
> 
>Host: ..............
> 
> .....
> 
> TE: trailers

## How to run

1st way :
```bash
  cd totalschool-downloader-main
  chmod +x run.sh
  ./run.sh
```

2nd way :
```bash
  cd totalschool-downloader-main
  php albums.php
  php videos.php
```
    