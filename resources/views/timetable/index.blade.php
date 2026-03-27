@extends('layouts.app')

@section('title', ($settings['masjid_name'] ?? 'Masjid') . ' - Prayer Timetable')

@section('content')

<!-- Media Display Overlay (Fullscreen) -->
<div id="media-overlay" class="media-overlay" style="display: none;">
    <div class="media-container">
        <div id="media-content" class="media-content">
            <!-- Media content will be loaded here -->
        </div>
        <div id="media-countdown" class="media-countdown" style="display: none;">
            <div class="countdown-timer">
                <div class="countdown-label">Next Prayer</div>
                <div id="countdown-prayer-name" class="countdown-prayer"></div>
                <div id="countdown-time" class="countdown-time"></div>
            </div>
        </div>
    </div>
</div>

<!-- Countdown Popup (Transparent Overlay) -->
<div id="countdown-popup" class="countdown-popup" style="display: none;">
    <div class="countdown-popup-content">
        <div class="countdown-popup-header">
            <div id="countdown-popup-title" class="countdown-popup-title"></div>
        </div>
        <div class="countdown-popup-body">
            <div id="countdown-popup-timer" class="countdown-popup-timer">30</div>
            <div class="countdown-popup-label">seconds</div>
        </div>
        <div class="countdown-popup-footer">
            <div id="countdown-popup-prayer" class="countdown-popup-prayer"></div>
        </div>
    </div>
</div>

<!-- Digital Information Board Layout -->
@php
    $timetableBgBox = $boxSettings['timetable_background_box'] ?? null;
    $timetableBgStyling = $timetableBgBox['styling_settings'] ?? [];
    $bgColor = $timetableBgStyling['background_color'] ?? '#fdf7e6';
    $backgroundStyle = "background-color: {$bgColor};";
