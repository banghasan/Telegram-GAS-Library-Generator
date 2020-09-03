# Telegram-Library-Generator

Membuat Source Library Telegram Secara Otomatis
untuk Google Apps Script

Generate Telegram Library Source, Based From Scrapping Url Docs [https://core.telegram.org/bots/api](https://core.telegram.org/bots/api)

Hasil dapat dilihat pada [example.js](https://github.com/banghasan/Telegram-GAS-Library-Generator/blob/master/example.js) yang dapat diletakkan di Google Apps Script.

## Localhost

Karena saya suka dengan command line, maka:

    php -S 0.0.0.0:8080 libgram.php

kemudian diakses di browser:

    http://localhost:8080/libgram.php?source=javascript

## Perubahan

+ penyesuaian API lama dengan `tg.request`
+ support request metode form
+ support blob
+ membuang komentar per paramater

## Resource

Script penyesuaian [Telegram-Library-Generator](https://github.com/Aghisna12/Telegram-Library-Generator)
