<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;

class LeaveFormService
{
    public function generate(LeaveRequest $leaveRequest, Request $request)
    {
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', public_path('fonts/'));
        }

        $pdf = new Fpdi();
        $pdf->AddFont('CenturyGothic', '', 'gothic.php');
        $pdf->AddFont('CenturyGothic', 'B', 'gothicb.php');
        $pdf->AddFont('CenturyGothic', 'I', 'gothici.php');
        $pdf->AddFont('CenturyGothic', 'BI', 'gothicbi.php');

        $templatePath = storage_path('app/templates/CSC_Form_6_Template.pdf'); 
        $pageCount = $pdf->setSourceFile($templatePath);

        // --- PAGE 1: FRONT PAGE ---
        $page1Id = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($page1Id);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($page1Id);

        $pdf->SetFont('CenturyGothic', 'B', 8);
        $pdf->SetTextColor(0, 0, 0);

        // Department & Profile Metadata
        $department = $leaveRequest->employee->department->code ?? 'MITCS';
        $pdf->SetXY(30, 40); $pdf->Write(0, $department);
        $pdf->SetXY(90, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->last_name, 'UTF-8'));
        $pdf->SetXY(120, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->first_name, 'UTF-8'));
        
        $mi = $leaveRequest->employee->middle_initial ?? '';
        $formatted_mi = !empty($mi) ? mb_strtoupper($mi, 'UTF-8') . '.' : '';
        $pdf->SetXY(157, 40); $pdf->Write(0, $formatted_mi);

        $pdf->SetXY(37, 47); $pdf->Write(0, Carbon::parse($leaveRequest->date_of_filing)->format('M d, Y'));
        $pdf->SetXY(97, 47); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->position_code, 'UTF-8'));

        // Leave Types Checkboxes
        $leaveYPositions = [
            'Vacation Leave'                   => 68.2,
            'Mandatory/Forced Leave'           => 73.4,
            'Sick Leave'                       => 78.6,
            'Maternity Leave'                  => 83.8,
            'Paternity Leave'                  => 89,
            'Special Privilege Leave'          => 94.2,
            'Solo Parent Leave'                => 99.4,
            'Study Leave'                      => 104.6,
            '10-Day VAWC Leave'                => 109.7,
            'Rehabilitation Privilege'         => 114.8,
            'Special Leave Benefits for Women' => 120,
            'Special Emergency Leave'          => 125.2,
            'Adoption Leave'                   => 130.2,
        ];

        $type = $leaveRequest->leave_type;

        if ($type === 'Others') {
            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(40, 196);
            $pdf->Write(0, $leaveRequest->leave_type_others);
        } elseif (array_key_exists($type, $leaveYPositions)) {
            $pdf->SetXY(6, $leaveYPositions[$type]); 
            $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
            $pdf->SetFont('CenturyGothic', '', 10); 
        }

        // Leave Specific Details
        $detailYPositions = [
            'Within the Philippines' => 74,
            'Abroad'                 => 79.2,
            'In Hospital'            => 89.6,
            'Out Patient'            => 94.8,
        ];

        $category = $leaveRequest->leave_detail_category;

        if (array_key_exists($category, $detailYPositions)) {
            $y = $detailYPositions[$category];
            $pdf->SetXY(117.8, $y);
            $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3');

            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(150, $y);
            $pdf->Write(0, $leaveRequest->leave_detail_specifics);
        }

        // Days & Dates
        $pdf->SetFont('CenturyGothic', '', 10);
        $pdf->SetXY(30, 215); $pdf->Write(0, number_format($leaveRequest->working_days_applied, 1) . ' days');
        
        $pdf->SetXY(30, 230);
        $dates = Carbon::parse($leaveRequest->start_date)->format('M d, Y') . ' - ' . Carbon::parse($leaveRequest->end_date)->format('M d, Y');
        $pdf->Write(0, $dates);

        // Commutation
        $commutationY = $leaveRequest->commutation_requested ? 222 : 215;
        $pdf->SetXY(120, $commutationY);
        $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
        $pdf->SetFont('CenturyGothic', '', 10);

        // --- PAGE 2: BACK PAGE ---
        if ($pageCount > 1) {
            $page2Id = $pdf->importPage(2);
            $size2 = $pdf->getTemplateSize($page2Id);
            $pdf->AddPage($size2['orientation'], [$size2['width'], $size2['height']]);
            $pdf->useTemplate($page2Id);
        }

        $startDateStr = Carbon::parse($leaveRequest->start_date)->format('Ymd');
        $fileName = 'CSC_Form_6_' . $leaveRequest->employee->last_name . '_' . $startDateStr . '.pdf';
        
        $pdf->Output($request->has('download') ? 'D' : 'I', $fileName);
        exit;
    }
}