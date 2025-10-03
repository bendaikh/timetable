<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;

class GoogleSheetsService
{
    public function __construct()
    {
        // Simple service for public Google Sheets
    }

    /**
     * Extract spreadsheet ID from Google Sheets URL
     */
    public function extractSpreadsheetId($url)
    {
        $pattern = '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        throw new \Exception('Invalid Google Sheets URL format');
    }

    /**
     * Get data from Google Sheets using CSV export
     */
    public function getSheetData($url, $range = 'A:Z')
    {
        try {
            $spreadsheetId = $this->extractSpreadsheetId($url);
            
            // Convert range to sheet name and range format
            $sheetName = 'Sheet1'; // Default sheet name
            if (strpos($range, '!') !== false) {
                [$sheetName, $range] = explode('!', $range);
            }
            
            // Create CSV export URL for public sheets
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:csv&sheet={$sheetName}";
            
            // Make HTTP request to get CSV data
            $response = Http::timeout(30)->get($csvUrl);
            
            if (!$response->successful()) {
                $statusCode = $response->status();
                if ($statusCode === 403) {
                    throw new \Exception('Access denied. Please make sure your Google Sheet is publicly accessible (Anyone with the link can view).');
                } elseif ($statusCode === 404) {
                    throw new \Exception('Google Sheet not found. Please check the URL.');
                } else {
                    throw new \Exception("Failed to fetch data from Google Sheets (HTTP {$statusCode}). Please check the URL and make sure the sheet is publicly accessible.");
                }
            }
            
            $csvData = $response->body();
            
            if (empty($csvData)) {
                throw new \Exception('No data found in the Google Sheet');
            }
            
            // Parse CSV data
            $lines = str_getcsv($csvData, "\n");
            $values = [];
            
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $values[] = str_getcsv($line);
                }
            }
            
            if (empty($values)) {
                throw new \Exception('No valid data found in the Google Sheet');
            }
            
