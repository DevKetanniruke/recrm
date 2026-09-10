<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Stream a CSV download for a given report dataset.
     */
    public function exportCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
