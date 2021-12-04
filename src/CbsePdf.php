<?php

namespace CBSE;

use TCPDF;

class CbsePdf extends TCPDF
{
    protected string $footerText = '';
    protected string $footerStatus = '';


    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $dateTime = current_datetime();
        $this->footerStatus = wp_date(get_option('date_format'), $dateTime->getTimestamp()) . ' ' . wp_date(get_option('time_format'), $dateTime->getTimestamp());
    }

    public function Footer()
    {
        $curY = $this->y;
        $this->SetTextColorArray($this->footer_text_color);
        //set style for cell border
        $lineWidth = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $lineWidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
        //print document barcode
        $barcode = $this->getBarcode();
        if (!empty($barcode))
        {
            $this->Ln($lineWidth);
            $barcodeWidth = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = array('position' => $this->rtl ? 'R' : 'L', 'align' => $this->rtl ? 'R' : 'L', 'stretch' => false, 'fitwidth' => true, 'cellfitalign' => '', 'border' => false, 'padding' => 0, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'text' => false);
            $this->write1DBarcode($barcode, 'C128', '', $curY + $lineWidth, '', (($this->footer_margin / 3) - $lineWidth), 0.3, $style, '');
        }
        $wPage = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups))
        {
            $pagenumtxt = $wPage . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | ' . $this->footerStatus;
        }
        else
        {
            $pagenumtxt = $wPage . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias() . ' | ' . $this->footerStatus;
        }
        $this->SetY($curY);
        //Print page number
        if ($this->getRTL())
        {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
            $this->Cell(0, 0, $this->footerText, 'T', 0, 'R');
        }
        else
        {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->footerText, 'T', 0, 'L');
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
    }

    public function setFooterText(string $text)
    {
        $this->footerText = $text;
    }
}