@endphp
<div id="timetable-background-box" data-box-root="timetable_background_box" class="container-fluid digital-board" style="{{ $backgroundStyle }}; padding: 0; margin: 0;">
    <!-- Unified Container for Consistent Width -->
    <div class="unified-container" style="padding: 0; margin: 0;">
        <!-- Top Header Row -->
        @if($useBoxesStyling && isset($boxSettings['header_box']))
            @php
                $headerBox = $boxSettings['header_box'] ?? null;
                $headerStyling = $headerBox['styling_settings'] ?? [];
                // Convert numeric font sizes to rem units
                $timeFontSize = $headerStyling['time_font_size'] ?? '5rem';
                $dateFontSize = $headerStyling['date_font_size'] ?? '4rem';
                // Add 'rem' if only number is provided
                if (is_numeric($timeFontSize)) {
                    $timeFontSize = $timeFontSize . 'rem';
                }
                if (is_numeric($dateFontSize)) {
                    $dateFontSize = $dateFontSize . 'rem';
                }
            @endphp
            <div id="header-box" data-box-root="header_box" class="board-header" style="
                {{ ($useBoxesStyling && !($boxSettings['header_box']['is_active'] ?? true)) ? 'display:none;' : '' }}
                background-color: {{ $headerStyling['background_color'] ?? '#1e4d2b' }};
                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                border: {{ $headerStyling['border_width'] ?? '2px' }} solid {{ $headerStyling['border_color'] ?? '#0066cc' }};
                border-radius: {{ $headerStyling['border_radius'] ?? '0px' }};
                padding: 10px 20px;
                margin: 0;
                position: relative;
            ">
                <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenBtn" style="position: absolute; top: 10px; right: 20px; font-size: 1.5rem; padding: 0.25rem 0.5rem;">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
                <div class="row align-items-center m-0">
                    <!-- Current Time -->
                    <div class="col-md-4 p-0">
                        <div class="current-time-display">
                            <div class="time-large" id="current-time" style="
                                font-size: {{ $timeFontSize }};
                                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                margin: 0;
                            ">{{ $now->format('h:i:s A') }}</div>
                        </div>
                    </div>
                    
                    <!-- Gregorian Date -->
                    <div class="col-md-4 text-center p-0">
                        <div class="date-display">
                            <div class="gregorian-date" id="gregorian-date" style="
                                font-size: {{ $dateFontSize }};
                                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                margin: 0;
                            ">{{ $now->format('D j M Y') }}</div>
                        </div>
                    </div>
                    
                    <!-- Islamic Date -->
                    <div class="col-md-4 p-0">
                        <div class="islamic-date-display text-center">
                            <div class="islamic-date" id="islamic-date" style="
                                font-size: {{ $dateFontSize }};
                                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                margin: 0;
                            ">{{ $islamicDate['day'] ?? '' }} {{ $islamicDate['month'] ?? '' }} {{ $islamicDate['year'] ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$useBoxesStyling)
            <!-- Original Clean Header Design -->
            @if(!isset($activeBoxTypes) || in_array('header_box', $activeBoxTypes))
            <div id="header-box" data-box-root="header_box" class="board-header">
                <div class="row align-items-center">
                    <!-- Current Time -->
                    <div class="col-md-4">
                        <div class="current-time-display">
                            <div class="time-large" id="current-time">{{ $now->format('h:i A') }}</div>
                        </div>
                    </div>
                    
                    <!-- Gregorian Date -->
                    <div class="col-md-4 text-center">
                        <div class="date-display">
                            <div class="gregorian-date" id="gregorian-date">{{ $now->format('D j M Y') }}</div>
                        </div>
                    </div>
                    
                    <!-- Islamic Date -->
                    <div class="col-md-3">
                        <div class="islamic-date-display text-center">
                            <div class="islamic-date" id="islamic-date">{{ $islamicDate['day'] ?? '' }} {{ $islamicDate['month'] ?? '' }} {{ $islamicDate['year'] ?? '' }}</div>
                        </div>
                    </div>
                    
                    <!-- Fullscreen Button -->
                    <div class="col-md-1" style="display: flex; justify-content: flex-end;">
                        <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenBtn">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        @endif

        <!-- Main Content Area -->
        <div class="board-main-content" style="padding: 0; margin: 0;">
            <div class="row h-100 m-0" style="display: flex; flex-direction: row;">
            <!-- Left Column - Prayer Times (2/3 width) -->
            @php $__showPrayer = ($useBoxesStyling ? isset($boxSettings['prayer_times_box']) : (!isset($activeBoxTypes) || in_array('prayer_times_box', $activeBoxTypes))); @endphp
            @if($__showPrayer)
            <div id="prayer-times-box" data-box-root="prayer_times_box" class="col-md-8 p-0" style="{{ ($useBoxesStyling && !($boxSettings['prayer_times_box']['is_active'] ?? true)) ? 'display:none;' : '' }}; display: flex; flex-direction: column;">
                @php
                    $prayerBox = $useBoxesStyling && isset($boxSettings['prayer_times_box']) ? $boxSettings['prayer_times_box'] : null;
                    $prayerStyling = $prayerBox['styling_settings'] ?? [];
                    $prayerLayout = $prayerBox['layout_settings'] ?? [];
                    // Safe style variables to avoid undefined array key errors on servers with older saved settings
                    $prayer_names_font_size = $prayerStyling['prayer_names_font_size'] ?? null;
                    $beginning_font_size = $prayerStyling['beginning_font_size'] ?? null;
                    $jamaat_font_size = $prayerStyling['jamaat_font_size'] ?? null;
                    $header_font_size = $prayerStyling['header_font_size'] ?? null;
                    $next_prayer_font_size = $prayerStyling['next_prayer_font_size'] ?? null;
                    $next_prayer_countdown_font_size = $prayerStyling['next_prayer_countdown_font_size'] ?? null;
                    $next_prayer_name_font_size = $prayerStyling['next_prayer_name_font_size'] ?? null;
                @endphp
                @if($useBoxesStyling && isset($boxSettings['prayer_times_box']))
                    <div id="prayer-times-section" class="prayer-times-section" style="
                        background-color: {{ $prayerStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $prayerStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $prayerStyling['font_family'] ?? 'Arial, sans-serif' }};
                        font-size: {{ $prayerStyling['font_size'] ?? '3.5rem' }}; /* Ensure rem values are applied directly */
                        border: {{ $prayerStyling['border_width'] ?? '1px' }} solid {{ $prayerStyling['border_color'] ?? '#0066cc' }};
                        padding: 10px 15px;
                        margin: 0;
                        @if($settings['logo_path'] ?? false)
                        --logo-bg-image: url('{{ app()->environment('production') ? url('public/storage/' . $settings['logo_path']) : asset('storage/' . $settings['logo_path']) }}');
                        @endif
                    ">
                        <div class="prayer-header" style="
                            background-color: {{ $prayerStyling['header_background_color'] ?? 'transparent' }};
                            color: {{ $prayerStyling['header_text_color'] ?? '#000000' }};
                            font-size: {{ $header_font_size ? (strpos($header_font_size, 'rem') !== false ? $header_font_size : $header_font_size . 'rem') : '1rem' }};
                            margin: 0 0 10px 0;
                            padding: 8px;
                            text-align: center;
                            font-weight: bold;
                            display: grid;
                            grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }};
                            gap: 10px;
                        ">
                            <div class="prayer-col-header" style="text-align: left;"></div>
                            <div class="prayer-col-header" style="text-align: center; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ $prayerContent['beginning_title'] ?? 'Beginning' }}</div>
                            <div class="prayer-col-header" style="text-align: center;">{{ $prayerContent['jamaat_time_title'] ?? 'Jamaat Time' }}</div>
                        </div>
                @elseif(!$useBoxesStyling)
                    <div id="prayer-times-section" class="prayer-times-section" @if($settings['logo_path'] ?? false) style="--logo-bg-image: url('{{ app()->environment('production') ? url('public/storage/' . $settings['logo_path']) : asset('storage/' . $settings['logo_path']) }}')" @endif>
                        <div class="prayer-header">
                            <div class="prayer-col-header"></div>
                            <div class="prayer-col-header">Beginning</div>
                            <div class="prayer-col-header">Jamaat Time</div>
                        </div>
                @endif
                    
                    @if($prayerTimes)
                        @if($useBoxesStyling && isset($boxSettings['prayer_times_box']))
                            @php
                                $nextPrayerPosition = $prayerLayout['next_prayer_position'] ?? 'below_table';
                            @endphp
                            @if($nextPrayerPosition === 'above_table')
                                <div class="next-prayer-info" style="
                                    margin-bottom: 15px;
                                    text-align: center;
                                ">
                                    <div class="next-prayer-text" style="
                                        margin-bottom: 8px; 
                                        font-weight: bold;
                                        font-size: {{ $next_prayer_font_size ? (strpos($next_prayer_font_size, 'rem') !== false ? $next_prayer_font_size : $next_prayer_font_size . 'rem') : '1.4rem' }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $next_prayer_countdown_font_size ? (strpos($next_prayer_countdown_font_size, 'rem') !== false ? $next_prayer_countdown_font_size : $next_prayer_countdown_font_size . 'rem') : '1.4rem' }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $next_prayer_name_font_size ? (strpos($next_prayer_name_font_size, 'rem') !== false ? $next_prayer_name_font_size : $next_prayer_name_font_size . 'rem') : '0.9rem' }} !important; 
                                        margin-top: 5px; 
                                        opacity: 0.8;
                                        color: {{ $prayerStyling['next_prayer_name_color'] ?? '#666666' }} !important;
                                    ">Calculating...</div>
                                </div>
                            @endif
                        @endif
                        <div class="prayer-list">
                            <div class="prayer-row" data-prayer-name="fajr" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size ? (strpos($prayer_names_font_size, 'rem') !== false ? $prayer_names_font_size : $prayer_names_font_size . 'rem') : '3rem' }}; font-weight: bold;">Fajr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size ? (strpos($beginning_font_size, 'rem') !== false ? $beginning_font_size : $beginning_font_size . 'rem') : '3rem' }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size ? (strpos($jamaat_font_size, 'rem') !== false ? $jamaat_font_size : $jamaat_font_size . 'rem') : '3rem' }};">{{ $prayerTimes->fajr_jamaat ? \Carbon\Carbon::parse($prayerTimes->fajr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->fajr)->addMinutes((int)$settings['fajr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="zohar" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size ? (strpos($prayer_names_font_size, 'rem') !== false ? $prayer_names_font_size : $prayer_names_font_size . 'rem') : '3rem' }}; font-weight: bold;">Zohar</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size ? (strpos($beginning_font_size, 'rem') !== false ? $beginning_font_size : $beginning_font_size . 'rem') : '3rem' }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size ? (strpos($jamaat_font_size, 'rem') !== false ? $jamaat_font_size : $jamaat_font_size . 'rem') : '3rem' }};">{{ $prayerTimes->zohar_jamaat ? \Carbon\Carbon::parse($prayerTimes->zohar_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->zohar)->addMinutes((int)$settings['zohar_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="asr" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size ? (strpos($prayer_names_font_size, 'rem') !== false ? $prayer_names_font_size : $prayer_names_font_size . 'rem') : '3rem' }}; font-weight: bold;">Asr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size ? (strpos($beginning_font_size, 'rem') !== false ? $beginning_font_size : $beginning_font_size . 'rem') : '3rem' }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->asr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size ? (strpos($jamaat_font_size, 'rem') !== false ? $jamaat_font_size : $jamaat_font_size . 'rem') : '3rem' }};">{{ $prayerTimes->asr_jamaat ? \Carbon\Carbon::parse($prayerTimes->asr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->asr)->addMinutes((int)$settings['asr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="maghrib" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size ? (strpos($prayer_names_font_size, 'rem') !== false ? $prayer_names_font_size : $prayer_names_font_size . 'rem') : '3rem' }}; font-weight: bold;">Maghrib</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size ? (strpos($beginning_font_size, 'rem') !== false ? $beginning_font_size : $beginning_font_size . 'rem') : '3rem' }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->maghrib)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size ? (strpos($jamaat_font_size, 'rem') !== false ? $jamaat_font_size : $jamaat_font_size . 'rem') : '3rem' }};">{{ $prayerTimes->maghrib_jamaat ? \Carbon\Carbon::parse($prayerTimes->maghrib_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->maghrib)->addMinutes((int)$settings['maghrib_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="isha" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size ? (strpos($prayer_names_font_size, 'rem') !== false ? $prayer_names_font_size : $prayer_names_font_size . 'rem') : '3rem' }}; font-weight: bold;">Isha</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size ? (strpos($beginning_font_size, 'rem') !== false ? $beginning_font_size : $beginning_font_size . 'rem') : '3rem' }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->isha)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size ? (strpos($jamaat_font_size, 'rem') !== false ? $jamaat_font_size : $jamaat_font_size . 'rem') : '3rem' }};">{{ $prayerTimes->isha_jamaat ? \Carbon\Carbon::parse($prayerTimes->isha_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->isha)->addMinutes((int)$settings['isha_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                        </div>
                        
                        @if($useBoxesStyling && isset($boxSettings['prayer_times_box']))
                            @php
                                $nextPrayerPosition = $prayerLayout['next_prayer_position'] ?? 'below_table';
                            @endphp
                            @if($nextPrayerPosition === 'below_table')
                                <div class="next-prayer-info" style="
                                    margin-top: 15px;
                                    text-align: center;
                                ">
                                    <div class="next-prayer-text" style="
                                        margin-bottom: 8px; 
                                        font-weight: bold;
                                        font-size: {{ $next_prayer_font_size ? (strpos($next_prayer_font_size, 'rem') !== false ? $next_prayer_font_size : $next_prayer_font_size . 'rem') : '1.4rem' }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $next_prayer_countdown_font_size ? (strpos($next_prayer_countdown_font_size, 'rem') !== false ? $next_prayer_countdown_font_size : $next_prayer_countdown_font_size . 'rem') : '1.4rem' }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $next_prayer_name_font_size ? (strpos($next_prayer_name_font_size, 'rem') !== false ? $next_prayer_name_font_size : $next_prayer_name_font_size . 'rem') : '0.9rem' }} !important; 
                                        margin-top: 5px; 
                                        opacity: 0.8;
                                        color: {{ $prayerStyling['next_prayer_name_color'] ?? '#666666' }} !important;
                                    ">Calculating...</div>
                                </div>
                            @endif
                        @elseif($useBoxesStyling && isset($boxSettings['note_prayer_box']))
                            @php
                                $noteBox = $boxSettings['note_prayer_box'] ?? null;
                                $noteStyling = $noteBox['styling_settings'] ?? [];
                                $noteContent = $noteBox['content_settings'] ?? [];
                            @endphp
                            <div class="next-prayer-info" data-box-root="note_prayer_box" style="
                                background-color: {{ $noteStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                                color: {{ $noteStyling['text_color'] ?? '#000000' }};
                                font-family: {{ $noteStyling['font_family'] ?? 'Arial, sans-serif' }};
                                font-size: {{ $noteStyling['font_size'] ?? '16px' }};
                                border: {{ $noteStyling['border_width'] ?? '1px' }} solid {{ $noteStyling['border_color'] ?? '#0066cc' }};
                                border-radius: {{ $noteStyling['border_radius'] ?? '0px' }};
                                padding: {{ $noteStyling['padding'] ?? '10px' }};
                                margin-top: 15px;
                            ">
                                <div class="next-prayer-text" style="margin-bottom: 8px; font-weight: bold;">{{ $noteContent['text'] ?? 'Next prayer in:' }}</div>
                                <div id="next-prayer-countdown" class="next-prayer-countdown" style="font-size: 1.4rem; font-weight: bold;">--:--:--</div>
                                <div id="next-prayer-name" class="next-prayer-name" style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8;">Calculating...</div>
                            </div>
                        @else
                            <div class="next-prayer-info" style="margin-top: 15px; text-align: center;">
                                <div class="next-prayer-text" style="margin-bottom: 8px; font-weight: bold;">Next prayer in:</div>
                                <div id="next-prayer-countdown" class="next-prayer-countdown" style="font-size: 1.4rem; font-weight: bold;">--:--:--</div>
                                <div id="next-prayer-name" class="next-prayer-name" style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8;">Calculating...</div>
                            </div>
                        @endif
                    @else
                        <div class="no-prayer-times">
                            <p>No prayer times available for today</p>
                        </div>
                    @endif
                </div>
                </div>
            @endif



            <!-- Right Column - Announcements (1/3 width) -->
            @php $__showAnnouncements = ($useBoxesStyling ? isset($boxSettings['announcements_box']) : (!isset($activeBoxTypes) || in_array('announcements_box', $activeBoxTypes))); @endphp
            @if($__showAnnouncements)
            <div class="col-md-4 p-0" data-box-root="announcements_box" style="{{ ($useBoxesStyling && !($boxSettings['announcements_box']['is_active'] ?? true)) ? 'display:none;' : '' }}; display: flex; flex-direction: column; height: 100%;">
                @if($useBoxesStyling && isset($boxSettings['announcements_box']))
                    @php
                        $announcementsBox = $boxSettings['announcements_box'] ?? null;
                        $announcementsStyling = $announcementsBox['styling_settings'] ?? [];
                        $announcementsLayout = $announcementsBox['layout_settings'] ?? [];
                    @endphp
                    <div class="announcements-section" id="announcements-section" style="
                        background-color: {{ $announcementsStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $announcementsStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $announcementsStyling['font_family'] ?? 'Arial, sans-serif' }};
                        border: {{ $announcementsStyling['border_width'] ?? '1px' }} solid {{ $announcementsStyling['border_color'] ?? '#0066cc' }};
                        border-radius: 0px;
                        padding: {{ $announcementsStyling['padding'] ?? '15' }}px;
                        margin: 0;
                        display: flex;
                        flex-direction: column;
                        flex: 1;
                        min-height: 0;
                    ">
                        <div class="announcements-header" style="
                            color: {{ $announcementsStyling['title_color'] ?? '#000000' }};
                            font-size: {{ $announcementsStyling['title_font_size'] ? (is_numeric($announcementsStyling['title_font_size']) ? $announcementsStyling['title_font_size'] . 'px' : $announcementsStyling['title_font_size']) : '28px' }};
                            font-weight: bold;
                            text-align: center;
                            margin: 0 0 15px 0;
                            flex-shrink: 0;
                        ">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @elseif(!$useBoxesStyling)
                    <div class="announcements-section" id="announcements-section" style=\"padding: 10px 15px; margin: 0;\">
                        <div class="announcements-header" style=\"font-size: 3.5rem; margin: 0 0 10px 0;\">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @endif
                    <div class="announcements-content" id="announcements-content" style="
                        margin: 0;
                        flex: 1;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        min-height: 0;
                    " data-display-mode="rotation">
                        @if($announcements->count() > 0)
                            @foreach($announcements as $index => $announcement)
                                <div class="announcement-item rotating-announcement" 
                                     data-index="{{ $index }}" 
                                     data-duration="{{ ($announcement->display_duration ?? 10) * 1000 }}" 
                                     style="
                                    display: {{ $index === 0 ? 'block' : 'none' }};
                                    margin: 0;
                                    padding: 0;
                                    word-wrap: break-word;
                                    word-break: break-word;
                                    overflow: hidden;
                                    background-color: {{ $announcement->background_color ?? '#ffffff' }};
                                    height: 100%;
                                    width: 100%;
                                    flex-direction: column;
                                    flex: 1;
                                ">
                                    <div class="announcement-title" style="
                                        font-weight: bold;
                                        margin-bottom: 8px;
                                        color: {{ $announcement->text_color ?? '#000000' }};
                                        font-size: {{ $announcement->title_font_size ?? 36 }}px;
                                        line-height: 1.3;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                    ">{{ $announcement->title }}</div>
                                    @php
                                        // Calculate scroll speed for vertical scrolling (1-10)
                                        // Speed 1 = slowest (50s), Speed 10 = fastest (5s)
                                        $baseSpeed = 50; // base seconds
                                        $scrollSpeed = $announcement->scroll_speed ?? 3;
                                        $animationDuration = $baseSpeed / $scrollSpeed; // inversely proportional
                                        $animationDuration = max(5, $animationDuration); // minimum 5 seconds
                                    @endphp
                                    <div class="announcement-text-container" style="
                                        flex: 1;
                                        overflow: hidden;
                                        position: relative;
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: flex-start;
                                    ">
                                        <div class="announcement-text-scroll" style="
                                            font-size: {{ $announcement->font_size ?? 24 }}px;
                                            color: {{ $announcement->text_color ?? '#000000' }};
                                            word-wrap: break-word;
                                            line-height: 1.4;
                                            margin: 0;
                                            min-height: 100%;
                                            animation: scroll-vertical {{ $animationDuration }}s linear infinite;
                                            animation-play-state: running;
                                        " data-scroll-speed="{{ $scrollSpeed }}">{{ $announcement->content }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="announcement-placeholder" style="text-align: center; padding: 20px;">
                                <p style="margin: 0; font-size: 0.9rem;">No announcements currently.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            </div>
        </div>

        <!-- Bottom Additional Times -->
    @php $__showSpecialTimes = (!$useBoxesStyling) ? (!isset($activeBoxTypes) || in_array('special_times_box', $activeBoxTypes)) : isset($boxSettings['special_times_box']); @endphp
    @if($__showSpecialTimes)
        @if($useBoxesStyling && isset($boxSettings['special_times_box']))
            @php
                $specialBox = $boxSettings['special_times_box'] ?? null;
                $specialStyling = $specialBox['styling_settings'] ?? [];
                $specialLayout = $specialBox['layout_settings'] ?? [];
            @endphp
            <div class="board-bottom-times" data-box-root="special_times_box" style="
                {{ ($useBoxesStyling && !($boxSettings['special_times_box']['is_active'] ?? true)) ? 'display:none;' : '' }}
                background-color: {{ $specialStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                color: {{ $specialStyling['text_color'] ?? '#000000' }};
                font-family: {{ $specialStyling['font_family'] ?? 'Courier New, monospace' }};
                font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }};
                border: {{ $specialStyling['border_width'] ?? '2px' }} solid {{ $specialStyling['border_color'] ?? '#000000' }};
                border-radius: 0px;
                padding: 10px 15px;
                text-align: center;
                margin: 0;
            ">
                <div class="row m-0">
                    <div class="col p-0">
                        <div class="additional-times" style="
                            display: grid;
                            grid-template-columns: {{ implode(' ', $specialLayout['column_widths'] ?? ['14%', '14%', '14%', '14%', '14%', '15%', '15%']) }};
                            gap: 10px;
                            align-items: center;
                            font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; /* Ensure font size is applied */
                        ">
                            @php
                                $tableHeaders = [
                                    $specialTimesContent['sehri_ends_title'] ?? 'Sehri Ends',
                                    $specialTimesContent['sun_rise_title'] ?? 'Sun Rise',
                                    $specialTimesContent['noon_title'] ?? 'Noon',
                                    $specialTimesContent['jumah_1_title'] ?? 'Jumu\'ah 1',
                                    $specialTimesContent['jumah_2_title'] ?? 'Jumu\'ah 2',
                                    $specialTimesContent['eid_prayer_1_title'] ?? 'Eid Prayer 1',
                                    $specialTimesContent['eid_prayer_2_title'] ?? 'Eid Prayer 2'
                                ];
                                $timeFormat = 'h:i';
                                $times = [
                                    $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format($timeFormat) : '--:--',
                                    $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->sun_rise)->format($timeFormat) : '--:--',
                                    $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format($timeFormat) : '--:--',
                                    $prayerTimes && $prayerTimes->jumah_1 ? \Carbon\Carbon::parse($prayerTimes->jumah_1)->format($timeFormat) : '--:--',
                                    $prayerTimes && $prayerTimes->jumah_2 ? \Carbon\Carbon::parse($prayerTimes->jumah_2)->format($timeFormat) : '--:--',
                                    $prayerTimes && $prayerTimes->eid_prayer_1 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_1)->format($timeFormat) : '--:--',
                                    $prayerTimes && $prayerTimes->eid_prayer_2 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_2)->format($timeFormat) : '--:--'
                                ];
                            @endphp
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[0] }}</div>
                                <div class="time-value" data-special-time="sehri_ends" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[0] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[1] }}</div>
                                <div class="time-value" data-special-time="sun_rise" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[1] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[2] }}</div>
                                <div class="time-value" data-special-time="noon" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[2] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[3] }}</div>
                                <div class="time-value" data-special-time="jumah_1" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[3] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[4] }}</div>
                                <div class="time-value" data-special-time="jumah_2" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[4] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[5] }}</div>
                                <div class="time-value" data-special-time="eid_prayer_1" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[5] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['header_font_size'] ? $specialStyling['header_font_size'] . 'rem' : '1rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[6] }}</div>
                                <div class="time-value" data-special-time="eid_prayer_2" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ? $specialStyling['font_size'] . 'rem' : '2.5rem' }}; font-weight: bold;">{{ $times[6] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$useBoxesStyling)
            <div class="board-bottom-times" data-box-root="special_times_box">
                <div class="row">
                    <div class="col">
                        <div class="additional-times">
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['sehri_ends_title'] ?? 'Sehri Ends' }}</div>
                                <div class="time-value" data-special-time="sehri_ends">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['sun_rise_title'] ?? 'Sun Rise' }}</div>
                                <div class="time-value" data-special-time="sun_rise">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->sun_rise)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['noon_title'] ?? 'Noon' }}</div>
                                <div class="time-value" data-special-time="noon">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['jumah_1_title'] ?? 'Jumu\'ah 1' }}</div>
                                <div class="time-value" data-special-time="jumah_1">{{ $prayerTimes && $prayerTimes->jumah_1 ? \Carbon\Carbon::parse($prayerTimes->jumah_1)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['jumah_2_title'] ?? 'Jumu\'ah 2' }}</div>
                                <div class="time-value" data-special-time="jumah_2">{{ $prayerTimes && $prayerTimes->jumah_2 ? \Carbon\Carbon::parse($prayerTimes->jumah_2)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['eid_prayer_1_title'] ?? 'Eid Prayer 1' }}</div>
                                <div class="time-value" data-special-time="eid_prayer_1">{{ $prayerTimes && $prayerTimes->eid_prayer_1 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_1)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['eid_prayer_2_title'] ?? 'Eid Prayer 2' }}</div>
                                <div class="time-value" data-special-time="eid_prayer_2">{{ $prayerTimes && $prayerTimes->eid_prayer_2 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_2)->format('h:i') : '--:--' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

        <!-- Welcome Box (Only shows with boxes styling enabled) -->
        @if($useBoxesStyling && isset($boxSettings['welcome_box']))
            @php
                $welcomeBox = $boxSettings['welcome_box'] ?? null;
                $welcomeStyling = $welcomeBox['styling_settings'] ?? [];
                $welcomeContent = $welcomeBox['content_settings'] ?? [];
            @endphp
            <div class="welcome-box" data-box-root="welcome_box" style="
                {{ ($useBoxesStyling && !($boxSettings['welcome_box']['is_active'] ?? true)) ? 'display:none;' : '' }}
                background-color: {{ $welcomeStyling['background_color'] ?? '#1e4d2b' }};
                color: {{ $welcomeStyling['text_color'] ?? '#FFD700' }};
                font-family: {{ $welcomeStyling['font_family'] ?? 'Arial, sans-serif' }};
                font-size: {{ $welcomeStyling['font_size'] ?? '24px' }};
                font-weight: {{ $welcomeStyling['font_weight'] ?? 'bold' }};
                border: {{ $welcomeStyling['border_width'] ?? '2px' }} solid {{ $welcomeStyling['border_color'] ?? '#0066cc' }};
                border-radius: {{ $welcomeStyling['border_radius'] ?? '0px' }};
                padding: {{ $welcomeStyling['padding'] ?? '15px' }};
                text-align: {{ $welcomeBox['layout_settings']['text_alignment'] ?? 'left' }};
            ">
                @if($welcomeContent['show_user_name'] ?? true)
                    {{ str_replace('{username}', 'imran', $welcomeContent['welcome_text'] ?? 'Hello imran Welcome to timetable - Manage your prayer times, announcement') }}
                @else
                    {{ $welcomeContent['welcome_text'] ?? 'Hello imran Welcome to timetable - Manage your prayer times, announcement' }}
                @endif
            </div>
        @endif

        @php $__showSliding = ($useBoxesStyling ? isset($boxSettings['sliding_text_box']) : (!isset($activeBoxTypes) || in_array('sliding_text_box', $activeBoxTypes))); @endphp
        @if($__showSliding)
        <!-- Scrolling Text Area -->
        @if($useBoxesStyling && isset($boxSettings['sliding_text_box']))
            @php
                $slidingBox = $boxSettings['sliding_text_box'] ?? null;
                $slidingStyling = $slidingBox['styling_settings'] ?? [];
                $slidingLayout = $slidingBox['layout_settings'] ?? [];
            @endphp
            <div class="scrolling-text-area" data-box-root="sliding_text_box" style="
                {{ ($useBoxesStyling && !($boxSettings['sliding_text_box']['is_active'] ?? true)) ? 'display:none;' : '' }}
                background-color: {{ $slidingStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                color: {{ $slidingStyling['text_color'] ?? '#000000' }};
                font-family: {{ $slidingStyling['font_family'] ?? 'Courier New, monospace' }};
                font-size: {{ $slidingStyling['font_size'] ?? '3rem' }};
                border: {{ $slidingStyling['border_width'] ?? '2px' }} solid {{ $slidingStyling['border_color'] ?? '#000000' }};
                text-align: {{ $slidingLayout['text_alignment'] ?? 'left' }};
                margin: 0;
                padding: 10px 15px;
            ">
                <div class="scrolling-content">
                    <div class="scrolling-text">
                        <div class="scroll-wrapper">
                            @if($slidingTexts->count() > 0)
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? $slidingText->font_size . 'rem'
                                            : ($slidingStyling['font_size'] ?? '3rem');
                                        $slidingFontWeight = $slidingText->font_weight
                                            ?? ($slidingStyling['font_weight'] ?? '700');
                                        $slidingTextColor = $slidingText->text_color
                                            ?? ($slidingStyling['text_color'] ?? '#000000');
                                    @endphp
                                    <span class="scroll-item" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? $slidingText->font_size . 'rem'
                                            : ($slidingStyling['font_size'] ?? '3rem');
                                        $slidingFontWeight = $slidingText->font_weight
                                            ?? ($slidingStyling['font_weight'] ?? '700');
                                        $slidingTextColor = $slidingText->text_color
                                            ?? ($slidingStyling['text_color'] ?? '#000000');
                                    @endphp
                                    <span class="scroll-item" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                            @else
                                <span class="scroll-item">Welcome to the Masjid - No sliding text configured</span>
                                <span class="scroll-item">Welcome to the Masjid - No sliding text configured</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$useBoxesStyling)
            <div class="scrolling-text-area" data-box-root="sliding_text_box">
                <div class="scrolling-content">
                    <div class="scrolling-text">
                        <div class="scroll-wrapper">
                            @if($slidingTexts->count() > 0)
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? $slidingText->font_size . 'rem'
                                            : '3rem';
                                        $slidingFontWeight = $slidingText->font_weight ?? '700';
                                        $slidingTextColor = $slidingText->text_color ?? '#000000';
                                    @endphp
                                    <span class="scroll-item" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? $slidingText->font_size . 'rem'
                                            : '3rem';
                                        $slidingFontWeight = $slidingText->font_weight ?? '700';
                                        $slidingTextColor = $slidingText->text_color ?? '#000000';
                                    @endphp
                                    <span class="scroll-item" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                            @else
                                <span class="scroll-item">Welcome to the Masjid - No sliding text configured</span>
                                <span class="scroll-item">Welcome to the Masjid - No sliding text configured</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @endif
    </div>

</div>

<style>
    /* Header layout fixes: keep time/dates on one line and prevent fullscreen button from squeezing Hijri date */
    #header-box .current-time-display,
    #header-box .time-large,
    #header-box .gregorian-date,
    #header-box .islamic-date {
        white-space: nowrap;
    }

    #header-box .header-right {
        width: 100%;
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        column-gap: clamp(10px, 2vw, 20px);
    }

    #header-box .header-right .islamic-date-display {
        min-width: 0;
        text-align: center;
        justify-self: stretch;
    }

    #header-box .header-right #fullscreenBtn {
        position: static;
        justify-self: end;
        transform: none;
    }

    /* Force 2/3 + 1/3 layout regardless of external CSS or empty announcements */
    .board-main-content .row {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        width: 100% !important;
        height: 100% !important;
        gap: 0 !important;
        min-height: 0 !important;
    }

    .board-main-content .col-md-8 {
        flex: 0 0 66.666% !important;
        max-width: 66.666% !important;
        min-width: 0 !important;
        height: 100% !important;
    }

    .board-main-content .col-md-4 {
        flex: 0 0 33.334% !important;
        max-width: 33.334% !important;
        min-width: 0 !important;
        height: 100% !important;
    }

    .announcements-section {
        min-height: 100% !important;
    }

    /* Next Prayer Info Container - Prevent overflow and ensure proper spacing */
    .next-prayer-info {
        max-height: 350px !important;
        overflow: hidden !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        line-height: 1.3 !important;
    }

    /* Next Prayer Text - Prevent font size from pushing content */
    .next-prayer-text {
        display: block !important;
        white-space: normal !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
    }

    /* Countdown Timer - Handle large font sizes */
    .next-prayer-countdown {
        display: block !important;
        word-break: break-all !important;
        max-width: 100% !important;
        overflow: hidden !important;
        line-height: 1.2 !important;
        padding: 0 5px !important;
    }

    /* Prayer Name - Ensure it fits */
    .next-prayer-name {
        display: block !important;
        white-space: normal !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
        margin-top: 5px !important;
    }

    /* Announcements Section - Overflow Management */
    .announcements-section {
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
    }

    .announcements-content {
        flex: 1 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
    }

    .announcement-item {
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow: hidden !important;
        flex-shrink: 0 !important;
    }

    .announcement-title {
        overflow: hidden !important;
        white-space: normal !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
    }

    .announcement-text {
        overflow: hidden !important;
        white-space: normal !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 6 !important;
        -webkit-box-orient: vertical !important;
    }

    @keyframes scroll-vertical {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-100%);
        }
    }

    .announcement-text-scroll {
        transition: animation 0.3s ease;
    }

    .announcement-text-scroll.no-scroll {
        animation: none !important;
    }

    /* Display Mode: Show All */
    #announcements-content[data-display-mode="show-all"] {
        overflow-y: auto;
        flex-direction: column;
        display: flex !important;
        gap: 0;
        padding: 0;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-item {
        display: flex !important;
        flex: 1 1 0 !important;
        height: auto !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 12px !important;
        border: none !important;
        border-radius: 0 !important;
        flex-direction: column !important;
        position: relative !important;
        overflow: hidden !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-title {
        font-weight: bold !important;
        margin-bottom: 5px !important;
        line-height: 1.2 !important;
        flex-shrink: 0;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-text-container {
        overflow: hidden !important;
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-text-scroll {
        animation: scroll-vertical 20s linear infinite !important;
        min-height: auto !important;
        line-height: 1.4 !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-text-scroll.no-scroll {
        animation: none !important;
        animation-play-state: paused !important;
    }

    /* Display Mode: Rotation */
    #announcements-content[data-display-mode="rotation"] {
        position: relative;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-item {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-text-scroll {
        animation: scroll-vertical 20s linear infinite !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-text-scroll.no-scroll {
        animation: none !important;
        animation-play-state: paused !important;
    }
</style>

@endsection

@section('scripts')
@php
    // Today's prayer times (jamaat times)
    $prayerTimesJson = [
        'fajr' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr_jamaat ?: $prayerTimes->fajr)->format('H:i') : null,
        'zohar' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar_jamaat ?: $prayerTimes->zohar)->format('H:i') : null,
        'asr' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->asr_jamaat ?: $prayerTimes->asr)->format('H:i') : null,
        'maghrib' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->maghrib_jamaat ?: $prayerTimes->maghrib)->format('H:i') : null,
        'isha' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->isha_jamaat ?: $prayerTimes->isha)->format('H:i') : null,
    ];
    
    // Today's beginning times
    $todayBeginningTimesJson = [
        'fajr' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format('H:i') : null,
        'zohar' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format('H:i') : null,
        'asr' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->asr)->format('H:i') : null,
        'maghrib' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->maghrib)->format('H:i') : null,
        'isha' => $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->isha)->format('H:i') : null,
    ];
    
    // Tomorrow's prayer times
    $tomorrowPrayerTimesJson = [
        'fajr' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->fajr_jamaat ?: $tomorrowPrayerTimes->fajr)->format('H:i') : null,
        'zohar' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->zohar_jamaat ?: $tomorrowPrayerTimes->zohar)->format('H:i') : null,
        'asr' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->asr_jamaat ?: $tomorrowPrayerTimes->asr)->format('H:i') : null,
        'maghrib' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->maghrib_jamaat ?: $tomorrowPrayerTimes->maghrib)->format('H:i') : null,
        'isha' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->isha_jamaat ?: $tomorrowPrayerTimes->isha)->format('H:i') : null,
    ];
    
    // Tomorrow's beginning times
    $tomorrowBeginningTimesJson = [
        'fajr' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->fajr)->format('H:i') : null,
        'zohar' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->zohar)->format('H:i') : null,
        'asr' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->asr)->format('H:i') : null,
        'maghrib' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->maghrib)->format('H:i') : null,
        'isha' => $tomorrowPrayerTimes ? \Carbon\Carbon::parse($tomorrowPrayerTimes->isha)->format('H:i') : null,
    ];
    
    // Jamaat offsets
    $jamaatOffsetsJson = [
        'fajr' => (int)($settings['fajr_jamaat_offset'] ?? 10),
        'zohar' => (int)($settings['zohar_jamaat_offset'] ?? 15),
        'asr' => (int)($settings['asr_jamaat_offset'] ?? 20),
        'maghrib' => (int)($settings['maghrib_jamaat_offset'] ?? 0),
        'isha' => (int)($settings['isha_jamaat_offset'] ?? 10),
    ];
    
    // Adhan times
    $adhanTimesJson = [
        'fajr_adhan' => $prayerTimes && $prayerTimes->fajr_adhan ? \Carbon\Carbon::parse($prayerTimes->fajr_adhan)->format('H:i') : ($prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format('H:i') : null),
        'zohar_adhan' => $prayerTimes && $prayerTimes->zohar_adhan ? \Carbon\Carbon::parse($prayerTimes->zohar_adhan)->format('H:i') : ($prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format('H:i') : null),
        'asr_adhan' => $prayerTimes && $prayerTimes->asr_adhan ? \Carbon\Carbon::parse($prayerTimes->asr_adhan)->format('H:i') : ($prayerTimes ? \Carbon\Carbon::parse($prayerTimes->asr)->format('H:i') : null),
        'maghrib_adhan' => $prayerTimes && $prayerTimes->maghrib_adhan ? \Carbon\Carbon::parse($prayerTimes->maghrib_adhan)->format('H:i') : ($prayerTimes ? \Carbon\Carbon::parse($prayerTimes->maghrib)->format('H:i') : null),
        'isha_adhan' => $prayerTimes && $prayerTimes->isha_adhan ? \Carbon\Carbon::parse($prayerTimes->isha_adhan)->format('H:i') : ($prayerTimes ? \Carbon\Carbon::parse($prayerTimes->isha)->format('H:i') : null),
    ];
