<?php
/**
 * Assignment 3 - EAI
 * Web Scrapper
 * 
 * 1306382852 - Hari
 * 1306464114 - Jundi Ahmad Alwan
 * 1306404645 - Muhammad Azki Darmawan
 * 
 * github: https://github.com/jundialwan/eai-tugas3
 *  
 */

include 'simple_html_dom.php';

function getHtml($query)
{
    $ctx = stream_context_create(['http' => ['header' => 'User-Agent:MyAgent/1.0\r\n']]);
    $header = file_get_contents('http://www.unsri.ac.id/'.$query, false, $ctx);

    $html = new simple_html_dom();
    $html->load($header);

    return $html;
}

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
        $row = $table->find('tr', $i);            
        $number = trim($row->find('td', 2)->innertext);

        fputcsv($csv, [
            $faculty, // fakultas 
            ($number[0].$number[1] == '19') ? $number : '', // nip
            ($number[0].$number[1] != '19') ? $number : '', // nik
            trim($row->find('td', 3)->innertext), // nama
            trim($row->find('td', 4)->innertext), // jabatan
            trim($row->find('td', 5)->innertext)  // prodi
        ]);
    }
}

echo 'Result saved to database.csv';

?>