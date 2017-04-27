<?php

include 'simple_html_dom.php';

function getHtml($query)
{
    $opt = [
        'http' => [
            'header' => 'User-Agent:MyAgent/1.0\r\n'
        ]
    ];

    $ctx = stream_context_create($opt);
    $header = file_get_contents('http://www.unsri.ac.id/'.$query, false, $ctx);

    $html = new simple_html_dom();
    $html->load($header);

    return $html;
}

// first table > tbody > second tr > first td > div > div#content-front-left > div > table > tbody > second tr > td > ul > li > a[href]

$faculties_html = getHtml('?act=daftar_dosen');

$links = $faculties_html->find('a[href^="?act=daftar_dosen&fakultas="]');

$csv = fopen('database.csv', 'w');
fputcsv($csv, ['Fakultas', 'NIP', 'NIK', 'Nama', 'Jabatan Fungsional', 'Program Studi']);

foreach ($links as $l)
{    
    $lecturer_html = getHtml($l->href);    
    $table = $lecturer_html->find('table', 2);

    $faculty = trim(str_replace('DAFTAR DOSEN ', '', $lecturer_html->find('table', 1)->find('tr', 0)->find('td h3', 0)->innertext));

    for ($i=1; $i < count($table->find('tr')); $i++)
    {
        $nip = $nik = '';

        $row = $table->find('tr', $i);        

        $nama = trim($row->find('td', 3)->innertext);
        $number = trim($row->find('td', 2)->innertext);        
        
        if ($number[0].$number[1] == '19')
            $nip = $number;
        else
            $nik = $number;

        $jabatan = trim($row->find('td', 4)->innertext);
        $prodi = trim($row->find('td', 5)->innertext);

        fputcsv($csv, [$faculty, $nip, $nik, $nama, $jabatan, $prodi]);
    }
}

echo 'Result saved to database.csv';

?>