@endphp
<script>
    // Prayer times data from PHP
    const prayerTimesData = @json($prayerTimesJson);
    const todayBeginningTimes = @json($todayBeginningTimesJson);
    const tomorrowPrayerTimesData = @json($tomorrowPrayerTimesJson);
    const tomorrowBeginningTimes = @json($tomorrowBeginningTimesJson);
    const jamaatOffsets = @json($jamaatOffsetsJson);
    const adhanTimesData = @json($adhanTimesJson);
    
    // Track which prayers have been updated to tomorrow
    const updatedPrayers = new Set();

    // Track which special times have been updated to tomorrow
    const updatedSpecialTimes = new Set();

    // Store special times for today/tomorrow
    let todaySpecialTimesData = {};
    let tomorrowSpecialTimesData = {};
    let originalTodaySpecialTimesData = {};
    
    // Store original today's times
    const originalTodayTimes = {
        beginning: { ...todayBeginningTimes },
        jamaat: { ...prayerTimesData }
    };
    
    // Store prayer metadata with durations (in minutes)
    const prayerMetadata = {
        'fajr': { duration: 20, key: 'fajr' },
        'zohar': { duration: 30, key: 'zohar' },
        'asr': { duration: 25, key: 'asr' },
        'maghrib': { duration: 20, key: 'maghrib' },
        'isha': { duration: 25, key: 'isha' }
    };

    // Update next prayer countdown
    function updateNextPrayerCountdown() {
        const countdownElement = document.getElementById('next-prayer-countdown');
        const prayerNameElement = document.getElementById('next-prayer-name');
        
        if (!countdownElement || !prayerNameElement) return;
        
        const now = new Date();
        const currentTime = now.getHours() * 60 + now.getMinutes();
        const currentSeconds = now.getSeconds();
        
        let nextPrayer = null;
        let nextPrayerTime = null;
        let nextPrayerMinutes = null;
        
        // Convert prayer times to minutes since midnight
        const prayers = [
            { name: 'Fajr', key: 'fajr', time: prayerTimesData.fajr },
            { name: 'Zohar', key: 'zohar', time: prayerTimesData.zohar },
            { name: 'Asr', key: 'asr', time: prayerTimesData.asr },
            { name: 'Maghrib', key: 'maghrib', time: prayerTimesData.maghrib },
            { name: 'Isha', key: 'isha', time: prayerTimesData.isha }
        ];
        
        for (const prayer of prayers) {
            if (!prayer.time) continue;
            
            const [hours, minutes] = prayer.time.split(':').map(Number);
            const prayerMinutes = hours * 60 + minutes;
            
            if (prayerMinutes > currentTime) {
                nextPrayer = prayer.name;
                nextPrayerMinutes = prayerMinutes;
                break;
            }
        }
        
        // If no prayer found today, next prayer is tomorrow's Fajr
        if (!nextPrayer && prayerTimesData.fajr) {
            nextPrayer = 'Fajr';
            const [hours, minutes] = prayerTimesData.fajr.split(':').map(Number);
            nextPrayerMinutes = (24 * 60) + (hours * 60 + minutes);
        }
        
        if (nextPrayer && nextPrayerMinutes !== null) {
            const minutesUntilPrayer = nextPrayerMinutes - currentTime;
            const hoursUntil = Math.floor(minutesUntilPrayer / 60);
            const minutesUntil = Math.floor(minutesUntilPrayer % 60);
            const secondsUntil = 60 - now.getSeconds();
            
            countdownElement.textContent = `${hoursUntil.toString().padStart(2, '0')}:${minutesUntil.toString().padStart(2, '0')}:${secondsUntil.toString().padStart(2, '0')}`;
            prayerNameElement.textContent = nextPrayer;
        } else {
            countdownElement.textContent = '--:--:--';
            prayerNameElement.textContent = 'No prayer times available';
        }
    }
    
    // Function to format time for display (12-hour format without AM/PM)
    function formatTimeForDisplay(time24) {
        if (!time24) return '--:--';
        const [hours, minutes] = time24.split(':').map(Number);
        let displayHours = hours % 12;
        if (displayHours === 0) displayHours = 12;
        const displayMinutes = minutes.toString().padStart(2, '0');
        return `${displayHours}:${displayMinutes}`;
    }
    
    // Function to convert 24-hour time to minutes since midnight
    function timeToMinutes(time24) {
        if (!time24) return null;
        const [hours, minutes] = time24.split(':').map(Number);
        return hours * 60 + minutes;
    }
    
    // Function to update a prayer's displayed time to tomorrow's time
    function updatePrayerToTomorrow(prayerName) {
        if (updatedPrayers.has(prayerName)) {
            return; // Already updated
        }
        
        const prayerRow = document.querySelector(`[data-prayer-name="${prayerName}"]`);
        if (!prayerRow) return;
        
        const beginningElement = prayerRow.querySelector('[data-time-type="beginning"]');
        const jamaatElement = prayerRow.querySelector('[data-time-type="jamaat"]');
        
        if (!beginningElement || !jamaatElement) return;
        
        // Get tomorrow's times
        const tomorrowBeginning = tomorrowBeginningTimes[prayerName];
        const tomorrowJamaat = tomorrowPrayerTimesData[prayerName];
        
        if (!tomorrowBeginning || !tomorrowJamaat) return;
        
        // Update the displayed times
        beginningElement.textContent = formatTimeForDisplay(tomorrowBeginning);
        jamaatElement.textContent = formatTimeForDisplay(tomorrowJamaat);
        
        // Update the prayer times data for countdown calculations
        prayerTimesData[prayerName] = tomorrowJamaat;
        todayBeginningTimes[prayerName] = tomorrowBeginning;
        
        // Mark as updated
        updatedPrayers.add(prayerName);
    }

    function deriveSpecialTimes(dayData) {
        return {
            sehri_ends: dayData && dayData.fajr ? dayData.fajr : null,
            sun_rise: dayData && dayData.sun_rise ? dayData.sun_rise : null,
            noon: dayData && dayData.zohar ? dayData.zohar : null,
            jumah_1: dayData && dayData.jumah_1 ? dayData.jumah_1 : null,
            jumah_2: dayData && dayData.jumah_2 ? dayData.jumah_2 : null,
            eid_prayer_1: dayData && dayData.eid_prayer_1 ? dayData.eid_prayer_1 : null,
            eid_prayer_2: dayData && dayData.eid_prayer_2 ? dayData.eid_prayer_2 : null
        };
    }

    function updateSpecialTimeToTomorrow(specialKey) {
        if (updatedSpecialTimes.has(specialKey)) {
            return;
        }

        const tomorrowValue = tomorrowSpecialTimesData && Object.prototype.hasOwnProperty.call(tomorrowSpecialTimesData, specialKey)
            ? tomorrowSpecialTimesData[specialKey]
            : null;

        if (!tomorrowValue) {
            return;
        }

        const formatted = formatTimeForDisplay(tomorrowValue);
        document.querySelectorAll(`[data-special-time="${specialKey}"]`).forEach(element => {
            if (element.textContent !== formatted) {
                element.textContent = formatted;
            }
        });

        updatedSpecialTimes.add(specialKey);
    }
    
    // Function to check if jamaat times have passed and update accordingly
    function checkAndUpdatePrayerTimes() {
        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        const currentSeconds = now.getSeconds();
        const currentTotalSeconds = currentMinutes * 60 + currentSeconds;

        const prayers = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];
        prayers.forEach(prayerName => {
            if (updatedPrayers.has(prayerName)) {
                return;
            }

            const jamaatTime = originalTodayTimes && originalTodayTimes.jamaat ? originalTodayTimes.jamaat[prayerName] : null;
            if (!jamaatTime) {
                return;
            }

            const minutes = timeToMinutes(jamaatTime);
            if (minutes === null) {
                return;
            }

            const totalSeconds = minutes * 60;
            if (currentTotalSeconds > totalSeconds) {
                updatePrayerToTomorrow(prayerName);
            }
        });

        const specialKeys = ['sehri_ends', 'sun_rise', 'noon', 'jumah_1', 'jumah_2', 'eid_prayer_1', 'eid_prayer_2'];
        specialKeys.forEach(specialKey => {
            if (updatedSpecialTimes.has(specialKey)) {
                return;
            }

            const originalValue = originalTodaySpecialTimesData && Object.prototype.hasOwnProperty.call(originalTodaySpecialTimesData, specialKey)
                ? originalTodaySpecialTimesData[specialKey]
                : null;

            if (!originalValue) {
                return;
            }

            const minutes = timeToMinutes(originalValue);
            if (minutes === null) {
                return;
            }

            const totalSeconds = minutes * 60;
            if (currentTotalSeconds > totalSeconds) {
                updateSpecialTimeToTomorrow(specialKey);
            }
        });
    }
    
    // Update countdown every second
    setInterval(updateNextPrayerCountdown, 1000);
    updateNextPrayerCountdown(); // Initial call
    
    // Check and update prayer times every second
    setInterval(checkAndUpdatePrayerTimes, 1000);
    checkAndUpdatePrayerTimes(); // Initial call
    
    // ========== AUTOMATIC DATE AND TIME UPDATE ==========
    let lastKnownDate = new Date().toISOString().slice(0, 10);

    function updateGregorianDateDisplay(now) {
        const gregorianDateElement = document.getElementById('gregorian-date');
        if (!gregorianDateElement) {
            return;
        }

        const formattedDate = now.toLocaleDateString('en-GB', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).replace(',', '');

        if (gregorianDateElement.textContent !== formattedDate) {
            gregorianDateElement.textContent = formattedDate;
        }
    }

    function updateIslamicDateDisplay(islamicDate) {
        if (!islamicDate || !islamicDate.day || !islamicDate.month || !islamicDate.year) {
            return;
        }

        const islamicDateElement = document.getElementById('islamic-date');
        if (!islamicDateElement) {
            return;
        }

        const formattedDate = `${islamicDate.day} ${islamicDate.month} ${islamicDate.year}`;
        if (islamicDateElement.textContent !== formattedDate) {
            islamicDateElement.textContent = formattedDate;
        }
    }

    function updateTimeAndDate() {
        const now = new Date();
        const currentDateKey = now.toISOString().slice(0, 10);

        // Update current time display in real-time (every second)
        const timeElements = document.querySelectorAll('#current-time');
        if (timeElements.length > 0) {
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;
            hours = String(hours).padStart(2, '0');

            const timeFormat = `${hours}:${minutes}:${seconds} ${ampm}`;

            timeElements.forEach(element => {
                if (element.textContent !== timeFormat) {
                    element.textContent = timeFormat;
                }
            });
        }

        updateGregorianDateDisplay(now);

        // Date changed (midnight) - refresh data via APIs only (no page reload)
        if (currentDateKey !== lastKnownDate) {
            console.log('🕐 Midnight detected, syncing timetable and announcements...');
            lastKnownDate = currentDateKey;
            resetDailyRuntimeState();
            requestTimetableSync();
            requestAnnouncementsSync();
            requestMediaSync();
            requestScreenConfigSync();
        }
    }

    setInterval(updateTimeAndDate, 1000);
    updateTimeAndDate();
    
    // Fullscreen functionality
    function toggleFullscreen() {
        const element = document.documentElement;
        const button = document.getElementById('fullscreenBtn');
        
        if (!document.fullscreenElement && !document.mozFullScreenElement && 
            !document.webkitFullscreenElement && !document.msFullscreenElement) {
            // Enter fullscreen
            const fullscreenPromise = element.requestFullscreen ? 
                element.requestFullscreen() :
                element.msRequestFullscreen ? element.msRequestFullscreen() :
                element.mozRequestFullScreen ? element.mozRequestFullScreen() :
                element.webkitRequestFullscreen ? element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT) :
                Promise.reject('Fullscreen not supported');
            
            fullscreenPromise.then(() => {
                button.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
                document.body.classList.add('fullscreen-mode');
                document.body.classList.add('show-controls');
                
                // Hide controls after 3 seconds
                setTimeout(() => {
                    document.body.classList.remove('show-controls');
                }, 3000);
            }).catch((error) => {
                console.log('Fullscreen permission denied or not supported:', error);
                // Still show media even if fullscreen fails
            });
            
        } else {
            // Exit fullscreen
            const exitPromise = document.exitFullscreen ? 
                document.exitFullscreen() :
                document.msExitFullscreen ? document.msExitFullscreen() :
                document.mozCancelFullScreen ? document.mozCancelFullScreen() :
                document.webkitExitFullscreen ? document.webkitExitFullscreen() :
                Promise.reject('Exit fullscreen not supported');
            
            exitPromise.then(() => {
                button.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Enter Fullscreen';
                document.body.classList.remove('fullscreen-mode');
            }).catch((error) => {
                console.log('Exit fullscreen failed:', error);
            });
        }
    }
    
    // Listen for fullscreen changes
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
    document.addEventListener('mozfullscreenchange', updateFullscreenButton);
    document.addEventListener('MSFullscreenChange', updateFullscreenButton);
    
    function updateFullscreenButton() {
        const button = document.getElementById('fullscreenBtn');
        
        if (document.fullscreenElement || document.webkitFullscreenElement || 
            document.mozFullScreenElement || document.msFullscreenElement) {
            button.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            document.body.classList.add('fullscreen-mode');
        } else {
            button.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Enter Fullscreen';
            document.body.classList.remove('fullscreen-mode');
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F11') {
            e.preventDefault();
            toggleFullscreen();
        } else if (e.key === 'Escape') {
            // Exit fullscreen on Escape (browser default)
            if (document.fullscreenElement) {
                toggleFullscreen();
            }
        } else if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            if (document.fullscreenElement) {
                e.preventDefault(); // Prevent dev tools in fullscreen mode
            }
        }
    });
    
    // Prevent right-click in fullscreen mode
    document.addEventListener('contextmenu', e => {
        if (document.fullscreenElement) {
            e.preventDefault();
        }
    });
    
    // Show controls on mouse movement in fullscreen
    let mouseTimer;
    document.addEventListener('mousemove', function() {
        if (document.fullscreenElement) {
            document.body.classList.add('show-controls');
            
            clearTimeout(mouseTimer);
            mouseTimer = setTimeout(() => {
                document.body.classList.remove('show-controls');
            }, 3000);
        }
    });

    // Media/Live Update System
    const defaultSlowPollMs = Math.max(5000, (Number(@json($settings['auto_refresh_interval'] ?? 45)) || 45) * 1000);
    const displayPollingConfig = window.displayPollingConfig || {};
    const POLL_INTERVALS = {
        media: Math.max(3000, Number(displayPollingConfig.media) || 3000),
        timetable: Math.max(5000, Number(displayPollingConfig.timetable) || defaultSlowPollMs),
        announcements: Math.max(5000, Number(displayPollingConfig.announcements) || defaultSlowPollMs),
        screenConfig: Math.max(5000, Number(displayPollingConfig.screenConfig) || 5000)
    };
    let announcementTextMaxHeight = (function() {
        const existingContainer = document.querySelector('#announcements-content .announcement-text-container');
        if (!existingContainer) {
            return '400px';
        }

        if (existingContainer.style.maxHeight) {
            return existingContainer.style.maxHeight;
        }

        const computed = window.getComputedStyle(existingContainer).maxHeight;
        return computed && computed !== 'none' ? computed : '400px';
    })();

    let currentMedia = null;
    let currentMediaData = null;
    let mediaDisplayTimer = null;
    let countdownTimer = null;
    let currentScreenState = null;
    const lastKnownVersions = {
        announcements: null,
        media: null,
        timetable: null,
        config: null,
        state: null
    };

    let mediaPollingTimer = null;
    let timetablePollingTimer = null;
    let announcementsPollingTimer = null;
    let screenConfigPollingTimer = null;
    let isMediaPollInFlight = false;
    let isTimetablePollInFlight = false;
    let isAnnouncementsPollInFlight = false;
    let isScreenConfigPollInFlight = false;

    let announcementRotationInterval = null;
    let announcementResizeHandler = null;

    async function fetchJson(url) {
        const response = await fetch(url, {
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`Request failed for ${url}: ${response.status}`);
        }

        return response.json();
    }

    function toPositiveInteger(value, fallback = 0) {
        const parsed = Number(value);
        return Number.isFinite(parsed) && parsed > 0 ? Math.floor(parsed) : fallback;
    }

    function minutesToTime(minutesSinceMidnight) {
        const normalized = ((minutesSinceMidnight % 1440) + 1440) % 1440;
        const hours = Math.floor(normalized / 60);
        const minutes = normalized % 60;

        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
    }

    function computeJamaatTime(beginningTime, explicitJamaatTime, offsetMinutes) {
        if (explicitJamaatTime) {
            return explicitJamaatTime;
        }

        const beginningMinutes = timeToMinutes(beginningTime);
        if (beginningMinutes === null) {
            return null;
        }

        return minutesToTime(beginningMinutes + (Number(offsetMinutes) || 0));
    }

    function isPosterState(state) {
        return state === 'PRAYER_POSTER' || state === 'FULLTIME_POSTER';
    }

    function normalizeMediaPayload(media) {
        if (!media) {
            return null;
        }

        return {
            id: typeof media.id !== 'undefined' ? media.id : null,
            title: media.title || '',
            type: media.type || '',
            file_url: media.file_url || '',
            display_duration: toPositiveInteger(media.display_duration, 0),
            priority: typeof media.priority !== 'undefined' ? media.priority : null,
            schedule_id: typeof media.schedule_id !== 'undefined' ? media.schedule_id : null
        };
    }

    function normalizeAnnouncement(announcement) {
        return {
            id: announcement && typeof announcement.id !== 'undefined' ? announcement.id : null,
            title: announcement && announcement.title ? announcement.title : '',
            content: announcement && announcement.content ? announcement.content : '',
            display_duration: toPositiveInteger(announcement && announcement.display_duration ? announcement.display_duration : null, 10),
            font_size: toPositiveInteger(announcement && announcement.font_size ? announcement.font_size : null, 24),
            text_color: announcement && announcement.text_color ? announcement.text_color : '#000000',
            background_color: announcement && announcement.background_color ? announcement.background_color : '#ffffff',
            scroll_speed: toPositiveInteger(announcement && announcement.scroll_speed ? announcement.scroll_speed : null, 3)
        };
    }

    function syncKnownVersions(payload) {
        if (!payload || typeof payload !== 'object') {
            return;
        }

        if (payload.announcements_version) {
            lastKnownVersions.announcements = payload.announcements_version;
        }

        if (payload.media_version) {
            lastKnownVersions.media = payload.media_version;
        }

        if (payload.timetable_version) {
            lastKnownVersions.timetable = payload.timetable_version;
        }

        if (payload.config_version) {
            lastKnownVersions.config = payload.config_version;
        }

        if (payload.state_version) {
            lastKnownVersions.state = payload.state_version;
        }
    }

    function hasVersionChanged(section, nextVersion) {
        if (!nextVersion) {
            return true;
        }

        return lastKnownVersions[section] !== nextVersion;
    }

    function normalizeCssValue(value, fallbackUnit = '') {
        if (value === null || typeof value === 'undefined' || value === '') {
            return '';
        }

        if (typeof value === 'number') {
            return `${value}${fallbackUnit}`;
        }

        const trimmed = String(value).trim();
        if (!trimmed) {
            return '';
        }

        if (!fallbackUnit) {
            return trimmed;
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            return `${trimmed}${fallbackUnit}`;
        }

        return trimmed;
    }

    function setStyleValue(element, property, value, fallbackUnit = '') {
        if (!element) {
            return;
        }

        const cssValue = normalizeCssValue(value, fallbackUnit);
        if (!cssValue) {
            return;
        }

        element.style.setProperty(property, cssValue);
    }

    function toggleBoxVisibility(boxType, isVisible) {
        document.querySelectorAll(`[data-box-root="${boxType}"]`).forEach((element) => {
            if (isVisible) {
                element.style.display = '';
                return;
            }

            element.style.display = 'none';
        });
    }

    function applyThemeVariables(themeVariables) {
        if (!themeVariables || typeof themeVariables !== 'object') {
            return;
        }

        const root = document.documentElement;
        Object.entries(themeVariables).forEach(([key, value]) => {
            if (value === null || typeof value === 'undefined') {
                return;
            }
            root.style.setProperty(`--${String(key).replace(/_/g, '-')}`, String(value));
        });

        const displayFontFamily = themeVariables.display_font_family || '';
        const displayBackgroundColor = themeVariables.display_background_color || '';
        const displayTextColor = themeVariables.display_text_color || '';

        root.style.setProperty('--display-font-family', displayFontFamily || 'Arial, sans-serif');
        root.style.setProperty('--display-background-color', displayBackgroundColor || '#ffffff');
        root.style.setProperty('--display-text-color', displayTextColor || '#000000');
        root.style.setProperty('--prayer-time-font-size', normalizeCssValue(themeVariables.prayer_time_font_size, 'px') || '24px');
        root.style.setProperty('--announcement-scroll-speed', String(themeVariables.announcement_scroll_speed || '3'));

        if (displayFontFamily) {
            document.body.style.fontFamily = displayFontFamily;
        }
        if (displayBackgroundColor) {
            const board = document.getElementById('timetable-background-box');
            if (board) {
                board.style.backgroundColor = displayBackgroundColor;
            }
        }
        if (displayTextColor) {
            document.body.style.color = displayTextColor;
        }
    }

    function buildOrderMap(boxSettings, boxOrder = []) {
        const orderMap = {};
        let sequence = 0;

        if (Array.isArray(boxOrder)) {
            boxOrder.forEach((boxType) => {
                orderMap[boxType] = sequence++;
            });
        }

        Object.entries(boxSettings || {}).forEach(([boxType, boxData]) => {
            if (typeof orderMap[boxType] !== 'undefined') {
                return;
            }
            orderMap[boxType] = Number(boxData && boxData.sort_order);
            if (!Number.isFinite(orderMap[boxType])) {
                orderMap[boxType] = sequence++;
            }
        });

        if (typeof orderMap.header_box === 'undefined') {
            orderMap.header_box = -1;
        }

        return orderMap;
    }

    function applyBoxOrdering(boxSettings, boxOrder = []) {
        const orderMap = buildOrderMap(boxSettings, boxOrder);
        const parents = new Set();

        document.querySelectorAll('[data-box-root]').forEach((node) => {
            if (node.parentElement) {
                parents.add(node.parentElement);
            }
        });

        parents.forEach((parent) => {
            const children = Array.from(parent.children).filter((child) => child.hasAttribute('data-box-root'));
            if (children.length < 2) {
                return;
            }

            children
                .sort((a, b) => {
                    const aType = a.getAttribute('data-box-root');
                    const bType = b.getAttribute('data-box-root');
                    const aOrder = typeof orderMap[aType] !== 'undefined' ? Number(orderMap[aType]) : Number.MAX_SAFE_INTEGER;
                    const bOrder = typeof orderMap[bType] !== 'undefined' ? Number(orderMap[bType]) : Number.MAX_SAFE_INTEGER;
                    return aOrder - bOrder;
                })
                .forEach((child) => parent.appendChild(child));
        });
    }

    function applyBoxConfig(boxSettings) {
        if (!boxSettings || typeof boxSettings !== 'object') {
            return;
        }

        Object.entries(boxSettings).forEach(([boxType, boxData]) => {
            const isActive = !boxData || typeof boxData.is_active === 'undefined' ? true : !!boxData.is_active;
            const styling = boxData && boxData.styling_settings ? boxData.styling_settings : {};
            const content = boxData && boxData.content_settings ? boxData.content_settings : {};
            const layout = boxData && boxData.layout_settings ? boxData.layout_settings : {};

            toggleBoxVisibility(boxType, isActive);
            if (!isActive) {
                return;
            }

            if (boxType === 'timetable_background_box') {
                const root = document.getElementById('timetable-background-box');
                setStyleValue(root, 'background-color', styling.background_color);
            }

            if (boxType === 'header_box') {
                const header = document.getElementById('header-box');
                setStyleValue(header, 'background-color', styling.background_color);
                setStyleValue(header, 'color', styling.text_color);
                setStyleValue(header, 'font-family', styling.font_family);
                setStyleValue(header, 'padding', styling.padding, 'px');
                if (header && styling.border_width && styling.border_color) {
                    header.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }
                setStyleValue(header, 'border-radius', styling.border_radius);
                document.querySelectorAll('#current-time').forEach((node) => setStyleValue(node, 'font-size', styling.time_font_size, 'rem'));
                document.querySelectorAll('#gregorian-date, #islamic-date').forEach((node) => setStyleValue(node, 'font-size', styling.date_font_size, 'rem'));
            }

            if (boxType === 'prayer_times_box') {
                const prayerSection = document.getElementById('prayer-times-section');
                const prayerBox = document.getElementById('prayer-times-box');
                setStyleValue(prayerSection, 'background-color', styling.background_color);
                setStyleValue(prayerSection, 'color', styling.text_color);
                setStyleValue(prayerSection, 'font-family', styling.font_family);
                setStyleValue(prayerSection, 'padding', styling.padding, 'px');
                if (prayerSection && styling.border_width && styling.border_color) {
                    prayerSection.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }

                if (Array.isArray(layout.column_widths) && layout.column_widths.length >= 3) {
                    const templateColumns = `${layout.column_widths[0]} ${layout.column_widths[1]} ${layout.column_widths[2]}`;
                    const prayerHeader = prayerSection ? prayerSection.querySelector('.prayer-header') : null;
                    if (prayerHeader) {
                        prayerHeader.style.gridTemplateColumns = templateColumns;
                    }
                    if (prayerSection) {
                        prayerSection.querySelectorAll('.prayer-row').forEach((row) => {
                            row.style.gridTemplateColumns = templateColumns;
                        });
                    }
                }

                const beginningSpacing = normalizeCssValue(layout.beginning_column_spacing, 'px');
                if (beginningSpacing && prayerSection) {
                    const headerCells = prayerSection.querySelectorAll('.prayer-header .prayer-col-header');
                    if (headerCells[1]) {
                        headerCells[1].style.marginLeft = `-${beginningSpacing}`;
                    }
                    prayerSection.querySelectorAll('.prayer-row [data-time-type="beginning"]').forEach((node) => {
                        node.style.marginLeft = `-${beginningSpacing}`;
                    });
                }

                const headerCells = prayerSection ? prayerSection.querySelectorAll('.prayer-header .prayer-col-header') : [];
                if (headerCells[1] && content.beginning_title) {
                    headerCells[1].textContent = content.beginning_title;
                }
                if (headerCells[2] && content.jamaat_time_title) {
                    headerCells[2].textContent = content.jamaat_time_title;
                }

                if (prayerBox && layout.width) {
                    prayerBox.style.width = normalizeCssValue(layout.width);
                }
            }

            if (boxType === 'announcements_box') {
                const section = document.getElementById('announcements-section');
                const header = section ? section.querySelector('.announcements-header') : null;
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                setStyleValue(section, 'font-size', styling.font_size, 'rem');
                if (section && styling.border_width && styling.border_color) {
                    section.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }
                setStyleValue(section, 'padding', styling.padding, 'px');
                setStyleValue(header, 'color', styling.title_color);
                setStyleValue(header, 'font-size', styling.title_font_size, 'px');
                if (header && content.title) {
                    header.textContent = content.title;
                }

                if (typeof layout.max_height !== 'undefined') {
                    announcementTextMaxHeight = normalizeCssValue(layout.max_height, 'px') || announcementTextMaxHeight;
                    document.querySelectorAll('#announcements-content .announcement-text-container').forEach((element) => {
                        element.style.maxHeight = announcementTextMaxHeight;
                    });
                }
            }

            if (boxType === 'special_times_box') {
                const section = document.querySelector('[data-box-root="special_times_box"]');
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                if (section && styling.border_width && styling.border_color) {
                    section.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }

                const labels = section ? section.querySelectorAll('.time-label') : [];
                const titles = [
                    content.sehri_ends_title,
                    content.sun_rise_title,
                    content.noon_title,
                    content.jumah_1_title,
                    content.jumah_2_title,
                    content.eid_prayer_1_title,
                    content.eid_prayer_2_title
                ];
                labels.forEach((label, index) => {
                    if (titles[index]) {
                        label.textContent = titles[index];
                    }
                });

                if (section && Array.isArray(layout.column_widths) && layout.column_widths.length > 0) {
                    const grid = section.querySelector('.additional-times');
                    if (grid) {
                        grid.style.gridTemplateColumns = layout.column_widths.join(' ');
                    }
                }
            }

            if (boxType === 'sliding_text_box') {
                const section = document.querySelector('[data-box-root="sliding_text_box"]');
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                setStyleValue(section, 'font-size', styling.font_size, 'rem');
                setStyleValue(section, 'text-align', layout.text_alignment);
                if (section && styling.border_width && styling.border_color) {
                    section.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }
                setStyleValue(section, 'padding', styling.padding, 'px');
            }

            if (boxType === 'welcome_box') {
                const section = document.querySelector('[data-box-root="welcome_box"]');
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                setStyleValue(section, 'font-size', styling.font_size);
                setStyleValue(section, 'font-weight', styling.font_weight);
                setStyleValue(section, 'text-align', layout.text_alignment);
                setStyleValue(section, 'padding', styling.padding, 'px');
                if (section && typeof content.welcome_text === 'string') {
                    section.textContent = content.welcome_text;
                }
            }

            if (boxType === 'note_prayer_box') {
                const section = document.querySelector('[data-box-root="note_prayer_box"]');
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                setStyleValue(section, 'font-size', styling.font_size);
                if (section && styling.border_width && styling.border_color) {
                    section.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }
                const noteText = section ? section.querySelector('.next-prayer-text') : null;
                if (noteText && content.text) {
                    noteText.textContent = content.text;
                }
            }
        });
    }

    function applyScreenConfig(configPayload) {
        if (!configPayload || typeof configPayload !== 'object') {
            return;
        }

        applyThemeVariables(configPayload.theme_variables || {});
        applyBoxConfig(configPayload.box_settings || {});
        applyBoxOrdering(configPayload.box_settings || {}, configPayload.box_order || []);
    }

    function clearAllScreenContent() {
        const mediaOverlay = document.getElementById('media-overlay');
        const countdownPopup = document.getElementById('countdown-popup');
        const mediaContent = document.getElementById('media-content');
        const mediaCountdown = document.getElementById('media-countdown');

        if (mediaOverlay) {
            mediaOverlay.style.display = 'none';
        }

        if (countdownPopup) {
            countdownPopup.style.display = 'none';
        }

        if (mediaContent) {
            mediaContent.innerHTML = '';
        }

        if (mediaCountdown) {
            mediaCountdown.style.display = 'none';
            mediaCountdown.innerHTML = '';
        }

        clearTimeout(mediaDisplayTimer);
        clearInterval(countdownTimer);

        mediaDisplayTimer = null;
        countdownTimer = null;
        currentMedia = null;
        currentMediaData = null;
    }

    function renderCountdownState(data) {
        const countdownPopup = document.getElementById('countdown-popup');
        const popupTitle = document.getElementById('countdown-popup-title');
        const popupPrayer = document.getElementById('countdown-popup-prayer');
        const popupTimer = document.getElementById('countdown-popup-timer');

        if (!countdownPopup || !data || !data.countdown || !data.countdown.prayer_time) {
            renderTimetableState();
            return;
        }

        const prayerName = data.countdown.prayer_name || 'Prayer';
        if (popupTitle) {
            popupTitle.textContent = 'Countdown';
        }
        if (popupPrayer) {
            popupPrayer.textContent = prayerName;
        }
        if (popupTimer) {
            popupTimer.textContent = '00';
        }

        countdownPopup.style.display = 'flex';
        countdownPopup.style.position = 'fixed';
        countdownPopup.style.top = '50%';
        countdownPopup.style.left = '50%';
        countdownPopup.style.transform = 'translate(-50%, -50%)';
        startCountdownTimer(data.countdown.prayer_time);
    }

    function renderAdhanState() {
        const overlay = document.getElementById('media-overlay');
        const mediaContent = document.getElementById('media-content');
        if (!overlay || !mediaContent) {
            return;
        }

        mediaContent.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #000; color: #fff;">
                <div style="font-size: 5rem; font-weight: bold; letter-spacing: 0.2rem;">ADHAN</div>
                <div style="font-size: 1.6rem; margin-top: 1rem;">Prayer time has started</div>
            </div>
        `;

        overlay.style.display = 'flex';
    }

    function renderMediaState(media, posterType) {
        const normalizedMedia = normalizeMediaPayload(media);
        const overlay = document.getElementById('media-overlay');
        const mediaContent = document.getElementById('media-content');

        if (!normalizedMedia || !normalizedMedia.file_url || !overlay || !mediaContent) {
            renderTimetableState();
            return;
        }

        const isSameMedia = currentMediaData
            && normalizedMedia
            && currentMediaData.id === normalizedMedia.id
            && currentMediaData.file_url === normalizedMedia.file_url
            && currentScreenState === posterType;

        if (isSameMedia) {
            overlay.style.display = 'flex';
            return;
        }

        clearInterval(countdownTimer);
        clearTimeout(mediaDisplayTimer);

        currentMedia = normalizedMedia;
        currentMediaData = normalizedMedia;

        let mediaElement = null;
        if (normalizedMedia.type === 'image') {
            mediaElement = document.createElement('img');
            mediaElement.src = normalizedMedia.file_url;
            mediaElement.alt = normalizedMedia.title;
            mediaElement.style.cssText = 'width: 100%; height: 100%; object-fit: contain; display: block; position: relative; z-index: 1;';
            mediaElement.onerror = function() {
                mediaContent.innerHTML = `<div style="color: white; text-align: center; padding: 20px;">Failed to load image: ${normalizedMedia.title}</div>`;
            };
        } else if (normalizedMedia.type === 'video') {
            mediaElement = document.createElement('video');
            mediaElement.src = normalizedMedia.file_url;
            mediaElement.autoplay = true;
            mediaElement.loop = true;
            mediaElement.muted = true;
            mediaElement.style.cssText = 'width: 100%; height: 100%; object-fit: contain;';
        }

        if (!mediaElement) {
            renderTimetableState();
            return;
        }

        mediaContent.innerHTML = '';
        mediaContent.appendChild(mediaElement);
        overlay.style.display = 'flex';

        const refreshDelayMs = Math.max(1000, toPositiveInteger(normalizedMedia.display_duration, 3) * 1000 + 250);
        mediaDisplayTimer = setTimeout(() => {
            // After display duration ends, poll for next media and RESUME polling
            requestMediaSync();
            mediaPollingTimer = setInterval(requestMediaSync, POLL_INTERVALS.media);
        }, refreshDelayMs);
    }

    function renderTimetableState() {
        const overlay = document.getElementById('media-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }

        clearTimeout(mediaDisplayTimer);
        clearInterval(countdownTimer);
        mediaDisplayTimer = null;
        countdownTimer = null;
        
        // RESUME media polling when returning to timetable
        if (!mediaPollingTimer) {
            mediaPollingTimer = setInterval(requestMediaSync, POLL_INTERVALS.media);
        }
    }

    function startCountdownTimer(prayerTime) {
        const targetTime = new Date(prayerTime).getTime();
        if (Number.isNaN(targetTime)) {
            return;
        }

        function tick() {
            const now = Date.now();
            const distance = targetTime - now;

            if (distance <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                requestMediaSync();
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const timeDisplay = document.getElementById('countdown-time-display');
            if (timeDisplay) {
                timeDisplay.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            const popupTimer = document.getElementById('countdown-popup-timer');
            if (popupTimer) {
                const totalSeconds = Math.max(0, Math.floor(distance / 1000));
                popupTimer.textContent = totalSeconds.toString().padStart(2, '0');
            }
        }

        clearInterval(countdownTimer);
        tick();
        countdownTimer = setInterval(tick, 1000);
    }

    function applyScreenState(screenStateData) {
        const nextState = (screenStateData && screenStateData.state) ? screenStateData.state : 'TIMETABLE';
        const nextMedia = normalizeMediaPayload(screenStateData && screenStateData.media ? screenStateData.media : null);
        const stateChanged = nextState !== currentScreenState;

        if (stateChanged) {
            clearAllScreenContent();
        }

        currentScreenState = nextState;

        if (nextState === 'COUNTDOWN') {
            renderCountdownState(screenStateData);
            return;
        }

        if (nextState === 'ADHAN') {
            renderAdhanState();
            return;
        }

        if (isPosterState(nextState)) {
            renderMediaState(nextMedia, nextState);
            return;
        }

        renderTimetableState();
    }

    async function pollMediaState() {
        try {
            const [screenStateData, currentMediaResponse] = await Promise.all([
                fetchJson('/api/screen-state'),
                fetchJson('/api/current-media')
            ]);

            if (!screenStateData || !screenStateData.state) {
                return;
            }

            const fallbackMedia = normalizeMediaPayload(currentMediaResponse && currentMediaResponse.media ? currentMediaResponse.media : null);
            if (isPosterState(screenStateData.state) && !screenStateData.media && fallbackMedia) {
                screenStateData.media = fallbackMedia;
            }

            const nextStateVersion = screenStateData.state_version || null;
            const shouldApply = nextStateVersion
                ? hasVersionChanged('state', nextStateVersion)
                : true;

            if (shouldApply) {
                applyScreenState(screenStateData);
            }

            syncKnownVersions(screenStateData);
            syncKnownVersions(currentMediaResponse);
        } catch (error) {
            console.error('Error polling media state:', error);
        }
    }

    function requestMediaSync() {
        if (isMediaPollInFlight) {
            return;
        }

        isMediaPollInFlight = true;
        pollMediaState().finally(() => {
            isMediaPollInFlight = false;
        });
    }

    function initMediaDisplay() {
        requestMediaSync();
        mediaPollingTimer = setInterval(requestMediaSync, POLL_INTERVALS.media);
    }

    function updateSpecialTimesDisplay(todayData) {
        const specialTimes = {
            sehri_ends: todayData && todayData.fajr ? todayData.fajr : null,
            sun_rise: todayData && todayData.sun_rise ? todayData.sun_rise : null,
            noon: todayData && todayData.zohar ? todayData.zohar : null,
            jumah_1: todayData && todayData.jumah_1 ? todayData.jumah_1 : null,
            jumah_2: todayData && todayData.jumah_2 ? todayData.jumah_2 : null,
            eid_prayer_1: todayData && todayData.eid_prayer_1 ? todayData.eid_prayer_1 : null,
            eid_prayer_2: todayData && todayData.eid_prayer_2 ? todayData.eid_prayer_2 : null
        };

        Object.entries(specialTimes).forEach(([key, value]) => {
            const formatted = formatTimeForDisplay(value);
            document.querySelectorAll(`[data-special-time="${key}"]`).forEach(element => {
                if (element.textContent !== formatted) {
                    element.textContent = formatted;
                }
            });
        });
    }

    function applyTimetableUpdate(payload) {
        if (!payload) {
            return;
        }

        if (payload.jamaat_offsets && typeof payload.jamaat_offsets === 'object') {
            Object.assign(jamaatOffsets, payload.jamaat_offsets);
        }

        const todayData = payload.today || {};
        const tomorrowData = payload.tomorrow || {};
        const prayers = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];

        updatedPrayers.clear();
        updatedSpecialTimes.clear();

        todaySpecialTimesData = deriveSpecialTimes(todayData);
        tomorrowSpecialTimesData = deriveSpecialTimes(tomorrowData);
        originalTodaySpecialTimesData = { ...todaySpecialTimesData };

        prayers.forEach(prayerName => {
            const todayBeginning = todayData[prayerName] || null;
            const todayJamaat = computeJamaatTime(todayBeginning, todayData[`${prayerName}_jamaat`] || null, jamaatOffsets[prayerName]);
            const tomorrowBeginning = tomorrowData[prayerName] || null;
            const tomorrowJamaat = computeJamaatTime(tomorrowBeginning, tomorrowData[`${prayerName}_jamaat`] || null, jamaatOffsets[prayerName]);

            prayerTimesData[prayerName] = todayJamaat;
            todayBeginningTimes[prayerName] = todayBeginning;
            tomorrowPrayerTimesData[prayerName] = tomorrowJamaat;
            tomorrowBeginningTimes[prayerName] = tomorrowBeginning;
            originalTodayTimes.beginning[prayerName] = todayBeginning;
            originalTodayTimes.jamaat[prayerName] = todayJamaat;
            adhanTimesData[`${prayerName}_adhan`] = todayData[`${prayerName}_adhan`] || todayBeginning;

            const prayerRow = document.querySelector(`[data-prayer-name="${prayerName}"]`);
            if (!prayerRow) {
                return;
            }

            const beginningElement = prayerRow.querySelector('[data-time-type="beginning"]');
            const jamaatElement = prayerRow.querySelector('[data-time-type="jamaat"]');
            const formattedBeginning = formatTimeForDisplay(todayBeginning);
            const formattedJamaat = formatTimeForDisplay(todayJamaat);

            if (beginningElement && beginningElement.textContent !== formattedBeginning) {
                beginningElement.textContent = formattedBeginning;
            }

            if (jamaatElement && jamaatElement.textContent !== formattedJamaat) {
                jamaatElement.textContent = formattedJamaat;
            }
        });

        updateSpecialTimesDisplay(todayData);

        if (payload.islamic_date) {
            updateIslamicDateDisplay(payload.islamic_date);
        }

        if (payload.server_date) {
            lastKnownDate = payload.server_date;
        }

        updateNextPrayerCountdown();
        checkAndUpdatePrayerTimes();
    }

    async function pollTimetableData() {
        try {
            const data = await fetchJson('/api/timetable');
            const nextTimetableVersion = data && data.timetable_version ? data.timetable_version : null;

            if (hasVersionChanged('timetable', nextTimetableVersion)) {
                applyTimetableUpdate(data);
            }

            syncKnownVersions(data);
        } catch (error) {
            console.error('Error polling timetable:', error);
        }
    }

    function requestTimetableSync() {
        if (isTimetablePollInFlight) {
            return;
        }

        isTimetablePollInFlight = true;
        pollTimetableData().finally(() => {
            isTimetablePollInFlight = false;
        });
    }

    function announcementAnimationDuration(scrollSpeed) {
        const speed = Math.max(1, Number(scrollSpeed) || 3);
        return Math.max(5, 50 / speed);
    }

    function createAnnouncementNode(announcement, index) {
        const item = document.createElement('div');
        item.className = 'announcement-item rotating-announcement';
        item.dataset.index = String(index);
        item.dataset.duration = String((announcement.display_duration ?? 10) * 1000);
        item.style.display = 'none';
        item.style.margin = '0';
        item.style.padding = '0';
        item.style.wordWrap = 'break-word';
        item.style.wordBreak = 'break-word';
        item.style.overflow = 'hidden';
        item.style.backgroundColor = announcement.background_color;
        item.style.height = '100%';
        item.style.width = '100%';
        item.style.flexDirection = 'column';
        item.style.flex = '1';

        const title = document.createElement('div');
        title.className = 'announcement-title';
        title.style.fontWeight = 'bold';
        title.style.marginBottom = '8px';
        title.style.color = announcement.text_color;
        title.style.fontSize = `${announcement.font_size}px`;
        title.style.lineHeight = '1.3';
        title.style.overflow = 'hidden';
        title.style.textOverflow = 'ellipsis';
        title.textContent = announcement.title;

        const textContainer = document.createElement('div');
        textContainer.className = 'announcement-text-container';
        textContainer.style.flex = '1';
        textContainer.style.overflow = 'hidden';
        textContainer.style.position = 'relative';
        textContainer.style.display = 'flex';
        textContainer.style.flexDirection = 'column';
        textContainer.style.justifyContent = 'flex-start';

        const scrollingText = document.createElement('div');
        scrollingText.className = 'announcement-text-scroll';
        scrollingText.style.fontSize = `${announcement.font_size}px`;
        scrollingText.style.color = announcement.text_color;
        scrollingText.style.wordWrap = 'break-word';
        scrollingText.style.lineHeight = '1.4';
        scrollingText.style.margin = '0';
        scrollingText.style.minHeight = '100%';
        scrollingText.style.animation = `scroll-vertical ${announcementAnimationDuration(announcement.scroll_speed)}s linear infinite`;
        scrollingText.style.animationPlayState = 'running';
        scrollingText.setAttribute('data-scroll-speed', String(announcement.scroll_speed));
        scrollingText.textContent = announcement.content;

        textContainer.appendChild(scrollingText);
        item.appendChild(title);
        item.appendChild(textContainer);

        return item;
    }

    function renderAnnouncements(announcements) {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) {
            return;
        }

        contentContainer.innerHTML = '';

        if (announcements.length === 0) {
            const placeholder = document.createElement('div');
            placeholder.className = 'announcement-placeholder';
            placeholder.style.textAlign = 'center';
            placeholder.style.padding = '20px';

            const text = document.createElement('p');
            text.style.margin = '0';
            text.style.fontSize = '0.9rem';
            text.textContent = 'No announcements currently.';

            placeholder.appendChild(text);
            contentContainer.appendChild(placeholder);
            return;
        }

        announcements.forEach((announcement, index) => {
            contentContainer.appendChild(createAnnouncementNode(announcement, index));
        });
        
        // Immediately show the first announcement
        setTimeout(() => {
            const firstAnnouncement = contentContainer.querySelector('.rotating-announcement');
            if (firstAnnouncement) {
                firstAnnouncement.style.display = 'block';
                firstAnnouncement.style.opacity = '1';
                firstAnnouncement.style.transform = 'translateY(0)';
            }
        }, 10);
    }

    async function pollAnnouncementsData() {
        // Skip polling - use static announcements from HTML template
        // This ensures announcements are always present and display modes work reliably
        return;
    }

    async function pollScreenConfigData() {
        try {
            const response = await fetchJson('/api/screen-config');
            const nextConfigVersion = response && response.config_version ? response.config_version : null;

            if (hasVersionChanged('config', nextConfigVersion)) {
                applyScreenConfig(response);
                requestTimetableSync();
                requestAnnouncementsSync();
            }

            syncKnownVersions(response);
        } catch (error) {
            console.error('Error polling screen config:', error);
        }
    }

    function requestAnnouncementsSync() {
        if (isAnnouncementsPollInFlight) {
            return;
        }

        isAnnouncementsPollInFlight = true;
        pollAnnouncementsData().finally(() => {
            isAnnouncementsPollInFlight = false;
        });
    }

    function requestScreenConfigSync() {
        if (isScreenConfigPollInFlight) {
            return;
        }

        isScreenConfigPollInFlight = true;
        pollScreenConfigData().finally(() => {
            isScreenConfigPollInFlight = false;
        });
    }

    function initContentRotation() {
        const hadeeths = document.querySelectorAll('.rotating-hadeeth');
        if (hadeeths.length > 1) {
            let currentHadeethIndex = 0;
            setInterval(() => {
                hadeeths[currentHadeethIndex].style.display = 'none';
                currentHadeethIndex = (currentHadeethIndex + 1) % hadeeths.length;
                hadeeths[currentHadeethIndex].style.display = 'block';
            }, 30 * 1000);
        }

        initDynamicAnnouncements();
    }

    function checkAnnouncementScrolling() {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) return;

        const announcements = Array.from(contentContainer.querySelectorAll('.rotating-announcement'));
        announcements.forEach(el => {
            const container = el.querySelector('.announcement-text-container');
            const scrollDiv = el.querySelector('.announcement-text-scroll');

            if (!container || !scrollDiv) return;

            const isOverflowing = scrollDiv.scrollHeight > container.clientHeight;
            if (isOverflowing) {
                scrollDiv.classList.remove('no-scroll');
                scrollDiv.style.animationPlayState = 'running';
            } else {
                scrollDiv.classList.add('no-scroll');
                scrollDiv.style.animationPlayState = 'paused';
            }
        });
    }

    function setAnnouncementDisplayMode(mode) {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) return;

        const announcements = Array.from(contentContainer.querySelectorAll('.rotating-announcement'));
        
        // Clear any existing rotation
        if (announcementRotationInterval) {
            clearTimeout(announcementRotationInterval);
            announcementRotationInterval = null;
        }

        contentContainer.dataset.displayMode = mode;

        if (mode === 'show-all') {
            // Show all announcements with equal heights
            contentContainer.style.cssText = `
                overflow: auto !important;
                overflow-y: auto !important;
                position: relative !important;
                display: flex !important;
                flex-direction: column !important;
            `;
            
            // Calculate equal height for each announcement
            // All boxes share the available height equally
            announcements.forEach(el => {
                // Preserve background color from inline style
                const bgColor = window.getComputedStyle(el).backgroundColor;
                const textColor = window.getComputedStyle(el).color;
                
                el.style.cssText = `
                    display: flex !important;
                    flex: 1 1 0 !important;
                    position: relative !important;
                    flex-direction: column !important;
                    margin: 0 !important;
                    width: 100% !important;
                    overflow: hidden !important;
                    background-color: ${bgColor} !important;
                    color: ${textColor} !important;
                `;
            });

            // Check scrolling after layout is settled
            setTimeout(checkAnnouncementScrolling, 100);
        } else {
            // Rotation mode - show only first, set up rotation
            contentContainer.style.cssText = `
                overflow: hidden !important;
                overflow-y: hidden !important;
                position: relative !important;
                display: flex !important;
                flex-direction: column !important;
            `;
            
            announcements.forEach((el, i) => {
                // Preserve background color
                const bgColor = window.getComputedStyle(el).backgroundColor;
                const textColor = window.getComputedStyle(el).color;
                
                el.style.cssText = `
                    display: ${i === 0 ? 'block' : 'none'} !important;
                    position: absolute !important;
                    height: 100% !important;
                    width: 100% !important;
                    flex-direction: column !important;
                    top: 0 !important;
                    left: 0 !important;
                    margin: 0 !important;
                    background-color: ${bgColor} !important;
                    color: ${textColor} !important;
                `;
            });
            // Initialize rotation
            initDynamicAnnouncements();
        }
    }

    function initDynamicAnnouncements() {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) return;

        const announcements = Array.from(contentContainer.querySelectorAll('.rotating-announcement'));
        if (announcements.length === 0) return;

        // Clear any existing rotation
        if (announcementRotationInterval) {
            clearTimeout(announcementRotationInterval);
            announcementRotationInterval = null;
        }

        let currentIndex = 0;

        function showOnly(index) {
            announcements.forEach((el, i) => {
                if (i === index) {
                    el.style.display = 'flex';  // Use flex to respect flex-direction
                    el.style.opacity = '1';
                } else {
                    el.style.display = 'none';
                    el.style.opacity = '0';
                }
            });
            
            // Check if text overflows and enable/disable scrolling accordingly
            setTimeout(() => {
                const currentEl = announcements[index];
                if (!currentEl || currentEl.style.display === 'none') {
                    console.warn(`Announcement ${index} is not visible`);
                    return;
                }
                
                const container = currentEl.querySelector('.announcement-text-container');
                const scrollDiv = currentEl.querySelector('.announcement-text-scroll');
                
                if (container && scrollDiv) {
                    // Force a reflow to ensure dimensions are calculated
                    const forceReflow = currentEl.offsetHeight;
                    const containerReflow = container.offsetHeight;
                    
                    // Get dimensions from visible element
                    const containerHeight = container.clientHeight;
                    const scrollHeight = scrollDiv.scrollHeight;
                    
                    const isOverflowing = scrollHeight > containerHeight;
                    console.log(`Announcement ${index}: scrollHeight=${scrollHeight}, containerHeight=${containerHeight}, overflowing=${isOverflowing}`);
                    
                    if (isOverflowing) {
                        // Text doesn't fit - enable scrolling
                        scrollDiv.classList.remove('no-scroll');
                        scrollDiv.style.animationPlayState = 'running';
                    } else {
                        // Text fits - disable scrolling
                        scrollDiv.classList.add('no-scroll');
                        scrollDiv.style.animationPlayState = 'paused';
                    }
                } else {
                    console.warn(`Announcement ${index}: container or scrollDiv not found`);
                }
            }, 150);
        }

        function rotate() {
            currentIndex = (currentIndex + 1) % announcements.length;
            showOnly(currentIndex);
            
            const nextDuration = parseInt(announcements[currentIndex].dataset.duration) || 5000;
            announcementRotationInterval = setTimeout(rotate, nextDuration);
        }

        // Show first announcement
        showOnly(0);
        
        // Schedule next rotation
        const firstDuration = parseInt(announcements[0].dataset.duration) || 5000;
        announcementRotationInterval = setTimeout(rotate, firstDuration);
    }

    function resetDailyRuntimeState() {
        updatedPrayers.clear();
        updatedSpecialTimes.clear();
        currentScreenState = null;
        lastKnownVersions.announcements = null;
        lastKnownVersions.media = null;
        lastKnownVersions.timetable = null;
        lastKnownVersions.config = null;
        lastKnownVersions.state = null;
        clearAllScreenContent();
    }

    let lastAnnouncementDisplayMode = null;

    let lastConfigHash = null;

    function checkForConfigChanges() {
        fetch('/api/screen-config?v=' + Date.now())
            .then(response => response.json())
            .then(data => {
                const configStr = JSON.stringify(data);
                const currentHash = hashCode(configStr);
                
                if (lastConfigHash !== null && lastConfigHash !== currentHash) {
                    console.log('Config changed, reloading page...');
                    window.location.reload(true);
                }
                lastConfigHash = currentHash;
            })
            .catch(err => console.error('Config check failed:', err));
    }

    function hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return hash;
    }

    document.addEventListener('DOMContentLoaded', function() {
        initMediaDisplay();
        initContentRotation();
        requestTimetableSync();
        requestAnnouncementsSync();
        requestScreenConfigSync();

        timetablePollingTimer = setInterval(requestTimetableSync, POLL_INTERVALS.timetable);
        announcementsPollingTimer = setInterval(requestAnnouncementsSync, POLL_INTERVALS.announcements);
        screenConfigPollingTimer = setInterval(requestScreenConfigSync, POLL_INTERVALS.screenConfig);
        
        // Check for admin config changes every 3 seconds
        checkForConfigChanges();
        setInterval(checkForConfigChanges, 3000);
        
        // Listen for display mode changes from admin panel
        window.addEventListener('storage', function(e) {
            if (e.key === 'announcementDisplayMode') {
                const mode = e.newValue || 'rotation';
                setAnnouncementDisplayMode(mode);
            }
        });
        
        // Check localStorage for saved mode on page load
        const savedMode = localStorage.getItem('announcementDisplayMode') || 'rotation';
        lastAnnouncementDisplayMode = savedMode;
        // Always initialize the display mode
        setAnnouncementDisplayMode(savedMode);

        // Poll for display mode changes (fallback for same-window changes)
        setInterval(function() {
            const currentMode = localStorage.getItem('announcementDisplayMode') || 'rotation';
            if (currentMode !== lastAnnouncementDisplayMode) {
                lastAnnouncementDisplayMode = currentMode;
                setAnnouncementDisplayMode(currentMode);
            }
        }, 500); // Check every 500ms
    });

</script>
@endsection
