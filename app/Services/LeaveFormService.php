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
            $pdf->SetFont('CenturyGothic', '', 8);
            $pdf->SetXY(10, 146);
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
            'Completion of Master\'s Degree' => 125.5,
            'BAR/Board Examination Review' => 130.7,
            'Monetization of Leave Credits' => 140.6,
            'Terminal Leave'           => 145.8,
        ];

        $category = $leaveRequest->leave_detail_category;
        $text = $leaveRequest->leave_detail_specifics; // Store text in a variable

        if (array_key_exists($category, $detailYPositions)) {
            $y = $detailYPositions[$category];
            
            // Draw Checkmark
            $pdf->SetXY(117.9, $y);
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3');

            // Set font for the text
            $pdf->SetFont('CenturyGothic', '', 6);

            // --- CUSTOM WORD WRAP LOGIC ---
            $startXLine1 = 156;  // Start of the first underline
            $maxWidthLine1 = 44; // Max width before hitting the right edge (Adjust if needed)
            
            $startXLine2 = 121;  // The X position where the second underline starts (Aligns with the checkbox)
            $yLine2 = $y + 5;    // Drop down to the second line (Adjust the +5 based on line spacing)

            // 1. If the whole text fits on the first line, just write it
            if ($pdf->GetStringWidth($text) <= $maxWidthLine1) {
                $pdf->SetXY($startXLine1, $y);
                $pdf->Write(0, $text);
            } 
            // 2. If it overflows, split it across two lines
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

                // Print the first half on Line 1
                $pdf->SetXY($startXLine1, $y);
                $pdf->Write(0, trim($line1));

                // Print the remaining text on Line 2, shifted to the left
                $pdf->SetXY($startXLine2, $yLine2);
                $pdf->Write(0, trim($line2));
            }
        } 
        // --- Special Leave Benefits for Women (No Checkbox) ---
        elseif ($leaveRequest->leave_type === 'Special Leave Benefits for Women') {
            
            $pdf->SetFont('CenturyGothic', '', 6);

            // --- COORDINATES FOR THIS SPECIFIC SECTION ---
            $y = 110;          // The Y position of the top "(Specify Illness)" line
            
            $startXLine1 = 156;  // Starts further right to make room for "(Specify Illness)"
            $maxWidthLine1 = 44; // Safe boundary limit before the right margin
            
            $startXLine2 = 121;  // Lines up with your second underline
            $yLine2 = $y + 5.2;  // Drop down to the second line
            
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

            // Print Line 1 using Cell() to anchor it firmly to the right side
            $pdf->SetXY($startXLine1, $y);
            $pdf->Cell(0, 0, trim($line1), 0, 0, 'L');

            // Print Line 2 using Cell() to block it from flying over to X=10
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
        
        // Calculate total calendar duration
        $calendarDaysSpan = $startDate->diffInDays($endDate) + 1;
        $daysApplied = (float)$leaveRequest->working_days_applied;

        // Scenario 1: Single day leave
        if ($calendarDaysSpan === 1 || $daysApplied === 1.0) {
            $dates = $startDate->format('M d, Y'); 
        } 
        // Scenario 2: Continuous ordered range (e.g., Aug 1 to Aug 5)
        elseif ($daysApplied === (float)$calendarDaysSpan) {
            $dates = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        } 
        // Scenario 3: Staggered/Skipped days (Plucking individual dates from relation)
        else {
            // Fetch and sort every separate selected date from your LeaveRequestDetail relationship
            $formattedDates = $leaveRequest->details()
                ->orderBy('leave_date', 'asc')
                ->pluck('leave_date')
                ->map(fn($date) => Carbon::parse($date)->format('M d, Y'))
                ->toArray();

            $totalDatesCount = count($formattedDates);

            if ($totalDatesCount === 1) {
                $dates = $formattedDates[0];
            } elseif ($totalDatesCount === 2) {
                // Example: "Aug 01, 2026 & Aug 08, 2026"
                $dates = implode(' & ', $formattedDates);
            } else {
                // Example: "Aug 01, 2026, Aug 08, 2026, & Sep 01, 2026"
                $lastDate = array_pop($formattedDates);
                $dates = implode(', ', $formattedDates) . ', & ' . $lastDate;
            }
        }

        $pdf->SetXY(10, 168);
        $pdf->Write(0, $dates);
        

        // 6.D -- Commutation
        $commutationY = $leaveRequest->commutation_requested ? 164 : 158;
        $pdf->SetXY(117.8, $commutationY);
        $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 
        $pdf->SetFont('CenturyGothic', '', 10);

        // 7.A -- As of section:
        // Dynamically calculate the last day of the previous month
        $asOfDate = Carbon::now()->subMonth()->endOfMonth()->format('F d, Y');
        $pdf->SetFont('CenturyGothic', '', 8);
        $pdf->SetXY(45, 191); 
        $pdf->Write(0, $asOfDate);


        // 1. Get current balances from the employee relationship.
        // Since the database already reflects the deduction, these are our FINAL balances.
        $vlBalance = floatval($leaveRequest->employee->vacation_leave_balance ?? 0);
        $slBalance = floatval($leaveRequest->employee->sick_leave_balance ?? 0);

        // 2. Determine deductions based on the leave type applied for
        $daysApplied = floatval($leaveRequest->working_days_applied);
        $vlDeduction = 0;
        $slDeduction = 0;

        $type = $leaveRequest->leave_type;

        // In CSC rules, Vacation Leave and Forced Leave usually deduct from the VL balance.
        // Sick Leave deducts from the SL balance.
        if (in_array($type, ['Vacation Leave', 'Mandatory/Forced Leave'])) {
            $vlDeduction = $daysApplied;
        } elseif ($type === 'Sick Leave') {
            $slDeduction = $daysApplied;
        }

        // 3. Calculate "Total Earned" (the past balance) by adding the deduction BACK to the current balance
        $vlEarned = $vlBalance + $vlDeduction;
        $slEarned = $slBalance + $slDeduction;

        // 4. Write to the PDF
        $pdf->SetFont('CenturyGothic', '', 8);

        // --- COORDINATES & BOUNDING BOXES ---
        // Note: These X coordinates now represent the LEFT EDGE of the invisible centering box.
        // You may need to decrease 50 and 78 slightly so the box fits perfectly inside the table borders.
        $vlColumnX = 45;  
        $slColumnX = 73;  
        
        // Define the width of the cell to find the exact center. Adjust if needed!
        $columnWidth = 20; 

        $rowEarnedY = 201.5;      // Y coordinate for TOTAL EARNED row
        $rowDeductionY = 206.5;   // Y coordinate for LESS THIS APPLICATION row
        $rowBalanceY = 211.5;     // Y coordinate for BALANCE row

        // Format strings beforehand for cleaner code
        $vlEarnedText = number_format($vlEarned, 1);
        $slEarnedText = number_format($slEarned, 1);
        
        $vlDeductionText = $vlDeduction > 0 ? number_format($vlDeduction, 1) : '0';
        $slDeductionText = $slDeduction > 0 ? number_format($slDeduction, 1) : '0';
        
        $vlBalanceText = number_format($vlBalance, 1);
        $slBalanceText = number_format($slBalance, 1);

        // Row 1: TOTAL EARNED (Past Balance: 50)
        $pdf->SetXY($vlColumnX, $rowEarnedY); 
        $pdf->Cell($columnWidth, 0, $vlEarnedText, 0, 0, 'C'); // 'C' triggers center alignment
        
        $pdf->SetXY($slColumnX, $rowEarnedY); 
        $pdf->Cell($columnWidth, 0, $slEarnedText, 0, 0, 'C');

        // Row 2: LESS THIS APPLICATION (Outputs 0 if there is no deduction)
        $pdf->SetXY($vlColumnX, $rowDeductionY); 
        $pdf->Cell($columnWidth, 0, $vlDeductionText, 0, 0, 'C');

        $pdf->SetXY($slColumnX, $rowDeductionY); 
        $pdf->Cell($columnWidth, 0, $slDeductionText, 0, 0, 'C');

        // Row 3: BALANCE (Current DB Balance: 47)
        $pdf->SetXY($vlColumnX, $rowBalanceY); 
        $pdf->Cell($columnWidth, 0, $vlBalanceText, 0, 0, 'C');

        $pdf->SetXY($slColumnX, $rowBalanceY); 
        $pdf->Cell($columnWidth, 0, $slBalanceText, 0, 0, 'C');

        // PAGE 2 BACK PAGE DO nothing!
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