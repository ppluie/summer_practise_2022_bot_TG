<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

define('FPDF_FONTPATH',"fpdf/font/");
require_once(__DIR__."/Groups.php");
require_once( "fpdf/fpdf.php" );
require_once(__DIR__."/functions.php");
require_once(__DIR__."/DB.php");
require_once(__DIR__."/Tgram.php");


global $groups_table2;

// Создает pdf файлы

function CreatePDF($group,$chat_id): FPDF
{
    $db = new DB();
    $name_group = GetGroup2($group);
    $users = $db->GetAllUsers($group);


    $pdf=new FPDF('L', 'mm', 'A4'); 
    $pdf->AddPage(); 
    $pdf->AddFont('Arial','','arial.php');
    $pdf->SetDrawColor( 0, 0, 0 );
    $pdf->SetFont( 'Arial');
    $pdf->SetTextColor( 0, 0, 0);
    $pdf->SetFillColor( 255, 255, 255);

    $text = iconv('utf-8', 'windows-1251', $name_group);
    $pdf->SetFontSize(12);
    $pdf->Cell( 23, 12, $text, 1, 0, 'L', true );
    $pdf->SetFontSize(8);

    foreach ($users as $user)
    {
        $name = $user['Name'];
        $att = $user['Attendance'];
        $attt = explode("\n", $att);
        foreach ($attt as $a)
        {
            $at = explode("/", $a);
            $id = (int) $at[3];
            if ($id == $chat_id) {
                $s = $at[0]." ".$at[1];
                $pdf->Cell( 23, 12, $s, 1, 0, 'C', true );
            }
        }
        break;
    }
    $pdf->Ln( 12 );

    $fill = false;
    $row = 0;


    foreach ($users as $user)
    {
        $pdf->SetFontSize(8);
        $name = $user['Name'];
        $text = iconv('utf-8', 'windows-1251', $name);
        $pdf->Cell( 23, 12, $text, 1, 0, 'C', true );
        $att = $user['Attendance'];
        $attt = explode("\n", $att);
        foreach ($attt as $a)
        {
            $at = explode("/", $a);
            $id = (int) $at[3];
            if ($id == $chat_id) {
                $s = $at[2];
                if ($s == "Геолокация не была отправлена") {
                    $pdf->Cell( 23, 12, "H", 1, 0, 'C', true );
                }else{
                $pdf->Cell( 23, 12, $s, 1, 0, 'C', true );}
            }
        }
    $fill = !$fill;
    $pdf->Ln( 12 );
    }

    //$pdf->Output("Table.pdf", "F");
    return $pdf;

}