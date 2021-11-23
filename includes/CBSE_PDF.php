<?php

namespace CBSE;

use TCPDF;

class CBSE_PDF extends TCPDF
{
    protected string $footer_text = '';
    protected string $footer_status = '';


    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $date_time = current_datetime();
        $this->footer_status = wp_date(get_option('date_format'), $date_time->getTimestamp()) . ' ' . wp_date(get_option('time_format'), $date_time->getTimestamp());
    }

    public function Footer()
    {
        $cur_y = $this->y;
        $this->SetTextColorArray($this->footer_text_color);
        //set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
        //print document barcode
        $barcode = $this->getBarcode();
        if (!empty($barcode))
        {
            $this->Ln($line_width);
            $barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = array('position' => $this->rtl ? 'R' : 'L', 'align' => $this->rtl ? 'R' : 'L', 'stretch' => false, 'fitwidth' => true, 'cellfitalign' => '', 'border' => false, 'padding' => 0, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'text' => false);
            $this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
        }
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups))
        {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | ' . $this->footer_status;
        }
        else
        {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias() . ' | ' . $this->footer_status;
        }
        $this->SetY($cur_y);
        //Print page number
        if ($this->getRTL())
        {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
            $this->Cell(0, 0, $this->footer_text, 'T', 0, 'R');
        }
        else
        {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->footer_text, 'T', 0, 'L');
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
    }

    public function setFooterText(string $text)
    {
        $this->footer_text = $text;
    }
}
