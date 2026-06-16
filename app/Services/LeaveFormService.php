<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
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
        $department = $leaveRequest->employee->division->department->code ?? ' ';
        $pdf->SetXY(30, 40); $pdf->Write(0, $department);
        $pdf->SetXY(90, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->last_name, 'UTF-8'));
        $pdf->SetXY(120, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->first_name, 'UTF-8'));
        
        $mi = $leaveRequest->employee->middle_initial ?? '';
        $formatted_mi = !empty($mi) ? mb_strtoupper($mi, 'UTF-8') . '.' : '';
        $pdf->SetXY(157, 40); $pdf->Write(0, $formatted_mi);

        $pdf->SetXY(37, 47); $pdf->Write(0, Carbon::parse($leaveRequest->date_of_filing)->format('M d, Y'));
        $pdf->SetXY(97, 47); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->position_code, 'UTF-8'));

        // DYNAMIC SALARY DISPLAY
        if ($leaveRequest->employee->salary) {
            $pdf->SetXY(156, 47); 
            $pdf->Write(0, 'PhP ' . number_format($leaveRequest->employee->salary, 2));
        }

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

        $leaveName = $leaveRequest->leaveType->leave_type_name ?? $leaveRequest->leaveType->name ?? 'Others';
        $leaveCode = $leaveRequest->leaveType->code ?? 'OTHERS';

        $matchedCheckbox = 'Others';
        foreach ($leaveYPositions as $key => $yPos) {
            $search = str_replace(' Leave', '', $key); 
            if (stripos($leaveName, $search) !== false) {
                $matchedCheckbox = $key;
                break;
            }
        }

        if (str_contains($leaveName, 'Others') || $matchedCheckbox === 'Others') {
            $pdf->SetFont('CenturyGothic', '', 8);
            $pdf->SetXY(10, 146);
            $pdf->Write(0, $leaveRequest->leave_detail_category); 
        } else {
            $pdf->SetXY(6, $leaveYPositions[$matchedCheckbox]); 
            $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
            $pdf->SetFont('CenturyGothic', '', 10); 
        }

        // Leave Specific Details (Word Wrap Logic)
        $detailYPositions = [
            'Within the Philippines'         => 74,
            'Abroad'                         => 79.2,
            'In Hospital'                    => 89.6,
            'Out Patient'                    => 94.8,
            'Completion of Master\'s Degree' => 125.5,
            'BAR/Board Examination Review'   => 130.7,
            'Monetization of Leave Credits'  => 140.6,
            'Terminal Leave'                 => 145.8,
        ];

        $category = $leaveRequest->leave_detail_category;
        $text = $leaveRequest->leave_detail_specifics; 

        if (array_key_exists($category, $detailYPositions)) {
            $y = $detailYPositions[$category];
            
            $pdf->SetXY(117.9, $y);
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3');

            $pdf->SetFont('CenturyGothic', '', 6);

            $startXLine1 = 156;  
            $maxWidthLine1 = 44; 
            $startXLine2 = 121;  
            $yLine2 = $y + 5;    

            if ($pdf->GetStringWidth($text) <= $maxWidthLine1) {
                $pdf->SetXY($startXLine1, $y);
                $pdf->Write(0, $text);
            } 
            else {
                $words = explode(' ', $text);
                $line1 = '';
                $line2 = '';

                foreach ($words as $word) {
                    if ($pdf->GetStringWidth($line1 . $word . ' ') <= $maxWidthLine1 && empty($line2)) {
                        $line1 .= $word . ' ';
                    } else {
                        $line2 .= $word . ' ';
                    }
                }

                $pdf->SetXY($startXLine1, $y);
                $pdf->Write(0, trim($line1));
                $pdf->SetXY($startXLine2, $yLine2);
                $pdf->Write(0, trim($line2));
            }
        } 
        elseif (stripos($leaveName, 'Women') !== false) {
            $pdf->SetFont('CenturyGothic', '', 6);
            $y = 110;          
            $startXLine1 = 156;  
            $maxWidthLine1 = 44; 
            $startXLine2 = 121;  
            $yLine2 = $y + 5.2;  
            
            $words = explode(' ', $text);
            $line1 = '';
            $line2 = '';

            foreach ($words as $word) {
                if ($pdf->GetStringWidth($line1 . $word . ' ') <= $maxWidthLine1 && empty($line2)) {
                    $line1 .= $word . ' ';
                } else {
                    $line2 .= $word . ' ';
                }
            }

            $pdf->SetXY($startXLine1, $y);
            $pdf->Cell(0, 0, trim($line1), 0, 0, 'L');

            if (!empty($line2)) {
                $pdf->SetXY($startXLine2, $yLine2);
                $pdf->Cell(0, 0, trim($line2), 0, 0, 'L');
            }
        }

        // --- DYNAMIC DAYS & DATES LOGIC ---
        $pdf->SetFont('CenturyGothic', '', 8);
        $pdf->SetXY(10, 158); 
        $pdf->Write(0, number_format($leaveRequest->working_days_applied, 2) . ' days');
        
        // OPTIMIZED: Run structural date ranges compression algorithm
        $dates = $this->formatInclusiveDates($leaveRequest);

        $pdf->SetXY(10, 168);
        $pdf->Write(0, $dates);
        
        $commutationY = $leaveRequest->commutation_requested ? 164 : 158;
        $pdf->SetXY(117.8, $commutationY);
        $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
        $pdf->SetFont('CenturyGothic', '', 10);

        $asOfDate = Carbon::now()->subMonth()->endOfMonth()->format('F d, Y');
        $pdf->SetFont('CenturyGothic', '', 8);
        $pdf->SetXY(45, 191); 
        $pdf->Write(0, $asOfDate);

        // SAFE SNAPSHOT & DETAILS-BASED BALANCE LOGIC
        $vlOriginal = (float)($leaveRequest->vl_balance_snapshot ?? 0);
        $slOriginal = (float)($leaveRequest->sl_balance_snapshot ?? 0);

        $vlDeduction = 0;
        $slDeduction = 0;

        if ($leaveRequest->details && $leaveRequest->details->count() > 0) {
            if (in_array($leaveCode, ['VL', 'FL'])) {
                $vlDeduction = $leaveRequest->details->where('is_with_pay', true)->sum('day_fraction');
            } elseif ($leaveCode === 'SL') {
                $slDeduction = $leaveRequest->details->where('is_with_pay', true)->sum('day_fraction');
            }
        } else {
            $daysApplied = (float)$leaveRequest->working_days_applied;
            if (in_array($leaveCode, ['VL', 'FL'])) {
                $vlDeduction = $daysApplied;
            } elseif ($leaveCode === 'SL') {
                $slDeduction = $daysApplied;
            }
        }

        $vlEarned = $vlOriginal; 
        $slEarned = $slOriginal;

        $vlBalance = $vlOriginal - $vlDeduction;
        $slBalance = $slOriginal - $slDeduction;

        $pdf->SetFont('CenturyGothic', '', 8);

        $vlColumnX = 45;  
        $slColumnX = 73;  
        $columnWidth = 20; 

        $rowEarnedY = 201.5;      
        $rowDeductionY = 206.5;   
        $rowBalanceY = 211.5;     

        $vlEarnedText = number_format($vlEarned, 2);
        $slEarnedText = number_format($slEarned, 2);
        
        $vlDeductionText = $vlDeduction > 0 ? number_format($vlDeduction, 2) : '0';
        $slDeductionText = $slDeduction > 0 ? number_format($slDeduction, 2) : '0';
        
        $vlBalanceText = number_format($vlBalance, 2);
        $slBalanceText = number_format($slBalance, 2);

        $pdf->SetXY($vlColumnX, $rowEarnedY); 
        $pdf->Cell($columnWidth, 0, $vlEarnedText, 0, 0, 'C'); 
        $pdf->SetXY($slColumnX, $rowEarnedY); 
        $pdf->Cell($columnWidth, 0, $slEarnedText, 0, 0, 'C');

        $pdf->SetXY($vlColumnX, $rowDeductionY); 
        $pdf->Cell($columnWidth, 0, $vlDeductionText, 0, 0, 'C');
        $pdf->SetXY($slColumnX, $rowDeductionY); 
        $pdf->Cell($columnWidth, 0, $slDeductionText, 0, 0, 'C');

        $pdf->SetXY($vlColumnX, $rowBalanceY); 
        $pdf->Cell($columnWidth, 0, $vlBalanceText, 0, 0, 'C');
        $pdf->SetXY($slColumnX, $rowBalanceY); 
        $pdf->Cell($columnWidth, 0, $slBalanceText, 0, 0, 'C');

        // Admin responses
        if ($leaveRequest->status === 'approved') {
            $pdf->SetXY(117.8, 191); 
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3'); 
            
            $pdf->SetFont('CenturyGothic', '', 8);

            if ($leaveRequest->days_with_pay > 0) {
                $pdf->SetXY(12, 235);
                $pdf->Write(0, $leaveRequest->days_with_pay);
            }
            if ($leaveRequest->days_without_pay > 0) {
                $pdf->SetXY(12, 240); 
                $pdf->Write(0, $leaveRequest->days_without_pay);
            }
        } 
        elseif ($leaveRequest->status === 'disapproved') {
            $pdf->SetXY(117.8, 245); 
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3');
            
            $pdf->SetFont('CenturyGothic', '', 8);

            if (!empty($leaveRequest->disapproval_reason)) {
                $pdf->SetXY(121, 250); 
                $pdf->Write(0, $leaveRequest->disapproval_reason);
            }
        }

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

    /**
     * NEW: Compresses chronological leave dates into a clean, month-grouped format.
     * Prevents text overflows on the physical PDF bounding boxes.
     */
    private function formatInclusiveDates(LeaveRequest $leaveRequest): string
    {
        // Gather exact unique days filed from the database breakdown
        $dates = $leaveRequest->details()
            ->orderBy('leave_date', 'asc')
            ->pluck('leave_date')
            ->map(fn($d) => Carbon::parse($d))
            ->toArray();

        // Fallback safety layer for legacy entries
        if (empty($dates)) {
            $start = Carbon::parse($leaveRequest->start_date);
            $end = Carbon::parse($leaveRequest->end_date);
            if ($start->equalTo($end)) {
                return $start->format('M d, Y');
            }
            return $start->format('M d, Y') . ' - ' . $end->format('M d, Y');
        }

        // Group dates by Year, then Month
        $grouped = [];
        foreach ($dates as $date) {
            $year = $date->year;
            $month = $date->format('M'); // Compresses down to short forms like 'Jun', 'Sep'
            $day = $date->day;
            $grouped[$year][$month][] = $day;
        }

        $yearStrings = [];
        $totalYears = count($grouped);

        foreach ($grouped as $year => $months) {
            $monthStrings = [];
            foreach ($months as $month => $days) {
                sort($days);
                $ranges = [];
                $start = $days[0];
                $end = $days[0];

                // Compress consecutive numbers (e.g., 1, 2, 3 becomes "1-3")
                for ($i = 1; $i < count($days); $i++) {
                    if ($days[$i] === $end + 1) {
                        $end = $days[$i];
                    } else {
                        $ranges[] = ($start === $end) ? $start : "$start-$end";
                        $start = $days[$i];
                        $end = $days[$i];
                    }
                }
                $ranges[] = ($start === $end) ? $start : "$start-$end";

                $monthStrings[] = $month . ' ' . implode(', ', $ranges);
            }

            if ($totalYears === 1) {
                $yearStrings[] = implode('; ', $monthStrings);
            } else {
                $yearStrings[] = implode('; ', $monthStrings) . ", $year";
            }
        }

        $finalString = implode('; ', $yearStrings);
        if ($totalYears === 1) {
            $firstYear = array_key_first($grouped);
            $finalString .= ", $firstYear";
        }

        // BOX WIDTH PROTECTION RULE
        // If text length exceeds 60 characters, default to a summary notice to avoid visual layout overflow.
        if (strlen($finalString) > 60) {
            return "Various Dates (See Attached Details)";
        }

        return $finalString;
    }
}