            return $values;
        } catch (\Exception $e) {
            Log::error('Google Sheets CSV Error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch data from Google Sheets: ' . $e->getMessage());
        }
    }

    /**
     * Get data from uploaded Google Sheets file
     */
    public function getSheetDataFromFile($file)
    {
        try {
            $filePath = $file->getPathname();
            $fileExtension = strtolower($file->getClientOriginalExtension());
            $fileName = $file->getClientOriginalName();
            
            // Validate file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new \Exception('File size exceeds 10MB limit. Please use a smaller file.');
            }
            
            // Handle different file formats
            switch ($fileExtension) {
                case 'csv':
                    return $this->parseCsvFile($filePath);
                case 'xlsx':
                case 'xls':
                    return $this->parseExcelFile($filePath);
                default:
                    throw new \Exception("Unsupported file format '{$fileExtension}'. Please upload a CSV, XLS, or XLSX file.");
            }
        } catch (\Exception $e) {
            Log::error('File parsing error: ' . $e->getMessage());
            throw new \Exception('Failed to parse uploaded file: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCsvFile($filePath)
    {
        $values = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (!empty(array_filter($data))) { // Skip empty rows
                    $values[] = $data;
                }
            }
            fclose($handle);
        }
        
        if (empty($values)) {
            throw new \Exception('No valid data found in the CSV file');
        }
        
        return $values;
    }

    /**
     * Parse Excel file (XLS/XLSX)
     */
    private function parseExcelFile($filePath)
    {
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            
            // Get the first worksheet
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row and column
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            $values = [];
            
            // Read all data from the worksheet
            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $worksheet->getCell($col . $row);
                    $cellValue = $cell->getCalculatedValue();
                    
                    // Handle date and time cells specially
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cellValue);
                        
                        // Check if this is a time-only value (date part is 1900-01-01 or 1900-01-00)
                        $datePart = $dateValue->format('Y-m-d');
                        if ($datePart === '1900-01-01' || $datePart === '1899-12-31') {
                            // This is a time-only value, format as time
                            $cellValue = $dateValue->format('H:i:s');
                        } else {
                            // This is a full date, format as date
                            $cellValue = $dateValue->format('m/d/Y');
                        }
                    }
                    
                    $rowData[] = $cellValue;
                }
                
                // Only add non-empty rows
                if (!empty(array_filter($rowData, function($value) {
                    return $value !== null && $value !== '';
                }))) {
                    $values[] = $rowData;
                }
            }
            
            if (empty($values)) {
                throw new \Exception('No valid data found in the Excel file');
            }
            
            return $values;
            
        } catch (Exception $e) {
            Log::error('Excel parsing error: ' . $e->getMessage());
            throw new \Exception('Failed to parse Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Parse prayer times data from Google Sheets
     * Expected format: Date, Fajr Beginning, Fajr Jamaat, Sunrise, Zuhr Beginning, Zuhr Jamaat, Asr Beginning, Asr Jamaat, Maghrib Beginning, Maghrib Jamaat, Isha Beginning, Isha Jamaat, Jumma 1, Jumma 2, hijri_date
     */
    public function parsePrayerTimesData($sheetData)
    {
        if (empty($sheetData)) {
            throw new \Exception('No data to parse');
        }

        $headerRow = array_shift($sheetData); // Remove header row
        $prayerTimes = [];
        $errors = [];

        // Map the headers to our expected format
        $headerMapping = $this->mapHeaders($headerRow);
        
        // Log the header mapping for debugging
        Log::info('Header mapping result:', [
            'headers' => $headerRow,
            'mapping' => $headerMapping
        ]);

        // Check if we have the required columns mapped
        $requiredColumns = ['date', 'fajr', 'zohar', 'asr', 'maghrib', 'isha'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $column) {
            if (!isset($headerMapping[$column]) || $headerMapping[$column] === null) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            $errors[] = "Could not find required columns: " . implode(', ', $missingColumns) . ". Please check your header format.";
        }

        foreach ($sheetData as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
            
            try {
                // Ensure we have at least the required columns
                if (count($row) < 6) {
                    $errors[] = "Row {$rowNumber}: Insufficient data columns";
                    continue;
                }

                // Parse date
                $dateValue = $row[$headerMapping['date']] ?? '';
                Log::info("Row {$rowNumber} - Date value: " . json_encode($dateValue) . " (type: " . gettype($dateValue) . ")");
                
                $date = $this->parseDate($dateValue, $rowNumber);
                if (!$date) {
                    $errors[] = "Row {$rowNumber}: Invalid date format";
                    continue;
                }

                // Debug: Log what values we're getting for each prayer time
                Log::info("Row {$rowNumber} - Raw values:", [
                    'date' => $row[$headerMapping['date']] ?? 'NOT FOUND',
                    'fajr' => $row[$headerMapping['fajr']] ?? 'NOT FOUND',
                    'zohar' => $row[$headerMapping['zohar']] ?? 'NOT FOUND',
                    'asr' => $row[$headerMapping['asr']] ?? 'NOT FOUND',
                    'maghrib' => $row[$headerMapping['maghrib']] ?? 'NOT FOUND',
                    'isha' => $row[$headerMapping['isha']] ?? 'NOT FOUND',
                ]);

                // Parse prayer times using the mapped columns
                $prayerTime = [
                    'date' => $date,
                    'fajr' => $this->parseTime($row[$headerMapping['fajr']] ?? '', $rowNumber, 'Fajr'),
                    'zohar' => $this->parseTime($row[$headerMapping['zohar']] ?? '', $rowNumber, 'Zohar'),
                    'asr' => $this->parseTime($row[$headerMapping['asr']] ?? '', $rowNumber, 'Asr'),
                    'maghrib' => $this->parseTime($row[$headerMapping['maghrib']] ?? '', $rowNumber, 'Maghrib'),
                    'isha' => $this->parseTime($row[$headerMapping['isha']] ?? '', $rowNumber, 'Isha'),
                    'sun_rise' => $this->parseTime($row[$headerMapping['sun_rise']] ?? '', $rowNumber, 'Sun Rise', true),
                    'jumah_1' => $this->parseTime($row[$headerMapping['jumah_1']] ?? '', $rowNumber, 'Jumah 1', true),
                    'jumah_2' => $this->parseTime($row[$headerMapping['jumah_2']] ?? '', $rowNumber, 'Jumah 2', true),
                ];

                $prayerTimes[] = $prayerTime;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        return [
            'prayer_times' => $prayerTimes,
            'errors' => $errors
        ];
    }

    /**
     * Map Google Sheets headers to our expected format
     */
    private function mapHeaders($headerRow)
    {
        $mapping = [
            'date' => 0,
            'fajr' => 1,
            'zohar' => 4,
            'asr' => 6,
            'maghrib' => 8,
            'isha' => 10,
            'sun_rise' => 3,
            'jumah_1' => 12,
            'jumah_2' => 13,
        ];

        // Try to auto-detect headers if they exist
        if (!empty($headerRow)) {
            $headerRow = array_map('strtolower', array_map('trim', $headerRow));
            
            // Look for date column
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'date') !== false && strpos($header, 'hijri') === false) {
                    $mapping['date'] = $index;
                    break;
                }
            }

            // Look for fajr column (handle variations like "Fajr Beginnin", "Fajr Beginning", etc.)
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'fajr') !== false && (strpos($header, 'begin') !== false || strpos($header, 'beginnin') !== false)) {
                    $mapping['fajr'] = $index;
                    break;
                }
            }

            // Look for zuhr/zohar column (handle variations like "Zuhr Beginnin", "Zuhr Beginning", etc.)
            foreach ($headerRow as $index => $header) {
                if ((strpos($header, 'zuhr') !== false || strpos($header, 'zohar') !== false) && (strpos($header, 'begin') !== false || strpos($header, 'beginnin') !== false)) {
                    $mapping['zohar'] = $index;
                    break;
                }
            }

            // Look for asr column (handle variations like "Asr Beginnin", "Asr Beginning", etc.)
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'asr') !== false && (strpos($header, 'begin') !== false || strpos($header, 'beginnin') !== false)) {
                    $mapping['asr'] = $index;
                    break;
                }
            }

            // Look for maghrib column (handle variations like "Maghrib Beginnin", "Maghrib Beginning", etc.)
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'maghrib') !== false && (strpos($header, 'begin') !== false || strpos($header, 'beginnin') !== false)) {
                    $mapping['maghrib'] = $index;
                    break;
                }
            }

            // Look for isha column (handle variations like "Isha Beginnin", "Isha Beginning", etc.)
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'isha') !== false && (strpos($header, 'begin') !== false || strpos($header, 'beginnin') !== false)) {
                    $mapping['isha'] = $index;
                    break;
                }
            }

            // Look for sunrise column
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'sunrise') !== false || strpos($header, 'sun rise') !== false) {
                    $mapping['sun_rise'] = $index;
                    break;
                }
            }

            // Look for jumma/jumah columns
            foreach ($headerRow as $index => $header) {
                if (strpos($header, 'jumma') !== false || strpos($header, 'jumah') !== false) {
                    if (strpos($header, '1') !== false) {
                        $mapping['jumah_1'] = $index;
                    } elseif (strpos($header, '2') !== false) {
                        $mapping['jumah_2'] = $index;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString, $rowNumber)
    {
        if (empty($dateString)) {
            throw new \Exception('Date is required');
        }

        $dateString = trim($dateString);
        
        // Log the date string for debugging
        Log::info("Parsing date: '{$dateString}' for row {$rowNumber}");

        // Try different date formats
        $formats = [
            'Y-m-d',           // 2025-01-01
            'm/d/Y',           // 1/1/2025
            'd/m/Y',           // 1/1/2025
            'Y/m/d',           // 2025/1/1
            'm-d-Y',           // 1-1-2025
            'd-m-Y',           // 1-1-2025
            'Y-m-d H:i:s',     // 2025-01-01 00:00:00
            'm/d/Y H:i:s',     // 1/1/2025 00:00:00
            'd/m/Y H:i:s',     // 1/1/2025 00:00:00
            'n/j/Y',           // 1/1/2025 (no leading zeros)
            'j/n/Y',           // 1/1/2025 (no leading zeros)
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                $result = $date->format('Y-m-d');
                Log::info("Successfully parsed date '{$dateString}' as '{$result}' using format '{$format}'");
                return $result;
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $result = date('Y-m-d', $timestamp);
            Log::info("Successfully parsed date '{$dateString}' as '{$result}' using strtotime");
            return $result;
        }

        // Try Excel date serial number (if it's a number)
        if (is_numeric($dateString)) {
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString);
            if ($excelDate) {
                $result = $excelDate->format('Y-m-d');
                Log::info("Successfully parsed Excel date '{$dateString}' as '{$result}'");
                return $result;
            }
        }

        Log::error("Failed to parse date '{$dateString}' for row {$rowNumber}");
        throw new \Exception("Invalid date format: '{$dateString}'. Expected formats: YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY, etc.");
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($timeString, $rowNumber, $prayerName, $optional = false)
    {
        if (empty($timeString)) {
            if ($optional) {
                return null;
            }
            throw new \Exception("{$prayerName} time is required");
        }

        $timeString = trim($timeString);

        // Check for zero time values (00:00:00, 0:00:00, etc.)
        if ($timeString === '00:00:00' || $timeString === '0:00:00' || $timeString === '00:00' || $timeString === '0:00') {
            if ($optional) {
                return null;
            }
            throw new \Exception("{$prayerName} time cannot be 00:00:00. Please provide a valid prayer time.");
        }

        // Try different time formats
        $formats = [
            'H:i',
            'H:i:s',
            'g:i A',
            'g:i:s A',
            'h:i A',
            'h:i:s A',
        ];

        foreach ($formats as $format) {
            $time = \DateTime::createFromFormat($format, $timeString);
            if ($time !== false) {
                $parsedTime = $time->format('H:i:s');
                // Double-check that the parsed time is not 00:00:00
                if ($parsedTime === '00:00:00') {
                    if ($optional) {
                        return null;
                    }
                    throw new \Exception("{$prayerName} time cannot be 00:00:00. Please provide a valid prayer time.");
                }
                return $parsedTime;
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($timeString);
        if ($timestamp !== false) {
            $parsedTime = date('H:i:s', $timestamp);
            // Double-check that the parsed time is not 00:00:00
            if ($parsedTime === '00:00:00') {
                if ($optional) {
                    return null;
                }
                throw new \Exception("{$prayerName} time cannot be 00:00:00. Please provide a valid prayer time.");
            }
            return $parsedTime;
        }

        throw new \Exception("Invalid {$prayerName} time format");
    }

    /**
     * Validate Google Sheets URL
     */
    public function validateUrl($url)
    {
        if (empty($url)) {
            throw new \Exception('Google Sheets URL is required');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid URL format');
        }

        if (!str_contains($url, 'docs.google.com/spreadsheets')) {
            throw new \Exception('URL must be a Google Sheets link');
        }

        try {
            $this->extractSpreadsheetId($url);
        } catch (\Exception $e) {
            throw new \Exception('Invalid Google Sheets URL format');
        }

        return true;
    }
}
