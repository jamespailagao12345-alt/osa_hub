<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrganizationProfileFileController extends Controller
{
    /**
     * Upload a file for an organization
     */
    public function upload(Request $request, $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);
        
        // Verify the user has access to this organization
        $user = auth()->user();
        $hasAccess = $this->checkOrganizationAccess($user, $organizationId);
        
        if (!$hasAccess) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to upload files for this organization.'
                ], 403);
            }
            return redirect()->route('admin.organizations.profile', $organizationId)
                ->with('file_error', 'You do not have access to upload files for this organization.');
        }
        
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls,csv,txt|max:20480', // 20MB max
            'file_category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        
        $file = $request->file('file');
        
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename = time() . '_' . $filename; // Add timestamp to prevent conflicts
        
        // Store file in organization-specific folder
        $folderPath = 'organizations/' . $organizationId;
        $path = $file->storeAs($folderPath, $filename, 'public');
        
        // Get file info
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $fileType = $this->detectFileType($mimeType, $file->getClientOriginalName());
        
        try {
            OrganizationFile::create([
                'organization_id' => $organizationId,
                'uploaded_by' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $fileType,
                'file_category' => $request->file_category,
                'description' => $request->description,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
            ]);
            
            Log::info('Organization profile file uploaded', [
                'organization_id' => $organizationId,
                'file_name' => $file->getClientOriginalName(),
                'file_category' => $request->file_category,
                'uploaded_by' => $user->id,
            ]);
            
            // Return JSON for AJAX requests, redirect for normal requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                    'file_name' => $file->getClientOriginalName(),
                    'file_category' => $request->file_category
                ]);
            }
            
            return redirect()->route('admin.organizations.profile', $organizationId)
                ->with('file_success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            // Delete file if database insert fails
            Storage::disk('public')->delete($path);
            
            Log::error('Failed to upload organization profile file', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage(),
            ]);
            
            // Return JSON for AJAX requests, redirect for normal requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file. Please try again.'
                ], 422);
            }
            
            return redirect()->route('admin.organizations.profile', $organizationId)
                ->with('file_error', 'Failed to upload file. Please try again.');
        }
    }

    /**
     * View a file in the browser (converted to PDF if not already PDF)
     */
    public function view($organizationId, $fileId)
    {
        $organization = Organization::findOrFail($organizationId);
        
        // Verify the user has access to this organization
        $user = auth()->user();
        $hasAccess = $this->checkOrganizationAccess($user, $organizationId);
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this organization.');
        }
        
        $file = OrganizationFile::where('organization_id', $organizationId)
            ->findOrFail($fileId);
        
        $path = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        // Security: verify path is within expected directory
        $realPath = realpath($path);
        $basePath = realpath(storage_path('app/public/organizations/' . $organizationId));
        
        if (!$realPath || !$basePath || !Str::startsWith($realPath, $basePath)) {
            abort(404, 'Invalid file path.');
        }
        
        // Get file MIME type and extension
        $mimeType = $file->mime_type ?? mime_content_type($realPath);
        $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
        
        // If file is already PDF, return as-is
        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            Log::info('Organization profile file viewed (PDF)', [
                'organization_id' => $organizationId,
                'file_id' => $fileId,
                'viewed_by' => $user->id,
            ]);
            
            return response()->file($realPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
            ]);
        }
        
        // Convert non-PDF files to PDF
        try {
            $pdfPath = $this->convertToPdf($realPath, $mimeType, $extension, $file->file_name);
            
            Log::info('Organization profile file converted to PDF and viewed', [
                'organization_id' => $organizationId,
                'file_id' => $fileId,
                'viewed_by' => $user->id,
                'original_mime_type' => $mimeType,
            ]);
            
            // Register cleanup function to delete temp PDF after response is sent
            register_shutdown_function(function() use ($pdfPath) {
                if (file_exists($pdfPath)) {
                    @unlink($pdfPath);
                }
            });
            
            // Return converted PDF
            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . pathinfo($file->file_name, PATHINFO_FILENAME) . '.pdf"',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to convert file to PDF', [
                'organization_id' => $organizationId,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Fallback: return original file
            return response()->file($realPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
            ]);
        }
    }
    
    /**
     * Convert a file to PDF
     */
    private function convertToPdf($filePath, $mimeType, $extension, $originalFilename)
    {
        $tempPdfPath = storage_path('app/temp/' . uniqid() . '_' . pathinfo($originalFilename, PATHINFO_FILENAME) . '.pdf');
        
        // Ensure temp directory exists
        $tempDir = dirname($tempPdfPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Handle different file types
        if (Str::startsWith($mimeType, 'image/')) {
            // Convert image to PDF
            $this->convertImageToPdf($filePath, $tempPdfPath);
        } elseif (Str::startsWith($mimeType, 'text/') || $extension === 'txt' || $extension === 'csv') {
            // Convert text file to PDF
            $this->convertTextToPdf($filePath, $tempPdfPath, $originalFilename);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            // Convert Excel to PDF
            $this->convertExcelToPdf($filePath, $tempPdfPath);
        } elseif (in_array($extension, ['doc', 'docx'])) {
            // For DOC/DOCX, we'll convert to text then to PDF (simple approach)
            // For full DOC/DOCX support, would need PhpWord library
            $this->convertTextToPdf($filePath, $tempPdfPath, $originalFilename);
        } else {
            // For other file types, try to read as text and convert
            $this->convertTextToPdf($filePath, $tempPdfPath, $originalFilename);
        }
        
        return $tempPdfPath;
    }
    
    /**
     * Convert image to PDF
     */
    private function convertImageToPdf($imagePath, $pdfPath)
    {
        // Check if Imagick is available
        if (extension_loaded('imagick')) {
            $imagick = new \Imagick($imagePath);
            $imagick->setImageFormat('pdf');
            $imagick->writeImage($pdfPath);
            $imagick->clear();
            $imagick->destroy();
        } else {
            // Fallback: Use dompdf with image embedded in HTML
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $html = '<html><body style="margin:0;padding:0;"><img src="data:' . $mimeType . ';base64,' . $imageData . '" style="max-width:100%;height:auto;" /></body></html>';
            
            $pdf = Pdf::loadHTML($html);
            $pdf->save($pdfPath);
        }
    }
    
    /**
     * Convert text file to PDF
     */
    private function convertTextToPdf($filePath, $pdfPath, $originalFilename)
    {
        $content = file_get_contents($filePath);
        
        // Escape HTML special characters
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
        // Preserve line breaks
        $content = nl2br($content);
        
        // Create HTML with proper formatting
        $html = '<html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
                    pre { white-space: pre-wrap; word-wrap: break-word; }
                </style>
            </head>
            <body>
                <h2>' . htmlspecialchars($originalFilename, ENT_QUOTES, 'UTF-8') . '</h2>
                <pre>' . $content . '</pre>
            </body>
        </html>';
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->save($pdfPath);
    }
    
    /**
     * Convert Excel file to PDF
     */
    private function convertExcelToPdf($filePath, $pdfPath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Convert to HTML table
            $html = '<html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 10px; padding: 10px; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h2>Excel File: ' . htmlspecialchars(basename($filePath), ENT_QUOTES, 'UTF-8') . '</h2>
                    <table>';
            
            // Get all data as array (more efficient than cell-by-cell)
            $data = $sheet->toArray(null, true, true, true);
            
            // Limit rows for performance (first 100 rows)
            $data = array_slice($data, 0, 100);
            
            // Add header row (first row)
            if (!empty($data)) {
                $html .= '<tr>';
                foreach (array_keys($data[array_key_first($data)]) as $col) {
                    $cellValue = $data[array_key_first($data)][$col] ?? '';
                    $html .= '<th>' . htmlspecialchars($cellValue, ENT_QUOTES, 'UTF-8') . '</th>';
                }
                $html .= '</tr>';
                
                // Add data rows (skip first row as it's the header)
                $firstRowKey = array_key_first($data);
                foreach ($data as $rowNum => $row) {
                    if ($rowNum == $firstRowKey) continue; // Skip header row
                    $html .= '<tr>';
                    foreach ($row as $col => $cellValue) {
                        $html .= '<td>' . htmlspecialchars($cellValue ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            
            $html .= '</table></body></html>';
            
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'landscape');
            $pdf->save($pdfPath);
        } catch (\Exception $e) {
            // Fallback: convert as text
            $this->convertTextToPdf($filePath, $pdfPath, basename($filePath));
        }
    }

    /**
     * Download a file
     */
    public function download($organizationId, $fileId)
    {
        $organization = Organization::findOrFail($organizationId);
        
        // Verify the user has access to this organization
        $user = auth()->user();
        $hasAccess = $this->checkOrganizationAccess($user, $organizationId);
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this organization.');
        }
        
        $file = OrganizationFile::where('organization_id', $organizationId)
            ->findOrFail($fileId);
        
        $path = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        // Security: verify path is within expected directory
        $realPath = realpath($path);
        $basePath = realpath(storage_path('app/public/organizations/' . $organizationId));
        
        if (!$realPath || !$basePath || !Str::startsWith($realPath, $basePath)) {
            abort(404, 'Invalid file path.');
        }
        
        Log::info('Organization profile file downloaded', [
            'organization_id' => $organizationId,
            'file_id' => $fileId,
            'downloaded_by' => $user->id,
        ]);
        
        return response()->download($realPath, $file->file_name);
    }

    /**
     * Delete a file
     */
    public function destroy($organizationId, $fileId)
    {
        $organization = Organization::findOrFail($organizationId);
        
        // Verify the user has access to this organization
        $user = auth()->user();
        $hasAccess = $this->checkOrganizationAccess($user, $organizationId);
        
        if (!$hasAccess) {
            return redirect()->route('admin.organizations.profile', $organizationId)
                ->with('file_error', 'You do not have access to delete files for this organization.');
        }
        
        $file = OrganizationFile::where('organization_id', $organizationId)
            ->findOrFail($fileId);
        
        // Only allow deletion if user uploaded the file or is admin
        if ($file->uploaded_by !== $user->id && (int)$user->role !== 4) {
            return redirect()->route('admin.organizations.profile', $organizationId)
                ->with('file_error', 'You can only delete files you uploaded.');
        }
        
        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // Delete database record
        $file->delete();
        
        Log::info('Organization profile file deleted', [
            'organization_id' => $organizationId,
            'file_id' => $fileId,
            'deleted_by' => $user->id,
        ]);
        
        return redirect()->route('admin.organizations.profile', $organizationId)
            ->with('file_success', 'File deleted successfully.');
    }

    /**
     * Check if user has access to the organization
     */
    private function checkOrganizationAccess($user, $organizationId)
    {
        // Admins have access to all organizations
        if ((int)($user->role ?? 0) === 4) {
            return true;
        }
        
        // Check if user is staff and has access to this organization
        if ((int)($user->role ?? 0) === 2) {
            $staff = \App\Models\Staff::where('email', $user->email)->first();
            
            if ($staff) {
                // Check single organization
                if ($staff->organization_id == $organizationId) {
                    return true;
                }
                
                // Check many-to-many organizations
                if ($staff->organizations()->where('organizations.id', $organizationId)->exists()) {
                    return true;
                }
            }
            
            // Check user's direct organization
            if ($user->organization_id == $organizationId) {
                return true;
            }
            
            // Check user's other organizations
            if (method_exists($user, 'otherOrganizations')) {
                if ($user->otherOrganizations()->where('organizations.id', $organizationId)->exists()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Detect file type based on MIME type and filename
     */
    private function detectFileType($mimeType, $filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check for Personal Data Sheet indicators
        if (stripos($filename, 'personal') !== false || 
            stripos($filename, 'data sheet') !== false ||
            stripos($filename, 'pds') !== false) {
            return 'personal_data_sheet';
        }
        
        // Check by MIME type
        if (Str::startsWith($mimeType, 'image/')) {
            return 'image';
        }
        
        if ($mimeType === 'application/pdf') {
            return 'document';
        }
        
        if (in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'document';
        }
        
        if (in_array($mimeType, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) {
            return 'spreadsheet';
        }
        
        return 'document';
    }
}

