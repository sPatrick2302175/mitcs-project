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

        // Department & Profile Metadata (Updated to reach through division)
        $department = $leaveRequest->employee->division->department->code ?? 'MITCS';
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

        // Ensure we check the name of the dynamic leave type relation
        $type = $leaveRequest->leaveType->name ?? 'Others';

        if ($type === 'Others') {
            $pdf->SetFont('CenturyGothic', '', 8);
            $pdf->SetXY(10, 146);
            $pdf->Write(0, $leaveRequest->leave_detail_category); // Adjusted to grab details
        } elseif (array_key_exists($type, $leaveYPositions)) {
            $pdf->SetXY(6, $leaveYPositions[$type]); 
            $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
            $pdf->SetFont('CenturyGothic', '', 10); 
        }

        // Leave Specific Details (Word Wrap Logic unchanged)
        $detailYPositions = [
            'Within the Philippines' => 74,
            'Abroad'                 => 79.2,
            'In Hospital'            => 89.6,
            'Out Patient'            => 94.8,
            'Completion of Master\'s Degree' => 125.5,
            'BAR/Board Examination Review' => 130.7,
            'Monetization of Leave Credits' => 140.6,
            'Terminal Leave'           => 145.8,
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
        elseif ($type === 'Special Leave Benefits for Women') {
            
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
        $pdf->Write(0, number_format($leaveRequest->working_days_applied, 1) . ' days');
        
        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);
        
        $calendarDaysSpan = $startDate->diffInDays($endDate) + 1;
        $daysApplied = (float)$leaveRequest->working_days_applied;
        $weekendDays = 0;
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            if ($tempDate->isWeekend()) {
                $weekendDays++;
            }
            $tempDate->addDay();
        }

        if ($calendarDaysSpan === 1 || $daysApplied === 1.0) {
            $dates = $startDate->format('M d, Y'); 
        } 
        elseif ($daysApplied === (float)($calendarDaysSpan - $weekendDays)) {
            $dates = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        } 
        else {
            $formattedDates = $leaveRequest->details()
                ->orderBy('leave_date', 'asc')
                ->pluck('leave_date')
                ->map(fn($date) => Carbon::parse($date)->format('M d, Y'))
                ->toArray();

            $totalDatesCount = count($formattedDates);
            if ($totalDatesCount === 1) {
                $dates = $formattedDates[0];
            } elseif ($totalDatesCount === 2) {
                $dates = implode(' & ', $formattedDates);
            } else {
                $lastDate = array_pop($formattedDates);
                $dates = implode(', ', $formattedDates) . ', & ' . $lastDate;
            }
        }

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

        // === FIXED BALANCE LOGIC ===
        // Fetch specific dynamic leave types to identify ID bounds
        $vlType = LeaveType::where('name', 'Vacation Leave')->first();
        $slType = LeaveType::where('name', 'Sick Leave')->first();

        // Safely retrieve the row records mapped to this employee
        $vlBalanceRecord = $leaveRequest->employee->leaveBalances()->where('leave_type_id', $vlType?->id)->first();
        $slBalanceRecord = $leaveRequest->employee->leaveBalances()->where('leave_type_id', $slType?->id)->first();

        $vlBalance = floatval($vlBalanceRecord?->balance ?? 0);
        $slBalance = floatval($slBalanceRecord?->balance ?? 0);

        $daysApplied = floatval($leaveRequest->working_days_applied);
        $vlDeduction = 0;
        $slDeduction = 0;

        if (in_array($type, ['Vacation Leave', 'Mandatory/Forced Leave'])) {
            $vlDeduction = $daysApplied;
        } elseif ($type === 'Sick Leave') {
            $slDeduction = $daysApplied;
        }

        $vlEarned = $vlBalance + $vlDeduction;
        $slEarned = $slBalance + $slDeduction;

        $pdf->SetFont('CenturyGothic', '', 8);

        $vlColumnX = 45;  
        $slColumnX = 73;  
        $columnWidth = 20; 

        $rowEarnedY = 201.5;      
        $rowDeductionY = 206.5;   
        $rowBalanceY = 211.5;     

        $vlEarnedText = number_format($vlEarned, 1);
        $slEarnedText = number_format($slEarned, 1);
        
        $vlDeductionText = $vlDeduction > 0 ? number_format($vlDeduction, 1) : '0';
        $slDeductionText = $slDeduction > 0 ? number_format($slDeduction, 1) : '0';
        
        $vlBalanceText = number_format($vlBalance, 1);
        $slBalanceText = number_format($slBalance, 1);

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

        //admin responses
        $pdf->SetFont('CenturyGothic', '', 8);

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
}