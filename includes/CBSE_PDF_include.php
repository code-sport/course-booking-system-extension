<?php

function cbse_cbse_pdf_install_and_update()
{
    $tcpdf_Folder = plugin_dir_path(__FILE__) . '../dependencies/';
    if (!is_dir($tcpdf_Folder))
    {
        mkdir($tcpdf_Folder, 0777, true);
    }

    $fpdf_file = cbse_get_tcpdf();
    if (!is_file($fpdf_file))
    {
        do_action('qm/debug', 'PDF library is not available under : {path}', ['path' => $fpdf_file]);
        $url = 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.4.2.zip';
        $zip_filename = 'TCPDF.zip';

        // WordPress Download
        $response = wp_remote_get($url, array('timeout' => 120,));
        do_action('qm/debug', 'wp_remote_get: {response}', ['response' => $response]);
        $body = wp_remote_retrieve_body($response);
        // Write the file using put_contents instead of fopen(), etc.
        $wp_filesystem = cbse_get_wp_filesystem();

        $wp_filesystem->put_contents($zip_filename, $body);

        // Extract
        $result = unzip_file($zip_filename, $tcpdf_Folder);
        if (is_wp_error($result))
        {
            do_action('qm/error', 'Could not extract TCPDF');
        }
        // Delete download
        unlink($zip_filename);
    }
}

function cbse_get_tcpdf(): string // TODO find a better way
{
    $tcpdf_folder = plugin_dir_path(__FILE__) . '../dependencies';

    $scan = scandir($tcpdf_folder);
    foreach ($scan as $scan_file)
    {
        if (substr($scan_file, 0, 6) === "TCPDF-")
        { //TODO Check if is a dictionary
            $tcpdf_folder .= '/' . $scan_file;
            break;
        }
    }

    $tpcPdfFile = realpath($tcpdf_folder . '/tcpdf.php');

    if (!is_file($tpcPdfFile))
    {
        cbse_cbse_pdf_install_and_update();
    }

    return $tpcPdfFile;
}

function cbse_get_wp_filesystem()
{
    global $wp_filesystem;

    if (is_null($wp_filesystem))
    {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }

    return $wp_filesystem;
}


require_once cbse_get_tcpdf();
require_once 'CBSE_PDF.php';
