<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application for Leave - CSC Form 6</title>
    <style>
        @page { margin: 0.4in 0.5in; }
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.2; color: #000; }
        
        /* Typography */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }
        
        /* Layout */
        .header-meta { font-size: 9px; position: absolute; top: 0; left: 0; font-weight: bold; }
        .agency-name { font-size: 11px; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        .main-table { border: 2px solid #000; }
        .main-table > tbody > tr > td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        
        /* Specific Cells */
        .section-header { text-align: center; font-weight: bold; font-size: 12px; border-top: 2px solid #000 !important; border-bottom: 2px solid #000 !important; background-color: #f0f0f0; padding: 5px !important;}
        .label-num { font-size: 10px; }
        
        /* Form Elements */
        .checkbox-container { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; display: inline-block; width: 16px; border: 1px solid #000; text-align: center; line-height: 12px; height: 14px;}
        .line-input { border-bottom: 1px solid #000; display: inline-block; min-width: 100px; text-align: center; }
        
        /* Inner Tables */
        .inner-table { width: 100%; border: none; }
        .inner-table td { border: none !important; padding: 2px; vertical-align: top;}
        .credits-table { width: 90%; margin: 5px auto; border-collapse: collapse; text-align: center; }
        .credits-table th, .credits-table td { border: 1px solid #000; padding: 4px; }
    </style>
</head>
<body>

    <div class="header-meta">
        <span class="italic">Civil Service Form No. 6</span><br>
        <span class="italic">Revised 2020</span>
    </div>

    <table style="border: none; margin-bottom: 10px; margin-top: 20px;">
        <tr>
            <td width="25%" style="border: none; text-align: right; vertical-align: middle;">
                @if(file_exists(public_path('bacolod-logo.png')))
                    <img src="{{ public_path('bacolod-logo.png') }}" width="70">
                @endif
            </td>
            <td width="50%" style="border: none; text-align: center; vertical-align: middle;">
                Republic of the Philippines<br>
                Management Information Technology and Computer Services<br>
                City of Bacolod<br><br>
                <div style="font-size: 18px; font-weight: bold; letter-spacing: 1px;">APPLICATION FOR LEAVE</div>
            </td>
            <td width="25%" style="border: none; text-align: left; vertical-align: middle;">
                @if(file_exists(public_path('mitcs-logo.png')))
                    <img src="{{ public_path('mitcs-logo.png') }}" width="70">
                @endif
            </td>
        </tr>
    </table>

    <table class="main-table">
        
        <tr>
            <td width="30%">
                <div class="label-num">1. OFFICE/DEPARTMENT:</div>
                <div class="text-center font-bold" style="margin-top: 15px;">{{ $leaveRequest->employee->department->department_name ?? 'MITCS' }}</div>
            </td>
            <td width="70%" colspan="2" style="padding: 0;">
                <table class="inner-table" style="height: 100%;">
                    <tr>
                        <td width="15%" style="padding-left: 5px;"><div class="label-num">2. NAME:</div></td>
                        <td width="28%" class="text-center">
                            <div class="font-bold">{{ $leaveRequest->employee->last_name }}</div>
                            <div style="font-size: 9px;">(Last)</div>
                        </td>
                        <td width="28%" class="text-center">
                            <div class="font-bold">{{ $leaveRequest->employee->first_name }}</div>
                            <div style="font-size: 9px;">(First)</div>
                        </td>
                        <td width="29%" class="text-center">
                            <div class="font-bold">{{ $leaveRequest->employee->middle_initial ?? '' }}</div>
                            <div style="font-size: 9px;">(Middle)</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td width="30%">
                <span class="label-num">3. DATE OF FILING:</span> 
                <span class="line-input font-bold" style="min-width: 80px;">{{ \Carbon\Carbon::parse($leaveRequest->date_of_filing)->format('M d, Y') }}</span>
            </td>
            <td width="40%">
                <span class="label-num">4. POSITION:</span> 
                <span class="line-input font-bold" style="min-width: 150px;">{{ $leaveRequest->employee->position }}</span>
            </td>
            <td width="30%">
                <span class="label-num">5. SALARY:</span> 
                <span class="line-input" style="min-width: 120px;"></span>
            </td>
        </tr>

        <tr>
            <td colspan="3" class="section-header">6. DETAILS OF APPLICATION</td>
        </tr>

        <tr>
            <td colspan="2" width="55%" style="border-right: 1px solid #000;">
                <div class="label-num" style="margin-bottom: 5px;">6.A TYPE OF LEAVE TO BE AVAILED OF:</div>
                <table class="inner-table">
                    <tr>
                        <td width="20"><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Vacation Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Vacation Leave <span style="font-size: 8px;">(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Mandatory/Forced Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Mandatory/Forced Leave <span style="font-size: 8px;">(Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Sick Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Sick Leave <span style="font-size: 8px;">(Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Maternity Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Maternity Leave <span style="font-size: 8px;">(R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Paternity Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Paternity Leave <span style="font-size: 8px;">(R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Special Privilege Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Special Privilege Leave <span style="font-size: 8px;">(Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Solo Parent Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Solo Parent Leave <span style="font-size: 8px;">(RA No. 8972 / CSC MC No. 8, s. 2004)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Study Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Study Leave <span style="font-size: 8px;">(Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == '10-Day VAWC Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>10-Day VAWC Leave <span style="font-size: 8px;">(RA No. 9262 / CSC MC No. 15, s. 2005)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Rehabilitation Privilege' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Rehabilitation Privilege <span style="font-size: 8px;">(Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Special Leave Benefits for Women' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Special Leave Benefits for Women <span style="font-size: 8px;">(RA No. 9710 / CSC MC No. 25, s. 2010)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Special Emergency Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Special Emergency (Calamity) Leave <span style="font-size: 8px;">(CSC MC No. 2, s. 2012, as amended)</span></td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->leave_type == 'Adoption Leave' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Adoption Leave <span style="font-size: 8px;">(R.A. No. 8552)</span></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-top: 15px;">
                            <div style="font-style: italic; margin-bottom: 5px;">Others:</div>
                            <span class="line-input font-bold" style="width: 90%; text-align: left; padding-left: 10px;">{{ $leaveRequest->leave_type == 'Others' ? $leaveRequest->leave_type_others : '' }}</span>
                        </td>
                    </tr>
                </table>
            </td>

            <td width="45%">
                <div class="label-num" style="margin-bottom: 5px;">6.B DETAILS OF LEAVE:</div>
                <table class="inner-table">
                    <tr><td colspan="2" class="italic">In case of Vacation/Special Privilege Leave:</td></tr>
                    <tr>
                        <td width="20" style="padding-left: 15px;"><span class="checkbox-container">{!! $leaveRequest->leave_detail_category == 'Within the Philippines' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Within the Philippines <span class="line-input" style="width: 120px;">{{ $leaveRequest->leave_detail_category == 'Within the Philippines' ? $leaveRequest->leave_detail_specifics : '' }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">{!! $leaveRequest->leave_detail_category == 'Abroad' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Abroad (Specify) <span class="line-input" style="width: 140px;">{{ $leaveRequest->leave_detail_category == 'Abroad' ? $leaveRequest->leave_detail_specifics : '' }}</span></td>
                    </tr>

                    <tr><td colspan="2" class="italic" style="padding-top: 10px;">In case of Sick Leave:</td></tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">{!! $leaveRequest->leave_detail_category == 'In Hospital' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>In Hospital (Specify Illness) <span class="line-input" style="width: 100px;">{{ $leaveRequest->leave_detail_category == 'In Hospital' ? $leaveRequest->leave_detail_specifics : '' }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">{!! $leaveRequest->leave_detail_category == 'Out Patient' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Out Patient (Specify Illness) <span class="line-input" style="width: 100px;">{{ $leaveRequest->leave_detail_category == 'Out Patient' ? $leaveRequest->leave_detail_specifics : '' }}</span></td>
                    </tr>
                    
                    <tr><td colspan="2" class="italic" style="padding-top: 10px;">In case of Special Leave Benefits for Women:</td></tr>
                    <tr>
                        <td></td>
                        <td style="padding-left: 5px;">(Specify Illness) <span class="line-input" style="width: 150px;"></span></td>
                    </tr>

                    <tr><td colspan="2" class="italic" style="padding-top: 10px;">In case of Study Leave:</td></tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">&nbsp;</span></td>
                        <td>Completion of Master's Degree</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">&nbsp;</span></td>
                        <td>BAR/Board Examination Review</td>
                    </tr>

                    <tr><td colspan="2" class="italic" style="padding-top: 10px;">Other purpose:</td></tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">&nbsp;</span></td>
                        <td>Monetization of Leave Credits</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 15px;"><span class="checkbox-container">&nbsp;</span></td>
                        <td>Terminal Leave</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="border-right: 1px solid #000;">
                <div class="label-num">6.C NUMBER OF WORKING DAYS APPLIED FOR:</div>
                <div class="text-center font-bold" style="border-bottom: 1px solid #000; width: 90%; margin: 5px auto;">
                    {{ number_format($leaveRequest->working_days_applied, 1) }} days
                </div>
                
                <div class="label-num" style="margin-top: 10px; padding-left: 15px;">INCLUSIVE DATES:</div>
                <div class="text-center font-bold" style="border-bottom: 1px solid #000; width: 90%; margin: 5px auto;">
                    {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}
                </div>
            </td>
            <td>
                <div class="label-num">6.D COMMUTATION:</div>
                <table class="inner-table" style="margin-top: 5px;">
                    <tr>
                        <td width="30" class="text-center"><span class="checkbox-container">{!! !$leaveRequest->commutation_requested ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Not Requested</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="checkbox-container">{!! $leaveRequest->commutation_requested ? '☑' : '&nbsp;' !!}</span></td>
                        <td>Requested</td>
                    </tr>
                </table>
                <div class="text-center" style="margin-top: 25px;">
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto; height: 15px;"></div>
                    <div style="font-size: 9px;">(Signature of Applicant)</div>
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="3" class="section-header">7. DETAILS OF ACTION ON APPLICATION</td>
        </tr>

        <tr>
            <td colspan="2" style="border-right: 1px solid #000;">
                <div class="label-num">7.A CERTIFICATION OF LEAVE CREDITS:</div>
                <div class="text-center" style="margin-top: 5px;">
                    As of <span class="line-input" style="width: 150px;">{{ now()->format('M d, Y') }}</span>
                </div>
                
                <table class="credits-table">
                    <tr class="font-bold">
                        <td width="30%" style="border: none;"></td>
                        <td width="35%">VACATION LEAVE</td>
                        <td width="35%">SICK LEAVE</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-left">TOTAL EARNED</td>
                        <td>{{ $leaveRequest->employee->vacation_leave_balance }}</td>
                        <td>{{ $leaveRequest->employee->sick_leave_balance }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-left">LESS THIS APPLICATION</td>
                        <td>{{ $leaveRequest->leave_type == 'Vacation Leave' ? number_format($leaveRequest->working_days_applied, 1) : '' }}</td>
                        <td>{{ $leaveRequest->leave_type == 'Sick Leave' ? number_format($leaveRequest->working_days_applied, 1) : '' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold text-left">BALANCE</td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>

                <div class="text-center" style="margin-top: 20px;">
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto; height: 15px;"></div>
                    <div style="font-size: 9px;">(Authorized Officer)</div>
                </div>
            </td>
            <td>
                <div class="label-num">7.B RECOMMENDATION:</div>
                <table class="inner-table" style="margin-top: 5px;">
                    <tr>
                        <td width="30"><span class="checkbox-container">{!! $leaveRequest->status == 'approved' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>For approval</td>
                    </tr>
                    <tr>
                        <td><span class="checkbox-container">{!! $leaveRequest->status == 'disapproved' ? '☑' : '&nbsp;' !!}</span></td>
                        <td>For disapproval due to: <br>
                            <span class="line-input font-bold" style="width: 90%; margin-top:5px; text-align: left;">{{ $leaveRequest->disapproval_reason }}</span>
                            <span class="line-input" style="width: 90%; margin-top:10px;"></span>
                            <span class="line-input" style="width: 90%; margin-top:10px;"></span>
                        </td>
                    </tr>
                </table>

                <div class="text-center" style="margin-top: 20px;">
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto; height: 15px;"></div>
                    <div style="font-size: 9px;">(Authorized Officer)</div>
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2" style="border-right: 1px solid #000; border-bottom: none;">
                <div class="label-num mb-2">7.C APPROVED FOR:</div>
                <table class="inner-table" style="margin-left: 15px; margin-top: 10px;">
                    <tr>
                        <td width="40"><span class="line-input font-bold text-center" style="min-width: 40px;">{{ $leaveRequest->days_with_pay ?: '' }}</span></td>
                        <td>days with pay</td>
                    </tr>
                    <tr>
                        <td><span class="line-input font-bold text-center" style="min-width: 40px;">{{ $leaveRequest->days_without_pay ?: '' }}</span></td>
                        <td>days without pay</td>
                    </tr>
                    <tr>
                        <td><span class="line-input font-bold text-center" style="min-width: 40px;"></span></td>
                        <td>others (Specify)</td>
                    </tr>
                </table>
            </td>
            <td style="border-bottom: none;">
                <div class="label-num mb-2">7.D DISAPPROVED DUE TO:</div>
                <div style="margin-top: 10px; text-align: center;">
                    <span class="line-input font-bold" style="width: 90%; text-align: left;">{{ $leaveRequest->disapproval_reason }}</span>
                    <span class="line-input" style="width: 90%; margin-top: 10px;"></span>
                    <span class="line-input" style="width: 90%; margin-top: 10px;"></span>
                </div>
            </td>
        </tr>
        
        <tr>
            <td colspan="3" style="padding: 30px 20px 10px 20px; border-top: none;">
                <div style="width: 50%; margin: 0 auto; text-align: center;">
                    <div style="border-bottom: 1px solid #000; height: 30px;"></div>
                    <div class="font-bold" style="font-size: 11px; margin-top: 3px;">(Authorized Official)</div>
                </div>
            </td>
        </tr>

    </table>

</body>
</html>