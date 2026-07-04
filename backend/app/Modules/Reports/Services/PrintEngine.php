<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Support\ReportDefinition;

/**
 * The one Print / PDF engine. It renders a print-ready HTML document (A4/A5/
 * Letter, portrait/landscape, with logo/header/footer/watermark/signature) that
 * the browser prints to PDF — the single, centralized print layer for the whole
 * ERP. A binary HTML→PDF driver can be plugged in later without changing callers.
 */
class PrintEngine
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $options
     */
    public function render(ReportDefinition $definition, array $rows, array $options = []): string
    {
        $paper = (string) ($options['paper_size'] ?? 'a4');
        $orientation = (string) ($options['orientation'] ?? 'portrait');
        $title = $options['title'] ?? $definition->name;
        $header = (string) ($options['header'] ?? '');
        $footer = (string) ($options['footer'] ?? '');
        $watermark = (string) ($options['watermark'] ?? '');
        $signature = (string) ($options['signature'] ?? '');
        $logo = isset($options['logo']) ? '<img class="logo" src="'.$this->e((string) $options['logo']).'" alt="logo">' : '';

        $thead = '<tr>'.implode('', array_map(fn ($l) => '<th>'.$this->e($l).'</th>', array_values($definition->columns))).'</tr>';
        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>'.implode('', array_map(fn ($k) => '<td>'.$this->e((string) ($row[$k] ?? '')).'</td>', array_keys($definition->columns))).'</tr>';
        }

        $wm = $watermark !== '' ? '<div class="watermark">'.$this->e($watermark).'</div>' : '';
        $sig = $signature !== '' ? '<div class="signature">'.$this->e($signature).'</div>' : '';

        return '<!doctype html><html><head><meta charset="utf-8"><title>'.$this->e((string) $title).'</title><style>'
            .'@page{size:'.$paper.' '.$orientation.';margin:14mm}'
            .'body{font-family:Arial,Helvetica,sans-serif;color:#111;font-size:12px}'
            .'.rpt-header{display:flex;align-items:center;gap:12px;border-bottom:2px solid #0d47a1;padding-bottom:8px;margin-bottom:12px}'
            .'.logo{height:48px}h1{font-size:18px;margin:0}'
            .'table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:5px 7px;text-align:left}'
            .'th{background:#f1f5f9}.watermark{position:fixed;top:40%;left:20%;font-size:80px;color:rgba(0,0,0,.06);transform:rotate(-30deg);z-index:-1}'
            .'.rpt-footer{margin-top:14px;border-top:1px solid #ccc;padding-top:6px;color:#666;font-size:10px;display:flex;justify-content:space-between}'
            .'.signature{margin-top:36px;text-align:right;font-style:italic}'
            .'</style></head><body>'
            .$wm
            .'<div class="rpt-header">'.$logo.'<div><h1>'.$this->e((string) $title).'</h1>'
            .($header !== '' ? '<div>'.$this->e($header).'</div>' : '').'</div></div>'
            .'<table><thead>'.$thead.'</thead><tbody>'.$tbody.'</tbody></table>'
            .$sig
            .'<div class="rpt-footer"><span>'.$this->e($footer).'</span><span>'.count($rows).' row(s)</span></div>'
            .'</body></html>';
    }

    private function e(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES);
    }
}
