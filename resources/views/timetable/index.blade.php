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
    $bgColor = $timetableBgStyling['background_color']
        ?? ($settings['display_background_color'] ?? '#ffffff');
    $pageGapColor = $settings['display_background_color'] ?? '#ffffff';
    $backgroundStyle = "background-color: {$bgColor};";
@endphp
<div id="timetable-background-box" data-box-root="timetable_background_box" class="container-fluid digital-board" style="{{ $backgroundStyle }} padding: 0; margin: 0;">
    <!-- Unified Container for Consistent Width -->
    <div class="unified-container" style="padding: 0; margin: 0; --board-header-content-gap: clamp(8px, 1vh, 14px);">
        <!-- Top Header Row -->
        @if($useBoxesStyling && isset($boxSettings['header_box']))
            @php
                $headerBox = $boxSettings['header_box'] ?? null;
                $headerStyling = $headerBox['styling_settings'] ?? [];
                $timeFontSize = \App\Support\CssUnits::normalizeBoxRem($headerStyling['time_font_size'] ?? null, '3rem');
                $dateFontSize = \App\Support\CssUnits::normalizeBoxRem($headerStyling['date_font_size'] ?? null, '2.75rem');
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

        <div class="board-header-content-gap" aria-hidden="true" style="background-color: {{ $pageGapColor }};"></div>

        <!-- Main Content Area -->
        @php
            $prayerBoxForPadding = $useBoxesStyling && isset($boxSettings['prayer_times_box']) ? $boxSettings['prayer_times_box'] : null;
            $announcementsBoxForPadding = $useBoxesStyling && isset($boxSettings['announcements_box']) ? $boxSettings['announcements_box'] : null;
            $boardColumnPadding = $prayerBoxForPadding['styling_settings']['padding']
                ?? $announcementsBoxForPadding['styling_settings']['padding']
                ?? '15';
        @endphp
        <div class="board-main-content" style="margin: 0; --board-column-padding: {{ $boardColumnPadding }}px;">
            <div class="row h-100 m-0" style="display: flex; flex-direction: row; align-items: stretch;">
            <!-- Left Column - Prayer Times -->
            @php $__showPrayer = ($useBoxesStyling ? isset($boxSettings['prayer_times_box']) : (!isset($activeBoxTypes) || in_array('prayer_times_box', $activeBoxTypes))); @endphp
            @if($__showPrayer)
            <div id="prayer-times-box" data-box-root="prayer_times_box" class="col-md-8 p-0" style="{{ ($useBoxesStyling && !($boxSettings['prayer_times_box']['is_active'] ?? true)) ? 'display:none;' : '' }}; display: flex; flex-direction: column; height: 100%; min-height: 0;">
                @php
                    $prayerBox = $useBoxesStyling && isset($boxSettings['prayer_times_box']) ? $boxSettings['prayer_times_box'] : null;
                    $prayerStyling = $prayerBox['styling_settings'] ?? [];
                    $prayerLayout = $prayerBox['layout_settings'] ?? [];
                    // Safe style variables to avoid undefined array key errors on servers with older saved settings
                    $prayer_names_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['prayer_names_font_size'] ?? null, '4rem');
                    $beginning_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['beginning_font_size'] ?? null, '3.5rem');
                    $jamaat_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['jamaat_font_size'] ?? null, '3.5rem');
                    $header_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['header_font_size'] ?? null, '1.5rem');
                    $next_prayer_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['next_prayer_font_size'] ?? null, '1.4rem');
                    $next_prayer_countdown_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['next_prayer_countdown_font_size'] ?? null, '1.4rem');
                    $next_prayer_name_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['next_prayer_name_font_size'] ?? null, '0.9rem');
                    $prayer_box_font_size = \App\Support\CssUnits::normalizeBoxRem($prayerStyling['font_size'] ?? null, '3.5rem');
                    $prayerColumnWidths = \App\Support\CssUnits::normalizePrayerColumnWidths($prayerLayout['column_widths'] ?? null);
                @endphp
                @if($useBoxesStyling && isset($boxSettings['prayer_times_box']))
                    <div id="prayer-times-section" class="prayer-times-section" style="
                        background-color: {{ $prayerStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $prayerStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $prayerStyling['font_family'] ?? 'Arial, sans-serif' }};
                        font-size: {{ $prayer_box_font_size }};
                        border: {{ $prayerStyling['border_width'] ?? '1px' }} solid {{ $prayerStyling['border_color'] ?? '#0066cc' }};
                        margin: 0;
                        display: flex;
                        flex-direction: column;
                        flex: 1;
                        min-height: 0;
                        @if($settings['logo_path'] ?? false)
                        --logo-bg-image: url('{{ app()->environment('production') ? url('public/storage/' . $settings['logo_path']) : asset('storage/' . $settings['logo_path']) }}');
                        @endif
                    ">
                        <div class="prayer-header" style="
                            background-color: {{ $prayerStyling['header_background_color'] ?? 'transparent' }};
                            color: {{ $prayerStyling['header_text_color'] ?? '#000000' }};
                            font-size: {{ $header_font_size }};
                            margin: 0 0 8px 0;
                            padding: 8px 8px 8px 8px;
                            text-align: center;
                            font-weight: bold;
                            display: grid;
                            grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }};
                            gap: 6px;
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

                    <div class="prayer-times-body">
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
                                        font-size: {{ $next_prayer_font_size }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $next_prayer_countdown_font_size }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $next_prayer_name_font_size }} !important; 
                                        margin-top: 5px; 
                                        opacity: 0.8;
                                        color: {{ $prayerStyling['next_prayer_name_color'] ?? '#666666' }} !important;
                                    ">Calculating...</div>
                                </div>
                            @endif
                        @endif
                        <div class="prayer-list">
                            <div class="prayer-row" data-prayer-name="fajr" style="display: grid; grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }}; gap: 6px; margin-bottom: 4px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size }}; font-weight: bold;">Fajr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size }};">{{ $prayerTimes->fajr_jamaat ? \Carbon\Carbon::parse($prayerTimes->fajr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->fajr)->addMinutes((int)$settings['fajr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="zohar" style="display: grid; grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }}; gap: 6px; margin-bottom: 4px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size }}; font-weight: bold;">Zohar</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size }};">{{ $prayerTimes->zohar_jamaat ? \Carbon\Carbon::parse($prayerTimes->zohar_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->zohar)->addMinutes((int)$settings['zohar_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="asr" style="display: grid; grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }}; gap: 6px; margin-bottom: 4px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size }}; font-weight: bold;">Asr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->asr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size }};">{{ $prayerTimes->asr_jamaat ? \Carbon\Carbon::parse($prayerTimes->asr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->asr)->addMinutes((int)$settings['asr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="maghrib" style="display: grid; grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }}; gap: 6px; margin-bottom: 4px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size }}; font-weight: bold;">Maghrib</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->maghrib)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size }};">{{ $prayerTimes->maghrib_jamaat ? \Carbon\Carbon::parse($prayerTimes->maghrib_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->maghrib)->addMinutes((int)$settings['maghrib_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="isha" style="display: grid; grid-template-columns: {{ $prayerColumnWidths[0] }} {{ $prayerColumnWidths[1] }} {{ $prayerColumnWidths[2] }}; gap: 6px; margin-bottom: 4px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: {{ $prayer_names_font_size }}; font-weight: bold;">Isha</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: {{ $beginning_font_size }}; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->isha)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: {{ $jamaat_font_size }};">{{ $prayerTimes->isha_jamaat ? \Carbon\Carbon::parse($prayerTimes->isha_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->isha)->addMinutes((int)$settings['isha_jamaat_offset'])->format('h:i') }}</div>
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
                                        font-size: {{ $next_prayer_font_size }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $next_prayer_countdown_font_size }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $next_prayer_name_font_size }} !important; 
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
                                font-size: {{ \App\Support\CssUnits::normalizeBoxRem($noteStyling['font_size'] ?? null, '1.2rem') }};
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
                </div>
            @endif



            <!-- Right Column - Announcements (1/3 width) -->
            @php $__showAnnouncements = ($useBoxesStyling ? isset($boxSettings['announcements_box']) : (!isset($activeBoxTypes) || in_array('announcements_box', $activeBoxTypes))); @endphp
            @if($__showAnnouncements)
            <div class="col-md-4 p-0" data-box-root="announcements_box" style="{{ ($useBoxesStyling && !($boxSettings['announcements_box']['is_active'] ?? true)) ? 'display:none;' : '' }}; display: flex; flex-direction: column; height: 100%; min-height: 0;">
                @if($useBoxesStyling && isset($boxSettings['announcements_box']))
                    @php
                        $announcementsBox = $boxSettings['announcements_box'] ?? null;
                        $announcementsStyling = $announcementsBox['styling_settings'] ?? [];
                        $announcementsLayout = $announcementsBox['layout_settings'] ?? [];
                        $announcementsTitleBg = $announcementsStyling['title_background_color'] ?? '#1E4D2B';
                        $announcementsTitleText = $announcementsStyling['title_color'] ?? '#ffffff';
                        $announcementsTitleFontSize = \App\Support\CssUnits::normalizeBoxRem($announcementsStyling['title_font_size'] ?? null, '1.6rem');
                    @endphp
                    <div class="announcements-section" id="announcements-section" style="
                        --announcements-header-bg: {{ $announcementsTitleBg }};
                        --announcements-header-text: {{ $announcementsTitleText }};
                        background-color: {{ $announcementsStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $announcementsStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $announcementsStyling['font_family'] ?? 'Arial, sans-serif' }};
                        border: {{ $announcementsStyling['border_width'] ?? '1px' }} solid {{ $announcementsStyling['border_color'] ?? '#0066cc' }};
                        border-radius: 0px;
                        margin: 0;
                        display: flex;
                        flex-direction: column;
                        flex: 1;
                        min-height: 0;
                    ">
                        <div class="announcements-header" style="
                            background-color: {{ $announcementsTitleBg }};
                            color: {{ $announcementsTitleText }};
                            font-size: {{ $announcementsTitleFontSize }};
                            margin: 0 0 10px 0;
                            padding: 8px 8px 10px 8px;
                            text-align: center;
                            font-weight: bold;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @elseif(!$useBoxesStyling)
                    <div class="announcements-section" id="announcements-section" style="--announcements-header-bg: #1E4D2B; --announcements-header-text: #ffffff; margin: 0;">
                        <div class="announcements-header">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @endif
                    <div class="announcements-content" id="announcements-content" data-layout-version="0" style="margin: 0;" data-display-mode="rotation">
                        @if($announcements->count() > 0)
                            @foreach($announcements as $index => $announcement)
                                <div class="announcement-item rotating-announcement{{ $index === 0 ? ' is-active' : '' }}" 
                                     data-announcement-id="{{ $announcement->id }}"
                                     data-display-order="{{ $announcement->display_order ?? ($index + 1) }}"
                                     data-index="{{ $index }}" 
                                     data-duration="{{ ($announcement->display_duration ?? 10) * 1000 }}"
                                     data-display-duration-seconds="{{ $announcement->display_duration ?? 10 }}" 
                                     style="
                                    margin: 0;
                                    padding: 12px;
                                    box-sizing: border-box;
                                    word-wrap: break-word;
                                    word-break: break-word;
                                    overflow: hidden;
                                    background-color: {{ $announcement->background_color ?? '#ffffff' }};
                                    width: 100%;
                                    min-height: 0;
                                ">
                                    @if(filled($announcement->title))
                                    <div class="announcement-title" style="
                                        font-weight: bold;
                                        margin-bottom: 8px;
                                        color: {{ $announcement->text_color ?? '#000000' }};
                                        font-size: {{ \App\Support\CssUnits::normalizeAnnouncementRem($announcement->title_font_size ?? null, '2.25rem') }};
                                        line-height: 1.3;
                                        white-space: normal;
                                        overflow-wrap: break-word;
                                        word-break: normal;
                                    ">{{ $announcement->title }}</div>
                                    @endif
                                    @php
                                        // Calculate scroll speed for vertical scrolling (1-10)
                                        // Speed 1 = slowest (50s), Speed 10 = fastest (5s)
                                        $baseSpeed = 50; // base seconds
                                        $scrollSpeed = $announcement->scroll_speed ?? 3;
                                        $animationDuration = $baseSpeed / $scrollSpeed; // inversely proportional
                                        $animationDuration = max(5, $animationDuration); // minimum 5 seconds
                                    @endphp
                                    <div class="announcement-text-container" style="
                                        flex: 1 1 0;
                                        min-height: 0;
                                        overflow: hidden;
                                        position: relative;
                                        display: block;
                                        padding: 0;
                                    ">
                                        <div class="announcement-text-scroll" style="
                                            font-size: {{ \App\Support\CssUnits::normalizeAnnouncementRem($announcement->font_size ?? null, '1.5rem') }};
                                            color: {{ $announcement->text_color ?? '#000000' }};
                                            word-wrap: break-word;
                                            overflow-wrap: break-word;
                                            word-break: normal;
                                            white-space: normal;
                                            line-height: 1.45;
                                            margin: 0;
                                            padding: 0.15em 0 0.5em;
                                            transform: translate3d(0, 0, 0);
                                        " data-scroll-speed="{{ $scrollSpeed }}">{!! $announcement->contentHtml() !!}</div>
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
                $specialFontSize = \App\Support\CssUnits::normalizeBoxRem($specialStyling['font_size'] ?? null, '4rem');
                $specialHeaderFontSize = \App\Support\CssUnits::normalizeBoxRem($specialStyling['header_font_size'] ?? null, '4rem');
            @endphp
            <div class="board-bottom-times" data-box-root="special_times_box" style="
                {{ ($useBoxesStyling && !($boxSettings['special_times_box']['is_active'] ?? true)) ? 'display:none;' : '' }}
                background-color: {{ $specialStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                color: {{ $specialStyling['text_color'] ?? '#000000' }};
                font-family: {{ $specialStyling['font_family'] ?? 'Courier New, monospace' }};
                font-size: {{ $specialFontSize }};
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
                            font-size: {{ $specialFontSize }};
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
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[0] }}</div>
                                <div class="time-value" data-special-time="sehri_ends" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[0] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[1] }}</div>
                                <div class="time-value" data-special-time="sun_rise" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[1] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[2] }}</div>
                                <div class="time-value" data-special-time="noon" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[2] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[3] }}</div>
                                <div class="time-value" data-special-time="jumah_1" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[3] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[4] }}</div>
                                <div class="time-value" data-special-time="jumah_2" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[4] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[5] }}</div>
                                <div class="time-value" data-special-time="eid_prayer_1" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[5] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialHeaderFontSize }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[6] }}</div>
                                <div class="time-value" data-special-time="eid_prayer_2" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialFontSize }}; font-weight: bold;">{{ $times[6] }}</div>
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
                font-size: {{ \App\Support\CssUnits::normalizeBoxRem($welcomeStyling['font_size'] ?? null, '1.5rem') }};
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
                font-size: {{ \App\Support\CssUnits::normalizeBoxRem($slidingStyling['font_size'] ?? null, '5rem') }};
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
                                            ? \App\Support\CssUnits::normalizeBoxRem($slidingText->font_size, '5rem')
                                            : \App\Support\CssUnits::normalizeBoxRem($slidingStyling['font_size'] ?? null, '5rem');
                                        $slidingFontWeight = $slidingText->font_weight
                                            ?? ($slidingStyling['font_weight'] ?? '700');
                                        $slidingTextColor = $slidingText->text_color
                                            ?? ($slidingStyling['text_color'] ?? '#000000');
                                    @endphp
                                    <span class="scroll-item" data-sliding-text-id="{{ $slidingText->id }}" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? \App\Support\CssUnits::normalizeBoxRem($slidingText->font_size, '5rem')
                                            : \App\Support\CssUnits::normalizeBoxRem($slidingStyling['font_size'] ?? null, '5rem');
                                        $slidingFontWeight = $slidingText->font_weight
                                            ?? ($slidingStyling['font_weight'] ?? '700');
                                        $slidingTextColor = $slidingText->text_color
                                            ?? ($slidingStyling['text_color'] ?? '#000000');
                                    @endphp
                                    <span class="scroll-item" data-sliding-text-id="{{ $slidingText->id }}" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
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
                                            ? \App\Support\CssUnits::normalizeBoxRem($slidingText->font_size, '5rem')
                                            : '5rem';
                                        $slidingFontWeight = $slidingText->font_weight ?? '700';
                                        $slidingTextColor = $slidingText->text_color ?? '#000000';
                                    @endphp
                                    <span class="scroll-item" data-sliding-text-id="{{ $slidingText->id }}" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                    @php
                                        $slidingFontSize = $slidingText->font_size
                                            ? \App\Support\CssUnits::normalizeBoxRem($slidingText->font_size, '5rem')
                                            : '5rem';
                                        $slidingFontWeight = $slidingText->font_weight ?? '700';
                                        $slidingTextColor = $slidingText->text_color ?? '#000000';
                                    @endphp
                                    <span class="scroll-item" data-sliding-text-id="{{ $slidingText->id }}" style="font-size: {{ $slidingFontSize }}; font-weight: {{ $slidingFontWeight }}; color: {{ $slidingTextColor }};">{{ $slidingText->text }}</span>
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

    .board-header-content-gap {
        display: block !important;
        width: 100% !important;
        height: var(--board-header-content-gap, clamp(8px, 1vh, 14px)) !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-sizing: border-box !important;
        flex: 0 0 auto !important;
        order: 20 !important;
    }

    .unified-container {
        display: flex !important;
        flex-direction: column !important;
        height: 100vh !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }

    #header-box,
    .unified-container > .board-header {
        order: 10 !important;
        flex: 0 0 auto !important;
    }

    [data-box-root="special_times_box"] {
        order: 40 !important;
        flex: 0 0 auto !important;
    }

    [data-box-root="welcome_box"] {
        order: 50 !important;
        flex: 0 0 auto !important;
    }

    [data-box-root="sliding_text_box"] {
        order: 60 !important;
        flex: 0 0 auto !important;
    }

    /* Force prayer + larger announcements layout with identical top alignment */
    .board-main-content {
        order: 30 !important;
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        width: 100% !important;
        height: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        --board-prayer-width: 55%;
        --board-announce-width: 45%;
        --prayer-col-name: 30%;
        --prayer-col-beginning: 35%;
        --prayer-col-jamaat: 35%;
    }

    .board-main-content .row {
        display: flex !important;
        flex-direction: row !important;
        align-items: flex-start !important;
        flex: 1 1 0 !important;
        width: 100% !important;
        height: 100% !important;
        gap: 0 !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .board-main-content .col-md-8,
    .board-main-content .col-md-4,
    [data-box-root="prayer_times_box"],
    [data-box-root="announcements_box"] {
        padding: 0 !important;
        margin: 0 !important;
        align-self: flex-start !important;
        min-width: 0 !important;
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    .board-main-content .col-md-8,
    [data-box-root="prayer_times_box"] {
        flex: 0 0 var(--board-prayer-width, 55%) !important;
        max-width: var(--board-prayer-width, 55%) !important;
        width: var(--board-prayer-width, 55%) !important;
    }

    .board-main-content .col-md-4,
    [data-box-root="announcements_box"] {
        flex: 0 0 var(--board-announce-width, 45%) !important;
        max-width: var(--board-announce-width, 45%) !important;
        width: var(--board-announce-width, 45%) !important;
    }

    [data-box-root="prayer_times_box"] > .prayer-times-section,
    [data-box-root="announcements_box"] > .announcements-section,
    .prayer-times-section,
    #announcements-section {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        height: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        overflow: hidden !important;
        padding-top: 0 !important;
        padding-left: var(--board-column-padding, clamp(15px, 2.5vh, 25px)) !important;
        padding-right: var(--board-column-padding, clamp(15px, 2.5vh, 25px)) !important;
        padding-bottom: var(--board-column-padding, clamp(15px, 2.5vh, 25px)) !important;
        border-top-width: 0 !important;
    }

    .prayer-times-body {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .prayer-list {
        gap: clamp(4px, 0.8vh, 10px) !important;
    }

    .prayer-row {
        gap: 6px !important;
        margin-bottom: 4px !important;
        padding-top: clamp(4px, 0.8vh, 8px) !important;
        padding-bottom: clamp(4px, 0.8vh, 8px) !important;
    }

    .announcements-content,
    #announcements-content {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        width: 100% !important;
        height: auto !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    .prayer-header,
    .announcements-header {
        flex: 0 0 auto !important;
        width: 100% !important;
        min-height: var(--board-header-row-height, auto);
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

    /* Announcements Section - full height flex chain */
    .announcements-section {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        height: 100% !important;
        overflow: hidden !important;
    }

    .announcement-item {
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow: hidden !important;
        align-items: stretch !important;
        justify-content: flex-start !important;
        box-sizing: border-box !important;
        min-height: 0 !important;
        flex-shrink: 1 !important;
    }

    .announcement-title {
        overflow: visible !important;
        white-space: normal !important;
        display: block !important;
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
        text-overflow: clip !important;
        overflow-wrap: break-word !important;
        word-break: normal !important;
        line-height: 1.3 !important;
    }

    .announcement-text {
        overflow: hidden !important;
        white-space: normal !important;
        display: block !important;
        line-height: 1.45 !important;
    }

    .announcement-text-container {
        position: relative !important;
        overflow: hidden !important;
        min-height: 0 !important;
        flex: 1 1 0 !important;
        align-self: stretch !important;
        display: block !important;
        padding: 0 !important;
    }

    @keyframes scroll-vertical-measured {
        0% {
            transform: translate3d(0, 0, 0);
        }
        100% {
            transform: translate3d(0, calc(-1 * var(--announcement-scroll-distance, 0px)), 0);
        }
    }

    .announcement-text-scroll {
        display: block !important;
        white-space: normal !important;
        overflow-wrap: break-word !important;
        word-break: normal !important;
        line-height: 1.45 !important;
        transform: translate3d(0, 0, 0);
        transform-origin: top left;
        will-change: auto;
        padding: 0.15em 0 0.5em;
        position: relative;
        top: 0;
        left: 0;
        backface-visibility: hidden;
    }

    .announcement-text-scroll.no-scroll {
        animation: none !important;
        transform: translate3d(0, 0, 0) !important;
        will-change: auto !important;
    }

    .announcement-text-scroll.is-scrolling {
        will-change: transform !important;
    }

    /* Display Mode: Show All — equal-height rows, each with internal scroll */
    #announcements-content[data-display-mode="show-all"] {
        display: grid !important;
        grid-template-columns: 1fr !important;
        grid-auto-rows: minmax(0, 1fr) !important;
        align-content: stretch !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow: hidden !important;
        gap: 0 !important;
        padding: 0 !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-item {
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
        max-height: 100% !important;
        margin: 0 !important;
        padding: 12px !important;
        border: none !important;
        border-radius: 0 !important;
        position: relative !important;
        overflow: hidden !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-title {
        font-weight: bold !important;
        margin-bottom: 5px !important;
        line-height: 1.2 !important;
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-text-container {
        overflow: hidden !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        position: relative !important;
        display: block !important;
    }

    #announcements-content[data-display-mode="show-all"] .announcement-text-scroll {
        min-height: 0 !important;
        line-height: 1.4 !important;
    }

    /* Display Mode: Rotation — only the active card is visible and fills the panel */
    #announcements-content[data-display-mode="rotation"] {
        position: relative !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        width: 100% !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-item {
        display: none !important;
        flex-direction: column !important;
        flex: 1 1 0 !important;
        min-height: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 12px !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        position: absolute !important;
        inset: 0 !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-item.is-active {
        display: flex !important;
        position: relative !important;
        inset: auto !important;
        max-height: 100% !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-title {
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
        margin-bottom: 8px !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-text-container {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow: hidden !important;
        position: relative !important;
        display: block !important;
    }

    #announcements-content[data-display-mode="rotation"] .announcement-text-scroll {
        min-height: 0 !important;
    }

</style>

@endsection

@section('scripts')
<script src="{{ asset('js/announcement-scroll.js') }}?v={{ filemtime(public_path('js/announcement-scroll.js')) }}"></script>
@php
    use App\Support\PrayerJamaatTime;

    // Today's prayer times (resolved jamaat / iqamah values)
    $prayerTimesJson = [
        'fajr' => $prayerTimes ? PrayerJamaatTime::resolve($prayerTimes, 'fajr')?->format('H:i') : null,
        'zohar' => $prayerTimes ? PrayerJamaatTime::resolve($prayerTimes, 'zohar')?->format('H:i') : null,
        'asr' => $prayerTimes ? PrayerJamaatTime::resolve($prayerTimes, 'asr')?->format('H:i') : null,
        'maghrib' => $prayerTimes ? PrayerJamaatTime::resolve($prayerTimes, 'maghrib')?->format('H:i') : null,
        'isha' => $prayerTimes ? PrayerJamaatTime::resolve($prayerTimes, 'isha')?->format('H:i') : null,
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
        'fajr' => $tomorrowPrayerTimes ? PrayerJamaatTime::resolve($tomorrowPrayerTimes, 'fajr')?->format('H:i') : null,
        'zohar' => $tomorrowPrayerTimes ? PrayerJamaatTime::resolve($tomorrowPrayerTimes, 'zohar')?->format('H:i') : null,
        'asr' => $tomorrowPrayerTimes ? PrayerJamaatTime::resolve($tomorrowPrayerTimes, 'asr')?->format('H:i') : null,
        'maghrib' => $tomorrowPrayerTimes ? PrayerJamaatTime::resolve($tomorrowPrayerTimes, 'maghrib')?->format('H:i') : null,
        'isha' => $tomorrowPrayerTimes ? PrayerJamaatTime::resolve($tomorrowPrayerTimes, 'isha')?->format('H:i') : null,
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
    $displayTimezone = $settings['timezone'] ?? config('app.timezone', 'Europe/London');
@endphp
<script>
    const APP_DISPLAY_TIMEZONE = @json($displayTimezone);
    // Prayer times data from PHP
    const prayerTimesData = @json($prayerTimesJson);
    const todayBeginningTimes = @json($todayBeginningTimesJson);
    const tomorrowPrayerTimesData = @json($tomorrowPrayerTimesJson);
    const tomorrowBeginningTimes = @json($tomorrowBeginningTimesJson);
    const jamaatOffsets = @json($jamaatOffsetsJson);
    const adhanTimesData = @json($adhanTimesJson);
    
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

    function nowInAppTimezone() {
        return new Date(new Date().toLocaleString('en-US', { timeZone: APP_DISPLAY_TIMEZONE }));
    }

    function getAppClockParts(date = new Date()) {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: APP_DISPLAY_TIMEZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).formatToParts(date);

        const pick = (type) => parts.find((part) => part.type === type)?.value || '00';
        return {
            year: pick('year'),
            month: pick('month'),
            day: pick('day'),
            hours: Number(pick('hour')),
            minutes: Number(pick('minute')),
            seconds: Number(pick('second')),
        };
    }

    // Update next prayer countdown (uses mosque timezone + jamaat times)
    function updateNextPrayerCountdown() {
        const countdownElement = document.getElementById('next-prayer-countdown');
        const prayerNameElement = document.getElementById('next-prayer-name');
        
        if (!countdownElement || !prayerNameElement) return;
        
        const clock = getAppClockParts();
        const currentTime = clock.hours * 60 + clock.minutes;
        
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
            const secondsUntil = 60 - clock.seconds;
            
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

    // Update countdown every second
    setInterval(updateNextPrayerCountdown, 1000);
    updateNextPrayerCountdown(); // Initial call
    
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
        const clock = getAppClockParts();
        const currentDateKey = `${clock.year}-${clock.month}-${clock.day}`;

        // Mosque clock (UK), not the laptop's local timezone
        const timeElements = document.querySelectorAll('#current-time');
        if (timeElements.length > 0) {
            let hours = clock.hours;
            const minutes = String(clock.minutes).padStart(2, '0');
            const seconds = String(clock.seconds).padStart(2, '0');
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

        updateGregorianDateDisplay(nowInAppTimezone());

        // Date changed (midnight) - refresh data via APIs only (no page reload)
        if (currentDateKey !== lastKnownDate) {
            markUpdateSource('midnight-sync', { previousDate: lastKnownDate, nextDate: currentDateKey });
            lastKnownDate = currentDateKey;
            resetDailyRuntimeState();
            requestTimetableSync();
            requestAnnouncementsSync();
            requestSlidingTextsSync();
            requestMediaSync();
            requestScreenConfigSync();
        }
    }

    setInterval(updateTimeAndDate, 1000);
    updateTimeAndDate();
    
    function getFullscreenElement() {
        return document.fullscreenElement
            || document.webkitFullscreenElement
            || document.mozFullScreenElement
            || document.msFullscreenElement
            || null;
    }

    function requestRootFullscreen() {
        const element = document.documentElement;

        if (element.requestFullscreen) {
            return element.requestFullscreen();
        }
        if (element.msRequestFullscreen) {
            return element.msRequestFullscreen();
        }
        if (element.mozRequestFullScreen) {
            return element.mozRequestFullScreen();
        }
        if (element.webkitRequestFullscreen) {
            return element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }

        return Promise.reject(new Error('Fullscreen not supported'));
    }

    function exitRootFullscreen() {
        if (document.exitFullscreen) {
            return document.exitFullscreen();
        }
        if (document.msExitFullscreen) {
            return document.msExitFullscreen();
        }
        if (document.mozCancelFullScreen) {
            return document.mozCancelFullScreen();
        }
        if (document.webkitExitFullscreen) {
            return document.webkitExitFullscreen();
        }

        return Promise.reject(new Error('Exit fullscreen not supported'));
    }

    function updateFullscreenButton() {
        const button = document.getElementById('fullscreenBtn');
        if (!button) {
            return;
        }

        let icon = button.querySelector('i');
        if (!icon) {
            icon = document.createElement('i');
            button.textContent = '';
            button.appendChild(icon);
        }

        let labelNode = Array.from(button.childNodes).find((node) => node.nodeType === Node.TEXT_NODE);
        if (!labelNode) {
            labelNode = document.createTextNode('');
            button.appendChild(labelNode);
        }

        if (getFullscreenElement()) {
            icon.className = 'bi bi-fullscreen-exit';
            labelNode.textContent = ' Exit Fullscreen';
            document.body.classList.add('fullscreen-mode');
            return;
        }

        icon.className = 'bi bi-arrows-fullscreen';
        labelNode.textContent = ' Enter Fullscreen';
        document.body.classList.remove('fullscreen-mode');
    }

    function scheduleFullscreenRestore(reason) {
        if (!fullscreenState.restoreEnabled || getFullscreenElement()) {
            return;
        }

        clearTimeout(fullscreenState.restoreTimer);
        fullscreenState.restoreTimer = setTimeout(() => {
            if (!fullscreenState.restoreEnabled || getFullscreenElement()) {
                return;
            }

            debugLog('fullscreen', 'Attempting fullscreen restore', {
                reason,
                lastRenderCause: dashboardRuntime.lastRenderCause,
                lastUpdateSource: dashboardRuntime.lastUpdateSource
            });

            requestRootFullscreen().catch((error) => {
                debugLog('fullscreen', 'Fullscreen restore failed', {
                    reason,
                    error: error && error.message ? error.message : error
                });
            });
        }, 250);
    }

    function toggleFullscreen() {
        if (!getFullscreenElement()) {
            fullscreenState.userActivated = true;
            fullscreenState.restoreEnabled = true;
            fullscreenState.manualExitRequested = false;

            debugLog('fullscreen', 'Manual fullscreen request', {
                lastRenderCause: dashboardRuntime.lastRenderCause
            });

            requestRootFullscreen().then(() => {
                document.body.classList.add('show-controls');
                setTimeout(() => {
                    document.body.classList.remove('show-controls');
                }, 3000);
                updateFullscreenButton();
            }).catch((error) => {
                debugLog('fullscreen', 'Fullscreen request failed', {
                    error: error && error.message ? error.message : error
                });
            });

            return;
        }

        fullscreenState.restoreEnabled = false;
        fullscreenState.manualExitRequested = true;
        clearTimeout(fullscreenState.restoreTimer);
        debugLog('fullscreen', 'Manual fullscreen exit requested', {});

        exitRootFullscreen().catch((error) => {
            debugLog('fullscreen', 'Fullscreen exit failed', {
                error: error && error.message ? error.message : error
            });
        });
    }

    function handleFullscreenChange(event) {
        const isActive = !!getFullscreenElement();

        if (!isActive && fullscreenState.wasActive) {
            const exitReason = fullscreenState.manualExitRequested ? 'manual-exit' : 'unexpected-exit';
            debugLog('fullscreen', 'Fullscreen exited', {
                reason: exitReason,
                eventType: event ? event.type : 'fullscreenchange',
                lastRenderCause: dashboardRuntime.lastRenderCause,
                lastUpdateSource: dashboardRuntime.lastUpdateSource
            });

            if (fullscreenState.restoreEnabled && !fullscreenState.manualExitRequested && fullscreenState.userActivated) {
                scheduleFullscreenRestore(exitReason);
            }
        }

        if (isActive && !fullscreenState.wasActive) {
            debugLog('fullscreen', 'Fullscreen active', {
                eventType: event ? event.type : 'fullscreenchange'
            });
        }

        if (!isActive) {
            fullscreenState.manualExitRequested = false;
        }

        fullscreenState.wasActive = isActive;
        updateFullscreenButton();
    }

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F11') {
            e.preventDefault();
            toggleFullscreen();
        } else if (e.key === 'Escape') {
            // Exit fullscreen on Escape (browser default)
            if (getFullscreenElement()) {
                fullscreenState.restoreEnabled = false;
                fullscreenState.manualExitRequested = true;
            }
        } else if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            if (getFullscreenElement()) {
                e.preventDefault(); // Prevent dev tools in fullscreen mode
            }
        }
    });
    
    // Prevent right-click in fullscreen mode
    document.addEventListener('contextmenu', e => {
        if (getFullscreenElement()) {
            e.preventDefault();
        }
    });
    
    // Show controls on mouse movement in fullscreen
    let mouseTimer;
    document.addEventListener('mousemove', function() {
        if (getFullscreenElement()) {
            document.body.classList.add('show-controls');
            
            clearTimeout(mouseTimer);
            mouseTimer = setTimeout(() => {
                document.body.classList.remove('show-controls');
            }, 3000);
        }
    });

    // Media/Live Update System
    const defaultSlowPollMs = 3000;
    const displayPollingConfig = window.displayPollingConfig || {};
    const POLL_INTERVALS = {
        media: Math.max(1000, Number(displayPollingConfig.media) || 1000),
        timetable: Math.max(3000, Number(displayPollingConfig.timetable) || defaultSlowPollMs),
        announcements: Math.max(3000, Number(displayPollingConfig.announcements) || defaultSlowPollMs),
        slidingTexts: Math.max(3000, Number(displayPollingConfig.slidingTexts) || defaultSlowPollMs),
        screenConfig: Math.max(2000, Number(displayPollingConfig.screenConfig) || 2000)
    };
    let announcementSectionMaxHeight = (function() {
        const existingSection = document.getElementById('announcements-section');
        if (!existingSection) {
            return '';
        }

        if (existingSection.style.maxHeight) {
            return existingSection.style.maxHeight;
        }

        const computed = window.getComputedStyle(existingSection).maxHeight;
        return computed && computed !== 'none' ? computed : '';
    })();

    let currentMedia = null;
    let currentMediaData = null;
    let currentMediaVersionToken = null;
    let mediaDisplayTimer = null;
    let countdownTimer = null;
    let currentScreenState = null;
    let currentPosterType = null;
    let lastAppliedScreenSignature = null;
    const lastReceivedVersions = {
        announcements: null,
        media: null,
        timetable: null,
        config: null,
        slidingTexts: null,
        state: null
    };
    const lastAppliedSectionSignatures = {
        timetable: null,
        announcements: null,
        slidingTexts: null,
        config: null
    };

    let mediaPollingTimer = null;
    let timetablePollingTimer = null;
    let announcementsPollingTimer = null;
    let slidingTextsPollingTimer = null;
    let screenConfigPollingTimer = null;
    let isMediaPollInFlight = false;
    let mediaPollSequence = 0;
    let pendingMediaPoll = false;
    let lastAppliedServerTimestampMs = 0;
    const countdownVerification = {
        sessionKey: null,
        instanceCount: 0,
        duplicateRenderSkips: 0,
        phase: null,
        countdownStart: null,
        countdownEnd: null,
        serverSecondsAtStart: null,
        ticks: [],
        driftAlerts: [],
        stateTransitions: [],
        pollSkipsSameSignature: 0,
    };
    window.__countdownVerification = countdownVerification;
    let isTimetablePollInFlight = false;
    let isAnnouncementsPollInFlight = false;
    let isSlidingTextsPollInFlight = false;
    let isScreenConfigPollInFlight = false;
    let screenConfigSyncQueued = false;
    let slidingTextsSyncQueued = false;

    let announcementRotationInterval = null;
    let announcementRotationIndex = 0;
    let announcementRotationStartedAt = 0;
    let lastAnnouncementRotationCount = 0;
    let announcementResizeHandler = null;
    let announcementLayoutPassId = 0;
    let uiLayoutVersion = 0;
    let announcementsDataCache = [];
    let lastSlidingTextsCache = [];
    let lastSlidingBoxStyling = @json(($boxSettings['sliding_text_box']['styling_settings'] ?? []));
    const announcementNodesByKey = new Map();
    const mediaDom = {
        adhanScreen: null,
        image: null,
        video: null,
        errorMessage: null
    };
    const fullscreenState = {
        userActivated: false,
        restoreEnabled: false,
        manualExitRequested: false,
        restoreTimer: null,
        wasActive: false
    };
    const dashboardRuntime = {
        lastUpdateSource: 'initial-render',
        lastRenderCause: 'initial-render'
    };

    async function fetchJson(url) {
        const separator = url.includes('?') ? '&' : '?';
        const response = await fetch(`${url}${separator}_=${Date.now()}`, {
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });

        if (!response.ok) {
            throw new Error(`Request failed for ${url}: ${response.status}`);
        }

        return response.json();
    }

    const DISPLAY_SYNC_STORAGE_KEY = 'timetable-display-sync';
    let displaySyncChannel = null;

    function notifyDisplayClientsChanged() {
        try {
            localStorage.setItem(DISPLAY_SYNC_STORAGE_KEY, String(Date.now()));
        } catch (error) {
            // Ignore storage failures in restricted contexts.
        }

        try {
            if (!displaySyncChannel) {
                displaySyncChannel = new BroadcastChannel('timetable-display');
            }
            displaySyncChannel.postMessage({ type: 'sync', at: Date.now() });
        } catch (error) {
            // BroadcastChannel may be unavailable in older browsers.
        }
    }

    function requestDisplaySyncAll() {
        requestScreenConfigSync();
        requestSlidingTextsSync();
        requestAnnouncementsSync();
        requestTimetableSync();
    }

    function initDisplaySyncListeners() {
        window.addEventListener('storage', (event) => {
            if (event.key === DISPLAY_SYNC_STORAGE_KEY) {
                requestDisplaySyncAll();
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                requestDisplaySyncAll();
            }
        });

        window.addEventListener('focus', () => {
            requestDisplaySyncAll();
        });

        try {
            if (!displaySyncChannel) {
                displaySyncChannel = new BroadcastChannel('timetable-display');
            }
            displaySyncChannel.onmessage = () => {
                requestDisplaySyncAll();
            };
        } catch (error) {
            // Ignore unsupported browsers.
        }
    }

    function debugLog(scope, message, details = {}) {
        console.log(`[tv-dashboard][${scope}] ${message}`, details);
    }

    function logCountdownDebug(event, details = {}) {
        const entry = {
            at: new Date().toISOString(),
            event,
            ...details,
        };
        debugLog('countdown', event, details);
        if (event === 'tick') {
            if (countdownVerification.ticks.length > 120) {
                countdownVerification.ticks.shift();
            }
            countdownVerification.ticks.push(entry);
        } else if (event === 'drift') {
            countdownVerification.driftAlerts.push(entry);
        }
    }

    function getCountdownSessionKey(countdown) {
        if (!countdown) {
            return null;
        }
        const end = countdown.countdown_end || countdown.prayer_time || null;
        const phase = countdown.phase || 'iqamah';
        return end ? `${phase}|${end}` : null;
    }

    function recordCountdownStateTransition(fromState, toState, details = {}) {
        const entry = {
            at: new Date().toISOString(),
            from: fromState,
            to: toState,
            ...details,
        };
        countdownVerification.stateTransitions.push(entry);
        logCountdownDebug('state-transition', entry);
    }

    function markRenderCause(cause, details = {}) {
        dashboardRuntime.lastRenderCause = cause;
        debugLog('render', cause, details);
    }

    function markUpdateSource(source, details = {}) {
        dashboardRuntime.lastUpdateSource = source;
        debugLog('update', source, details);
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
        return state === 'PRAYER_POSTER' || state === 'FULLTIME_POSTER' || state === 'MEDIA';
    }

    function normalizeScreenStateValue(state) {
        if (isPosterState(state)) {
            return 'MEDIA';
        }

        return state || 'TIMETABLE';
    }

    function computeScreenStateSignature(screenStateData) {
        if (!screenStateData || typeof screenStateData !== 'object') {
            return 'TIMETABLE|empty';
        }

        const normalizedState = normalizeScreenStateValue(screenStateData.state);
        const media = screenStateData.media || {};
        const countdown = screenStateData.countdown || {};

        return JSON.stringify({
            state: normalizedState,
            versions: getPayloadVersionBundle(screenStateData),
            posterType: screenStateData.state || null,
            mediaId: media.id || null,
            mediaUrl: media.file_url || null,
            mediaDuration: media.display_duration || null,
            prayerName: countdown.prayer_name || null,
            countdownPhase: countdown.phase || null,
            countdownEnd: countdown.countdown_end || countdown.prayer_time || null
        });
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
            title: announcement && announcement.title ? String(announcement.title).trim() : '',
            content: announcement && announcement.content
                ? String(announcement.content).replace(/\r\n/g, '\n').replace(/\r/g, '\n')
                : '',
            display_duration: toPositiveInteger(announcement && announcement.display_duration ? announcement.display_duration : null, 10),
            display_order: toPositiveInteger(announcement && announcement.display_order ? announcement.display_order : null, 1),
            title_font_size: normalizeAnnouncementRemFontSize(announcement && announcement.title_font_size ? announcement.title_font_size : null, '2.25rem'),
            font_size: normalizeAnnouncementRemFontSize(announcement && announcement.font_size ? announcement.font_size : null, '1.5rem'),
            text_color: announcement && announcement.text_color ? announcement.text_color : '#000000',
            background_color: announcement && announcement.background_color ? announcement.background_color : '#ffffff',
            scroll_speed: toPositiveInteger(announcement && announcement.scroll_speed ? announcement.scroll_speed : null, 3)
        };
    }

    function sortAnnouncementsByDisplayOrder(announcements) {
        return (announcements || []).slice().sort(function(a, b) {
            const orderA = Number(a && a.display_order ? a.display_order : 0);
            const orderB = Number(b && b.display_order ? b.display_order : 0);
            if (orderA !== orderB) {
                return orderA - orderB;
            }
            return Number(a && a.id ? a.id : 0) - Number(b && b.id ? b.id : 0);
        });
    }

    function getPayloadVersionBundle(payload) {
        return {
            announcements: payload && payload.announcements_version ? payload.announcements_version : null,
            media: payload && payload.media_version ? payload.media_version : null,
            timetable: payload && payload.timetable_version ? payload.timetable_version : null,
            config: payload && payload.config_version ? payload.config_version : null,
            slidingTexts: payload && payload.sliding_texts_version ? payload.sliding_texts_version : null,
            state: payload && payload.state_version ? payload.state_version : null
        };
    }

    function updateReceivedVersions(payload) {
        const bundle = getPayloadVersionBundle(payload);
        Object.entries(bundle).forEach(([key, value]) => {
            if (value) {
                lastReceivedVersions[key] = value;
            }
        });
    }

    function detectVersionChanges(payload) {
        const bundle = getPayloadVersionBundle(payload);
        const changed = [];

        Object.entries(bundle).forEach(([key, value]) => {
            if (!value) {
                return;
            }

            if (lastReceivedVersions[key] !== value) {
                changed.push(key);
            }
        });

        return {
            changed,
            bundle
        };
    }

    function logPayloadReceipt(section, payload) {
        debugLog('payload', `Received ${section} payload`, {
            section,
            versions: getPayloadVersionBundle(payload),
            payload
        });
    }

    function logDetectedChanges(section, changed, details = {}) {
        if (!changed || changed.length === 0) {
            return;
        }

        debugLog('diff', `Detected changes for ${section}`, {
            section,
            changed,
            ...details
        });
    }

    function computeTimetableSignature(payload) {
        return JSON.stringify({
            version: payload && payload.timetable_version ? payload.timetable_version : null,
            today: payload && payload.today ? payload.today : null,
            tomorrow: payload && payload.tomorrow ? payload.tomorrow : null,
            islamic_date: payload && payload.islamic_date ? payload.islamic_date : null,
            jamaat_offsets: payload && payload.jamaat_offsets ? payload.jamaat_offsets : null
        });
    }

    function computeAnnouncementsSignature(payload) {
        return JSON.stringify({
            version: payload && payload.announcements_version ? payload.announcements_version : null,
            announcements: Array.isArray(payload && payload.announcements) ? payload.announcements : []
        });
    }

    function computeSlidingTextsSignature(payload) {
        return payload && payload.sliding_texts_version ? String(payload.sliding_texts_version) : '';
    }

    function computeConfigSignature(payload) {
        return payload && payload.config_version ? String(payload.config_version) : '';
    }

    function buildMediaAssetUrl(fileUrl, versionToken) {
        if (!fileUrl) {
            return '';
        }

        if (!versionToken) {
            return fileUrl;
        }

        try {
            const url = new URL(fileUrl, window.location.origin);
            url.searchParams.set('tvv', versionToken);

            if (/^https?:/i.test(fileUrl) || fileUrl.startsWith('//') || fileUrl.startsWith('/')) {
                return url.toString();
            }

            return `${url.pathname}${url.search}${url.hash}`;
        } catch (error) {
            const separator = fileUrl.includes('?') ? '&' : '?';
            return `${fileUrl}${separator}tvv=${encodeURIComponent(versionToken)}`;
        }
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

    function normalizeRemFontSize(value, defaultRem = '1.2rem') {
        if (value === null || typeof value === 'undefined' || value === '') {
            return defaultRem;
        }

        const trimmed = String(value).trim();
        if (!trimmed) {
            return defaultRem;
        }

        if (/rem$/i.test(trimmed)) {
            return trimmed;
        }

        if (/px$/i.test(trimmed)) {
            const numeric = parseFloat(trimmed);
            if (!Number.isFinite(numeric)) {
                return defaultRem;
            }

            const rem = Math.round((numeric / 16) * 1000) / 1000;
            return `${rem}rem`;
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            const rem = Math.round((parseFloat(trimmed) / 16) * 1000) / 1000;
            return `${rem}rem`;
        }

        return trimmed;
    }

    // Box admin inputs store bare numbers as rem (matches Blade: value + 'rem').
    function normalizeBladeRemFontSize(value, defaultRem = '1rem') {
        if (value === null || typeof value === 'undefined' || value === '') {
            return defaultRem;
        }

        const trimmed = String(value).trim();
        if (!trimmed) {
            return defaultRem;
        }

        if (/rem$/i.test(trimmed)) {
            return trimmed;
        }

        if (/px$/i.test(trimmed)) {
            const numeric = parseFloat(trimmed);
            if (!Number.isFinite(numeric)) {
                return defaultRem;
            }

            const rem = Math.round((numeric / 16) * 1000) / 1000;
            return `${rem}rem`;
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            return `${trimmed}rem`;
        }

        return trimmed;
    }

    // Announcement font sizes: legacy px integers (>10) or rem (<=10 / rem suffix).
    function normalizeAnnouncementRemFontSize(value, defaultRem = '1.5rem') {
        if (value === null || typeof value === 'undefined' || value === '') {
            return defaultRem;
        }

        const trimmed = String(value).trim();
        if (!trimmed) {
            return defaultRem;
        }

        if (/rem$/i.test(trimmed)) {
            return normalizeBladeRemFontSize(trimmed, defaultRem);
        }

        if (/px$/i.test(trimmed)) {
            return normalizeRemFontSize(trimmed, defaultRem);
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            const numeric = parseFloat(trimmed);
            if (!Number.isFinite(numeric)) {
                return defaultRem;
            }
            if (numeric > 10) {
                return normalizeRemFontSize(trimmed, defaultRem);
            }
            return normalizeBladeRemFontSize(trimmed, defaultRem);
        }

        return defaultRem;
    }

    function parsePercentValue(value) {
        if (value === null || typeof value === 'undefined' || value === '') {
            return null;
        }
        if (typeof value === 'number' && Number.isFinite(value)) {
            return value;
        }
        const match = String(value).trim().match(/^([0-9]+(?:\.[0-9]+)?)\s*%?$/);
        return match ? parseFloat(match[1]) : null;
    }

    function formatPercentValue(value) {
        const rounded = Math.round(value * 1000) / 1000;
        return `${String(rounded).replace(/\.0+$/, '').replace(/(\.\d*?)0+$/, '$1')}%`;
    }

    function normalizePrayerColumnWidths(widths, defaults = ['30%', '35%', '35%']) {
        const fallback = defaults.slice(0, 3);
        while (fallback.length < 3) {
            fallback.push('33.333%');
        }

        if (!Array.isArray(widths) || widths.length < 3) {
            return fallback;
        }

        const parsed = [0, 1, 2].map((index) => parsePercentValue(widths[index]));
        if (parsed.some((value) => value === null)) {
            return fallback;
        }

        const sum = parsed[0] + parsed[1] + parsed[2];
        if (sum >= 95 && sum <= 105) {
            if (Math.abs(sum - 100) > 0.05) {
                const scale = 100 / sum;
                return parsed.map((value) => formatPercentValue(value * scale));
            }
            return parsed.map((value) => formatPercentValue(value));
        }

        if (sum < 95) {
            const name = Math.min(Math.max(parsed[0], 20), 40);
            const remaining = Math.max(100 - name, 60);
            const timeShare = remaining / 2;
            return [formatPercentValue(name), formatPercentValue(timeShare), formatPercentValue(timeShare)];
        }

        const scale = 100 / sum;
        return parsed.map((value) => formatPercentValue(value * scale));
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

    const FLEX_BOX_TYPES = ['announcements_box', 'prayer_times_box'];

    function toggleBoxVisibility(boxType, isVisible) {
        document.querySelectorAll(`[data-box-root="${boxType}"]`).forEach((element) => {
            if (isVisible) {
                if (FLEX_BOX_TYPES.includes(boxType)) {
                    element.style.display = 'flex';
                    element.style.flexDirection = 'column';
                    element.style.height = '100%';
                    element.style.minHeight = '0';
                    element.style.marginTop = '0';
                    element.style.paddingTop = '0';
                    element.style.alignSelf = 'flex-start';
                    if (boxType === 'announcements_box') {
                        element.style.flex = '0 0 var(--board-announce-width, 45%)';
                        element.style.maxWidth = 'var(--board-announce-width, 45%)';
                        element.style.width = 'var(--board-announce-width, 45%)';
                    }
                    if (boxType === 'prayer_times_box') {
                        element.style.flex = '0 0 var(--board-prayer-width, 55%)';
                        element.style.maxWidth = 'var(--board-prayer-width, 55%)';
                        element.style.width = 'var(--board-prayer-width, 55%)';
                    }
                } else {
                    element.style.display = '';
                }
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
        root.style.setProperty('--prayer-time-font-size', normalizeRemFontSize(themeVariables.prayer_time_font_size, '1.5rem'));
        root.style.setProperty('--announcement-scroll-speed', String(themeVariables.announcement_scroll_speed || '3'));

        if (displayFontFamily) {
            document.body.style.fontFamily = displayFontFamily;
        }
        const pageBackground = displayBackgroundColor || '#ffffff';
        document.body.style.backgroundColor = pageBackground;
        syncBoardHeaderContentGap();
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

    const DISPLAY_MODE_KEY = 'announcementDisplayMode';
    const MAIN_COLUMN_BOX_TYPES = ['prayer_times_box', 'announcements_box'];
    const BOTTOM_BOX_TYPES = ['special_times_box', 'welcome_box', 'sliding_text_box'];

    function getAnnouncementDisplayMode() {
        const storedMode = localStorage.getItem(DISPLAY_MODE_KEY);
        if (storedMode === 'show-all' || storedMode === 'rotation') {
            return storedMode;
        }

        const contentContainer = document.getElementById('announcements-content');
        const domMode = contentContainer ? contentContainer.dataset.displayMode : null;
        return domMode === 'show-all' ? 'show-all' : 'rotation';
    }

    function clearAnnouncementsLayoutInlineStyles() {
        const section = document.getElementById('announcements-section');
        const contentContainer = document.getElementById('announcements-content');
        const column = document.querySelector('[data-box-root="announcements_box"]');

        [column, section, contentContainer].forEach((node) => {
            if (!node) {
                return;
            }

            node.style.removeProperty('display');
            node.style.removeProperty('flex');
            node.style.removeProperty('flex-direction');
            node.style.removeProperty('flex-shrink');
            node.style.removeProperty('height');
            node.style.removeProperty('max-height');
            node.style.removeProperty('min-height');
            node.style.removeProperty('overflow');
            node.style.removeProperty('overflow-y');
        });

        if (!contentContainer) {
            return;
        }

        contentContainer.querySelectorAll('.announcement-item, .rotating-announcement').forEach((item) => {
            item.style.removeProperty('flex');
            item.style.removeProperty('flex-direction');
            item.style.removeProperty('flex-shrink');
            item.style.removeProperty('height');
            item.style.removeProperty('max-height');
            item.style.removeProperty('min-height');
        });

        contentContainer.querySelectorAll('.announcement-text-container').forEach((node) => {
            node.style.removeProperty('flex');
            node.style.removeProperty('display');
            node.style.removeProperty('min-height');
            node.style.removeProperty('height');
        });
    }

    function extractAnnouncementsDataFromDom() {
        const nodes = document.querySelectorAll('#announcements-content .rotating-announcement');
        if (nodes.length === 0) {
            return [];
        }

        return Array.from(nodes).map((node) => {
            const titleEl = node.querySelector('.announcement-title');
            const scrollEl = node.querySelector('.announcement-text-scroll');
            return normalizeAnnouncement({
                id: node.dataset.announcementId || null,
                title: titleEl ? titleEl.textContent : '',
                content: scrollEl
                    ? (scrollEl.dataset.rawContent || scrollEl.innerText || scrollEl.textContent || '')
                    : '',
                display_duration: toPositiveInteger(
                    node.dataset.displayDurationSeconds
                        ? node.dataset.displayDurationSeconds
                        : ((parseInt(node.dataset.duration, 10) || 10000) / 1000),
                    10
                ),
                display_order: toPositiveInteger(
                    node.dataset.displayOrder || ((Number(node.dataset.index) || 0) + 1),
                    1
                ),
                scroll_speed: scrollEl ? Number(scrollEl.getAttribute('data-scroll-speed')) || 3 : 3,
                background_color: node.style.backgroundColor || undefined,
                text_color: titleEl ? titleEl.style.color : undefined,
                font_size: scrollEl ? (scrollEl.style.fontSize || undefined) : undefined,
                title_font_size: titleEl ? (titleEl.style.fontSize || undefined) : undefined
            });
        });
    }

    function shouldRestartAnnouncementRotation(reason = '') {
        const restartReasons = new Set([
            'dom-ready-immediate',
            'dom-ready',
            'render-announcements-empty',
            'render-announcements-replaced',
            'display-mode-storage',
        ]);

        return restartReasons.has(reason);
    }

    function applyAnnouncementsPresentation(reason = '') {
        uiLayoutVersion += 1;
        const contentContainer = document.getElementById('announcements-content');
        if (contentContainer) {
            contentContainer.dataset.layoutVersion = String(uiLayoutVersion);
        }

        const mode = getAnnouncementDisplayMode();
        const announcementCount = contentContainer
            ? contentContainer.querySelectorAll('.rotating-announcement').length
            : 0;
        const modeChanged = !!contentContainer && contentContainer.dataset.displayMode !== mode;
        const countChanged = announcementCount !== lastAnnouncementRotationCount;
        lastAnnouncementRotationCount = announcementCount;
        const restartRotation = shouldRestartAnnouncementRotation(reason) || modeChanged || countChanged;

        clearAnnouncementsLayoutInlineStyles();
        setAnnouncementDisplayMode(mode, {
            force: modeChanged || countChanged,
            restartRotation,
        });
        scheduleAnnouncementLayoutSync(reason);
    }

    function ensureBoardMainContentLayout() {
        const mainContent = document.querySelector('.board-main-content');
        const row = document.querySelector('.board-main-content .row');

        if (mainContent) {
            mainContent.style.minHeight = '0';
            mainContent.style.height = '100%';
            mainContent.style.overflow = 'hidden';
        }

        if (row) {
            row.style.display = 'flex';
            row.style.flexDirection = 'row';
            row.style.alignItems = 'flex-start';
            row.style.height = '100%';
            row.style.minHeight = '0';
            row.style.width = '100%';
            row.style.margin = '0';
            row.style.padding = '0';
            row.style.gap = '0';
        }

        if (mainContent) {
            mainContent.style.display = 'flex';
            mainContent.style.flexDirection = 'column';
        }
    }

    function resolveBoardPageGapColor() {
        return getComputedStyle(document.body).backgroundColor || '#ffffff';
    }

    function syncBoardHeaderContentGap() {
        const container = document.querySelector('.unified-container');
        const header = document.getElementById('header-box');
        const mainContent = document.querySelector('.board-main-content');
        if (!container || !mainContent) {
            return;
        }

        const spacers = Array.from(container.querySelectorAll(':scope > .board-header-content-gap'));
        let spacer = spacers[0] || null;
        spacers.slice(1).forEach((duplicate) => duplicate.remove());

        if (!spacer) {
            spacer = document.createElement('div');
            spacer.className = 'board-header-content-gap';
            spacer.setAttribute('aria-hidden', 'true');
            if (header && header.parentElement === container) {
                container.insertBefore(spacer, header.nextElementSibling);
            } else {
                container.insertBefore(spacer, mainContent);
            }
        }

        spacer.style.backgroundColor = resolveBoardPageGapColor();
    }

    function syncBoardColumnLayout(boxSettings = null) {
        restoreFixedBoardLayout();

        const configPadding = boxSettings
            ? resolveBoardColumnPadding(boxSettings)
            : getComputedStyle(document.querySelector('.board-main-content') || document.body).getPropertyValue('--board-column-padding').trim();

        applyBoardColumnPadding(configPadding || '15px');
        ensureBoardMainContentLayout();

        ['prayer-times-box', 'announcements_box'].forEach((key) => {
            const wrapper = key === 'prayer-times-box'
                ? document.getElementById('prayer-times-box')
                : document.querySelector('[data-box-root="announcements_box"]');
            if (!wrapper) {
                return;
            }
            wrapper.style.marginTop = '0';
            wrapper.style.paddingTop = '0';
            wrapper.style.alignSelf = 'flex-start';
        });

        ['prayer-times-section', 'announcements-section'].forEach((sectionId) => {
            const section = document.getElementById(sectionId);
            if (!section) {
                return;
            }
            section.style.marginTop = '0';
            section.style.paddingTop = '0';
            section.style.paddingLeft = '';
            section.style.paddingRight = '';
            section.style.paddingBottom = '';
            section.style.borderTopWidth = '0';
        });

        syncBoardColumnHeaders();
        syncBoardHeaderContentGap();
    }

    function resolveBoardColumnPadding(boxSettings) {
        const prayerPadding = boxSettings?.prayer_times_box?.styling_settings?.padding;
        const announcementsPadding = boxSettings?.announcements_box?.styling_settings?.padding;
        const value = prayerPadding ?? announcementsPadding ?? '15';
        return normalizeCssValue(value, 'px') || '15px';
    }

    function applyBoardColumnPadding(paddingValue) {
        const mainContent = document.querySelector('.board-main-content');
        if (!mainContent) {
            return;
        }

        const padding = normalizeCssValue(paddingValue, 'px') || '15px';
        mainContent.style.setProperty('--board-column-padding', padding);

        ['prayer-times-section', 'announcements-section'].forEach((sectionId) => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.style.paddingTop = '0';
                section.style.paddingLeft = '';
                section.style.paddingRight = '';
                section.style.paddingBottom = '';
            }
        });
    }

    function syncBoardColumnHeaders() {
        const prayerHeader = document.querySelector('.prayer-header');
        const annHeader = document.querySelector('.announcements-header');
        const boardMain = document.querySelector('.board-main-content');

        if (!prayerHeader || !annHeader) {
            return;
        }

        const sharedHeaderStyles = {
            margin: '0 0 10px 0',
            padding: '8px',
            paddingBottom: '10px',
            boxSizing: 'border-box',
        };

        Object.assign(prayerHeader.style, sharedHeaderStyles);
        Object.assign(annHeader.style, sharedHeaderStyles);

        prayerHeader.style.minHeight = '';
        annHeader.style.minHeight = '';
        if (boardMain) {
            boardMain.style.removeProperty('--board-header-row-height');
        }

        requestAnimationFrame(() => {
            const maxHeight = Math.max(prayerHeader.offsetHeight, annHeader.offsetHeight);
            if (!maxHeight) {
                return;
            }

            const heightPx = `${maxHeight}px`;
            prayerHeader.style.minHeight = heightPx;
            annHeader.style.minHeight = heightPx;
            if (boardMain) {
                boardMain.style.setProperty('--board-header-row-height', heightPx);
            }
        });
    }

    function resetAllAnnouncementScrollAnimations() {
        document.querySelectorAll('#announcements-content .announcement-text-scroll').forEach((scrollDiv) => {
            resetAnnouncementScrollState(scrollDiv);
        });
    }

    function restoreFixedBoardLayout() {
        const container = document.querySelector('.unified-container');
        const header = document.getElementById('header-box');
        const spacer = container ? container.querySelector(':scope > .board-header-content-gap') : null;
        const mainContent = document.querySelector('.board-main-content');

        if (!container || !mainContent) {
            return;
        }

        if (header && header.parentElement === container && container.firstElementChild !== header) {
            container.insertBefore(header, container.firstElementChild);
        }

        if (header && spacer && header.parentElement === container && header.nextElementSibling !== spacer) {
            container.insertBefore(spacer, header.nextElementSibling);
        }

        if (spacer && spacer.nextElementSibling !== mainContent) {
            container.insertBefore(mainContent, spacer.nextElementSibling);
        } else if (!spacer) {
            const anchor = header && header.parentElement === container ? header : null;
            if (anchor && anchor.nextElementSibling !== mainContent) {
                container.insertBefore(mainContent, anchor.nextElementSibling);
            } else if (!anchor && container.firstElementChild !== mainContent) {
                container.insertBefore(mainContent, container.firstElementChild);
            }
        }

        let insertAfter = mainContent;
        BOTTOM_BOX_TYPES.forEach((boxType) => {
            const box = document.querySelector(`[data-box-root="${boxType}"]`);
            if (!box || box.parentElement !== container) {
                return;
            }

            if (insertAfter.nextElementSibling !== box) {
                container.insertBefore(box, insertAfter.nextElementSibling);
            }

            insertAfter = box;
        });
    }

    function reorderBoxChildren(parent, orderMap, allowedBoxTypes) {
        if (!parent) {
            return;
        }

        const children = Array.from(parent.children).filter((child) => {
            if (!child.hasAttribute('data-box-root')) {
                return false;
            }

            const boxType = child.getAttribute('data-box-root');
            return !allowedBoxTypes || allowedBoxTypes.includes(boxType);
        });

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
    }

    function applyBoxOrdering(boxSettings, boxOrder = []) {
        const orderMap = buildOrderMap(boxSettings, boxOrder);

        reorderBoxChildren(
            document.querySelector('.board-main-content .row'),
            orderMap,
            MAIN_COLUMN_BOX_TYPES
        );

        restoreFixedBoardLayout();
    }

    function applyNextPrayerCountdownSettings(styling, layout) {
        const prayerBody = document.querySelector('#prayer-times-section .prayer-times-body');
        if (!prayerBody) {
            return;
        }

        const position = layout && layout.next_prayer_position ? layout.next_prayer_position : 'below_table';
        const prayerList = prayerBody.querySelector('.prayer-list');
        let infoBlock = prayerBody.querySelector('.next-prayer-info');

        if (position === 'hidden') {
            if (infoBlock) {
                infoBlock.style.display = 'none';
            }
            return;
        }

        if (!infoBlock) {
            infoBlock = document.createElement('div');
            infoBlock.className = 'next-prayer-info';
            infoBlock.innerHTML = `
                <div class="next-prayer-text">Next prayer in:</div>
                <div id="next-prayer-countdown" class="next-prayer-countdown">--:--:--</div>
                <div id="next-prayer-name" class="next-prayer-name">Calculating...</div>
            `;
            prayerBody.appendChild(infoBlock);
        }

        infoBlock.style.display = '';
        infoBlock.style.textAlign = 'center';
        infoBlock.style.marginTop = position === 'below_table' ? '15px' : '0';
        infoBlock.style.marginBottom = position === 'above_table' ? '15px' : '0';

        if (prayerList) {
            if (position === 'above_table') {
                prayerBody.insertBefore(infoBlock, prayerList);
            } else {
                prayerList.insertAdjacentElement('afterend', infoBlock);
            }
        }

        const label = infoBlock.querySelector('.next-prayer-text');
        const countdown = infoBlock.querySelector('#next-prayer-countdown')
            || infoBlock.querySelector('.next-prayer-countdown');
        const prayerName = infoBlock.querySelector('#next-prayer-name')
            || infoBlock.querySelector('.next-prayer-name');

        if (label) {
            label.style.setProperty('margin-bottom', '8px', 'important');
            label.style.setProperty('font-weight', 'bold', 'important');
            label.style.setProperty(
                'font-size',
                normalizeBladeRemFontSize(styling.next_prayer_font_size, '1.4rem'),
                'important'
            );
            label.style.setProperty('color', styling.next_prayer_text_color || '#000000', 'important');
        }

        if (countdown) {
            countdown.style.setProperty(
                'font-size',
                normalizeBladeRemFontSize(styling.next_prayer_countdown_font_size, '1.4rem'),
                'important'
            );
            countdown.style.setProperty('font-weight', 'bold', 'important');
            countdown.style.setProperty('color', styling.next_prayer_countdown_color || '#000000', 'important');
        }

        if (prayerName) {
            prayerName.style.setProperty(
                'font-size',
                normalizeBladeRemFontSize(styling.next_prayer_name_font_size, '0.9rem'),
                'important'
            );
            prayerName.style.setProperty('margin-top', '5px', 'important');
            prayerName.style.setProperty('opacity', '0.8', 'important');
            prayerName.style.setProperty('color', styling.next_prayer_name_color || '#666666', 'important');
        }

        updateNextPrayerCountdown();
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
                document.querySelectorAll('#current-time').forEach((node) => {
                    node.style.fontSize = normalizeBladeRemFontSize(styling.time_font_size, '3rem');
                });
                document.querySelectorAll('#gregorian-date, #islamic-date').forEach((node) => {
                    node.style.fontSize = normalizeBladeRemFontSize(styling.date_font_size, '2.75rem');
                });
            }

            if (boxType === 'prayer_times_box') {
                const prayerSection = document.getElementById('prayer-times-section');
                const prayerBox = document.getElementById('prayer-times-box');
                const prayerHeader = prayerSection ? prayerSection.querySelector('.prayer-header') : null;
                setStyleValue(prayerSection, 'background-color', styling.background_color);
                setStyleValue(prayerSection, 'color', styling.text_color);
                setStyleValue(prayerSection, 'font-family', styling.font_family);
                if (prayerSection && styling.font_size) {
                    prayerSection.style.fontSize = normalizeBladeRemFontSize(styling.font_size, '3.5rem');
                }
                if (prayerSection) {
                    prayerSection.querySelectorAll('.prayer-name').forEach((node) => {
                        node.style.fontSize = normalizeBladeRemFontSize(styling.prayer_names_font_size, '4rem');
                    });
                    prayerSection.querySelectorAll('[data-time-type="beginning"]').forEach((node) => {
                        node.style.fontSize = normalizeBladeRemFontSize(styling.beginning_font_size, '3.5rem');
                    });
                    prayerSection.querySelectorAll('[data-time-type="jamaat"]').forEach((node) => {
                        node.style.fontSize = normalizeBladeRemFontSize(styling.jamaat_font_size, '3.5rem');
                    });
                }
                if (prayerSection && styling.border_width && styling.border_color) {
                    prayerSection.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }

                if (prayerHeader) {
                    setStyleValue(prayerHeader, 'background-color', styling.header_background_color);
                    setStyleValue(prayerHeader, 'color', styling.header_text_color);
                    if (styling.header_font_size) {
                        prayerHeader.style.fontSize = normalizeBladeRemFontSize(styling.header_font_size, '1.5rem');
                    }
                }

                if (Array.isArray(layout.column_widths) && layout.column_widths.length >= 3) {
                    const normalizedWidths = normalizePrayerColumnWidths(layout.column_widths);
                    const templateColumns = `${normalizedWidths[0]} ${normalizedWidths[1]} ${normalizedWidths[2]}`;
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

                applyNextPrayerCountdownSettings(styling, layout);
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
                if (section) {
                    const headerBg = styling.title_background_color || '#1E4D2B';
                    const headerText = styling.title_color || '#ffffff';
                    section.style.setProperty('--announcements-header-bg', headerBg);
                    section.style.setProperty('--announcements-header-text', headerText);
                }
                if (header) {
                    const headerBg = styling.title_background_color || '#1E4D2B';
                    const headerText = styling.title_color || '#ffffff';
                    header.style.backgroundColor = headerBg;
                    header.style.color = headerText;
                    header.style.fontSize = normalizeBladeRemFontSize(styling.title_font_size, '1.6rem');
                    header.style.margin = '0 0 10px 0';
                    header.style.padding = '8px';
                    header.style.paddingBottom = '10px';
                    header.style.textAlign = 'center';
                    header.style.fontWeight = 'bold';
                    header.style.display = 'flex';
                    header.style.alignItems = 'center';
                    header.style.justifyContent = 'center';
                    header.style.boxSizing = 'border-box';
                }
                if (header && content.title) {
                    header.textContent = content.title;
                }

                if (typeof layout.max_height !== 'undefined') {
                    announcementSectionMaxHeight = normalizeCssValue(layout.max_height, 'px') || announcementSectionMaxHeight;
                    setStyleValue(section, 'max-height', announcementSectionMaxHeight);
                } else {
                    announcementSectionMaxHeight = '';
                    if (section) {
                        section.style.maxHeight = 'none';
                    }
                }
            }

            if (boxType === 'special_times_box') {
                const section = document.querySelector('[data-box-root="special_times_box"]');
                setStyleValue(section, 'background-color', styling.background_color);
                setStyleValue(section, 'color', styling.text_color);
                setStyleValue(section, 'font-family', styling.font_family);
                if (section && styling.font_size) {
                    section.style.fontSize = normalizeBladeRemFontSize(styling.font_size, '4rem');
                    section.querySelectorAll('.time-value').forEach((node) => {
                        node.style.fontSize = normalizeBladeRemFontSize(styling.font_size, '4rem');
                    });
                }
                if (section && styling.header_font_size) {
                    section.querySelectorAll('.time-label').forEach((node) => {
                        node.style.fontSize = normalizeBladeRemFontSize(styling.header_font_size, '4rem');
                    });
                }
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
                if (section && styling.font_size) {
                    section.style.setProperty('font-size', normalizeBladeRemFontSize(styling.font_size, '5rem'), 'important');
                }
                setStyleValue(section, 'text-align', layout.text_alignment);
                if (section && styling.border_width && styling.border_color) {
                    section.style.border = `${normalizeCssValue(styling.border_width)} solid ${styling.border_color}`;
                }
                setStyleValue(section, 'padding', styling.padding, 'px');
                lastSlidingBoxStyling = styling || {};
                renderSlidingTexts(lastSlidingTextsCache, lastSlidingBoxStyling);
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

        syncBoardColumnLayout(boxSettings);
    }

    function applyScreenConfig(configPayload) {
        if (!configPayload || typeof configPayload !== 'object') {
            return;
        }

        markUpdateSource('screen-config-sync', {
            configVersion: configPayload.config_version || null
        });
        applyThemeVariables(configPayload.theme_variables || {});
        applyBoxConfig(configPayload.box_settings || {});
        applyBoxOrdering(configPayload.box_settings || {}, configPayload.box_order || []);
        if (configPayload.box_settings && configPayload.box_settings.sliding_text_box) {
            lastSlidingBoxStyling = configPayload.box_settings.sliding_text_box.styling_settings || lastSlidingBoxStyling;
        }
        applySlidingTextsPayload(configPayload, lastSlidingBoxStyling);
        restoreFixedBoardLayout();
        applyAnnouncementsPresentation('applyScreenConfig');
        syncBoardColumnLayout(configPayload?.box_settings || {});
    }

    async function seedInitialPollSignatures() {
        try {
            const [announcementsRes, slidingTextsRes, configRes, timetableRes] = await Promise.all([
                fetchJson('/api/announcements'),
                fetchJson('/api/sliding-texts'),
                fetchJson('/api/screen-config'),
                fetchJson('/api/timetable')
            ]);

            lastAppliedSectionSignatures.announcements = computeAnnouncementsSignature(announcementsRes);
            lastAppliedSectionSignatures.slidingTexts = computeSlidingTextsSignature(slidingTextsRes);
            lastAppliedSectionSignatures.config = computeConfigSignature(configRes);
            lastAppliedSectionSignatures.timetable = computeTimetableSignature(timetableRes);
            updateReceivedVersions(announcementsRes);
            updateReceivedVersions(slidingTextsRes);
            updateReceivedVersions(configRes);
            updateReceivedVersions(timetableRes);

            if (Array.isArray(announcementsRes && announcementsRes.announcements)) {
                announcementsDataCache = sortAnnouncementsByDisplayOrder(
                    announcementsRes.announcements.map(normalizeAnnouncement)
                );
            }

            if (Array.isArray(slidingTextsRes && slidingTextsRes.sliding_texts)) {
                lastSlidingTextsCache = slidingTextsRes.sliding_texts;
            }
        } catch (error) {
            console.error('Failed to seed initial poll signatures:', error);
        }
    }

    function ensureMediaDom() {
        const mediaContent = document.getElementById('media-content');
        if (!mediaContent) {
            return null;
        }

        if (!mediaDom.adhanScreen) {
            const adhanScreen = document.createElement('div');
            adhanScreen.style.display = 'none';
            adhanScreen.style.flexDirection = 'column';
            adhanScreen.style.alignItems = 'center';
            adhanScreen.style.justifyContent = 'center';
            adhanScreen.style.height = '100vh';
            adhanScreen.style.background = '#000';
            adhanScreen.style.color = '#fff';

            const adhanTitle = document.createElement('div');
            adhanTitle.textContent = 'ADHAN';
            adhanTitle.style.fontSize = '5rem';
            adhanTitle.style.fontWeight = 'bold';
            adhanTitle.style.letterSpacing = '0.2rem';

            const adhanSubtitle = document.createElement('div');
            adhanSubtitle.textContent = 'Prayer time has started';
            adhanSubtitle.style.fontSize = '1.6rem';
            adhanSubtitle.style.marginTop = '1rem';

            adhanScreen.appendChild(adhanTitle);
            adhanScreen.appendChild(adhanSubtitle);
            mediaContent.appendChild(adhanScreen);
            mediaDom.adhanScreen = adhanScreen;
        }

        if (!mediaDom.image) {
            const image = document.createElement('img');
            image.style.cssText = 'display: none; width: 100%; height: 100%; object-fit: contain; position: relative; z-index: 1;';
            image.alt = '';
            mediaContent.appendChild(image);
            mediaDom.image = image;
        }

        if (!mediaDom.video) {
            const video = document.createElement('video');
            video.style.cssText = 'display: none; width: 100%; height: 100%; object-fit: contain;';
            video.autoplay = true;
            video.loop = true;
            video.muted = true;
            mediaContent.appendChild(video);
            mediaDom.video = video;
        }

        if (!mediaDom.errorMessage) {
            const errorMessage = document.createElement('div');
            errorMessage.style.display = 'none';
            errorMessage.style.color = 'white';
            errorMessage.style.textAlign = 'center';
            errorMessage.style.padding = '20px';
            mediaContent.appendChild(errorMessage);
            mediaDom.errorMessage = errorMessage;
        }

        return mediaDom;
    }

    function hideMediaNodes() {
        ensureMediaDom();
        if (mediaDom.adhanScreen) {
            mediaDom.adhanScreen.style.display = 'none';
        }
        if (mediaDom.image) {
            mediaDom.image.style.display = 'none';
            mediaDom.image.removeAttribute('src');
            mediaDom.image.alt = '';
        }
        if (mediaDom.video) {
            mediaDom.video.pause();
            mediaDom.video.style.display = 'none';
            mediaDom.video.removeAttribute('src');
            mediaDom.video.load();
        }
        if (mediaDom.errorMessage) {
            mediaDom.errorMessage.style.display = 'none';
            mediaDom.errorMessage.textContent = '';
        }
    }

    function showMediaError(message) {
        ensureMediaDom();
        hideMediaNodes();
        if (mediaDom.errorMessage) {
            mediaDom.errorMessage.textContent = message;
            mediaDom.errorMessage.style.display = 'block';
        }
    }

    function ensureMediaPollingTimer() {
        if (mediaPollingTimer) {
            return;
        }

        mediaPollingTimer = setInterval(requestMediaSync, POLL_INTERVALS.media);
    }

    function hidePosterOverlay() {
        const overlay = document.getElementById('media-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }

        clearTimeout(mediaDisplayTimer);
        mediaDisplayTimer = null;
        hideMediaNodes();
    }

    function isPosterScreenState(state) {
        return normalizeScreenStateValue(state) === 'MEDIA';
    }

    function isCountdownPopupActive() {
        const popup = document.getElementById('countdown-popup');
        if (!popup) {
            return false;
        }

        const visible = popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex';
        if (!visible) {
            return false;
        }

        const timer = document.getElementById('countdown-popup-timer');
        const timerText = timer ? timer.textContent.trim() : '';
        return timerText !== '00';
    }

    function shouldRejectPosterDuringCountdown(screenStateData) {
        if (!screenStateData || !isPosterScreenState(screenStateData.state)) {
            return false;
        }

        // Countdown reached 00 — allow before/after prayer posters to resume.
        if (isCountdownStuckAtZero()) {
            return false;
        }

        return currentScreenState === 'COUNTDOWN' || isCountdownPopupActive();
    }

    function clearAllScreenContent() {
        const mediaOverlay = document.getElementById('media-overlay');
        const countdownPopup = document.getElementById('countdown-popup');
        const mediaCountdown = document.getElementById('media-countdown');

        if (mediaOverlay) {
            mediaOverlay.style.display = 'none';
        }

        if (countdownPopup) {
            countdownPopup.style.display = 'none';
        }

        hideMediaNodes();

        if (mediaCountdown) {
            mediaCountdown.style.display = 'none';
        }

        clearTimeout(mediaDisplayTimer);
        clearInterval(countdownTimer);

        mediaDisplayTimer = null;
        countdownTimer = null;
        currentMedia = null;
        currentMediaData = null;
        currentMediaVersionToken = null;
        countdownVerification.sessionKey = null;
    }

    function renderCountdownState(data) {
        hidePosterOverlay();

        const countdownPopup = document.getElementById('countdown-popup');
        const popupTitle = document.getElementById('countdown-popup-title');
        const popupPrayer = document.getElementById('countdown-popup-prayer');
        const popupTimer = document.getElementById('countdown-popup-timer');

        const countdown = data && data.countdown ? data.countdown : null;
        const countdownEnd = countdown
            ? (countdown.countdown_end || countdown.prayer_time || null)
            : null;

        if (!countdownPopup || !countdown || !countdownEnd) {
            renderTimetableState();
            return;
        }

        const sessionKey = getCountdownSessionKey(countdown);
        if (sessionKey && countdownVerification.sessionKey === sessionKey && countdownTimer) {
            countdownVerification.duplicateRenderSkips += 1;
            logCountdownDebug('skip-duplicate-render', {
                sessionKey,
                phase: countdown.phase,
                countdownEnd,
            });
            return;
        }

        const prayerName = countdown.prayer_name || 'Prayer';
        const phase = countdown.phase || 'iqamah';
        const message = countdown.message
            || (phase === 'adhan'
                ? 'Adhan will start in 30 seconds'
                : 'Iqamah will start in 30 seconds');

        countdownVerification.sessionKey = sessionKey;
        countdownVerification.instanceCount += 1;
        countdownVerification.phase = phase;
        countdownVerification.countdownStart = countdown.countdown_start || null;
        countdownVerification.countdownEnd = countdownEnd;
        countdownVerification.serverSecondsAtStart = toPositiveInteger(countdown.seconds_remaining, 30);

        logCountdownDebug('start', {
            sessionKey,
            phase,
            prayerName,
            message,
            countdownStart: countdownVerification.countdownStart,
            countdownEnd,
            serverSecondsRemaining: countdownVerification.serverSecondsAtStart,
            instanceCount: countdownVerification.instanceCount,
        });

        if (popupTitle) {
            popupTitle.textContent = message;
        }
        if (popupPrayer) {
            popupPrayer.textContent = prayerName;
        }
        if (popupTimer) {
            const initialSeconds = toPositiveInteger(countdown.seconds_remaining, 30);
            popupTimer.textContent = String(initialSeconds).padStart(2, '0');
        }

        countdownPopup.style.display = 'flex';
        countdownPopup.style.position = 'fixed';
        countdownPopup.style.top = '50%';
        countdownPopup.style.left = '50%';
        countdownPopup.style.transform = 'translate(-50%, -50%)';
        startCountdownTimer(countdownEnd, phase);
    }

    function renderAdhanState() {
        const overlay = document.getElementById('media-overlay');
        ensureMediaDom();
        if (!overlay) {
            return;
        }

        markRenderCause('render-adhan-state', {});
        hideMediaNodes();
        if (mediaDom.adhanScreen) {
            mediaDom.adhanScreen.style.display = 'flex';
        }
        overlay.style.display = 'flex';
    }

    function renderMediaState(media, posterType, mediaVersionToken = null) {
        const normalizedMedia = normalizeMediaPayload(media);
        const overlay = document.getElementById('media-overlay');
        ensureMediaDom();

        if (!normalizedMedia || !normalizedMedia.file_url || !overlay) {
            renderTimetableState();
            return;
        }

        const isSameMedia = currentMediaData
            && normalizedMedia
            && currentMediaData.id === normalizedMedia.id
            && currentMediaData.file_url === normalizedMedia.file_url
            && currentScreenState === 'MEDIA'
            && currentPosterType === posterType
            && currentMediaVersionToken === mediaVersionToken;

        if (isSameMedia) {
            overlay.style.display = 'flex';
            return;
        }

        clearInterval(countdownTimer);
        clearTimeout(mediaDisplayTimer);

        currentMedia = normalizedMedia;
        currentMediaData = normalizedMedia;
        currentMediaVersionToken = mediaVersionToken;

        hideMediaNodes();

        if (normalizedMedia.type === 'image') {
            if (!mediaDom.image) {
                return;
            }

            mediaDom.image.onerror = function() {
                showMediaError(`Failed to load image: ${normalizedMedia.title}`);
            };
            mediaDom.image.alt = normalizedMedia.title;
            mediaDom.image.src = buildMediaAssetUrl(normalizedMedia.file_url, mediaVersionToken);
            mediaDom.image.style.display = 'block';
        } else if (normalizedMedia.type === 'video') {
            if (!mediaDom.video) {
                return;
            }

            mediaDom.video.src = buildMediaAssetUrl(normalizedMedia.file_url, mediaVersionToken);
            mediaDom.video.style.display = 'block';
            mediaDom.video.load();
            mediaDom.video.play().catch((error) => {
                debugLog('media', 'Video autoplay failed', {
                    mediaId: normalizedMedia.id,
                    error: error && error.message ? error.message : error
                });
            });
        }

        if (normalizedMedia.type !== 'image' && normalizedMedia.type !== 'video') {
            renderTimetableState();
            return;
        }

        markRenderCause('render-media-state', {
            posterType,
            mediaId: normalizedMedia.id,
            mediaType: normalizedMedia.type
        });
        overlay.style.display = 'flex';

        const refreshDelayMs = Math.max(1000, toPositiveInteger(normalizedMedia.display_duration, 3) * 1000 + 250);
        mediaDisplayTimer = setTimeout(() => {
            markUpdateSource('media-duration-expired', {
                posterType,
                mediaId: normalizedMedia.id,
                refreshDelayMs
            });
            requestMediaSync();
            ensureMediaPollingTimer();
        }, refreshDelayMs);
    }

    function renderTimetableState() {
        const overlay = document.getElementById('media-overlay');
        const countdownPopup = document.getElementById('countdown-popup');

        if (overlay) {
            overlay.style.display = 'none';
        }

        if (countdownPopup) {
            countdownPopup.style.display = 'none';
        }

        clearTimeout(mediaDisplayTimer);
        clearInterval(countdownTimer);
        mediaDisplayTimer = null;
        countdownTimer = null;
        hideMediaNodes();
        markRenderCause('render-timetable-state', {});
        
        ensureMediaPollingTimer();
    }

    function startCountdownTimer(countdownEnd, phase) {
        const targetTime = new Date(countdownEnd).getTime();
        if (Number.isNaN(targetTime)) {
            return;
        }

        let hasRequestedSync = false;
        let hasScheduledFinalize = false;
        let lastLoggedSecond = null;
        const startedAtMs = Date.now();
        const expectedDurationSec = 30;

        function tick() {
            const now = Date.now();
            const distance = targetTime - now;
            const secondsRemaining = distance > 0
                ? Math.ceil(distance / 1000)
                : 0;

            const popupTimer = document.getElementById('countdown-popup-timer');
            if (popupTimer) {
                popupTimer.textContent = String(secondsRemaining).padStart(2, '0');
            }

            if (secondsRemaining !== lastLoggedSecond) {
                const elapsedSec = Math.round((now - startedAtMs) / 1000);
                const expectedRemaining = Math.max(0, expectedDurationSec - elapsedSec);
                const driftSec = Math.abs(secondsRemaining - expectedRemaining);

                if (lastLoggedSecond !== null && driftSec > 1) {
                    logCountdownDebug('drift', {
                        phase,
                        secondsRemaining,
                        expectedRemaining,
                        driftSec,
                        countdownEnd,
                    });
                }

                logCountdownDebug('tick', {
                    phase,
                    secondsRemaining,
                    countdownEnd,
                });
                lastLoggedSecond = secondsRemaining;
            }

            if (distance <= 0) {
                if (!hasRequestedSync) {
                    hasRequestedSync = true;
                    logCountdownDebug('reached-zero', { phase, countdownEnd });
                    setTimeout(() => requestMediaSync(), 150);
                }

                if (!hasScheduledFinalize) {
                    hasScheduledFinalize = true;
                    setTimeout(() => {
                        if (isCountdownStuckAtZero()) {
                            finalizeCountdownCompletion(null);
                        }
                    }, 750);
                }
            }
        }

        clearInterval(countdownTimer);
        tick();
        countdownTimer = setInterval(tick, 250);
    }

    function isCountdownStuckAtZero() {
        const popup = document.getElementById('countdown-popup');
        if (!popup) {
            return false;
        }

        const visible = popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex';
        if (!visible) {
            return false;
        }

        const timer = document.getElementById('countdown-popup-timer');
        return timer && timer.textContent.trim() === '00';
    }

    function shouldDeferCountdownClear(screenStateData) {
        if (!screenStateData || screenStateData.state !== 'TIMETABLE' || currentScreenState !== 'COUNTDOWN') {
            return false;
        }

        return !isCountdownStuckAtZero();
    }

    function finalizeCountdownCompletion(screenStateData) {
        logCountdownDebug('finalize-countdown-completion', {
            currentScreenState,
            serverState: screenStateData?.state || null,
            timer: document.getElementById('countdown-popup-timer')?.textContent?.trim() || null,
        });

        clearInterval(countdownTimer);
        countdownTimer = null;
        countdownVerification.sessionKey = null;

        const payload = (screenStateData && screenStateData.state && screenStateData.state !== 'COUNTDOWN')
            ? screenStateData
            : {
                state: 'TIMETABLE',
                timestamp: (screenStateData && screenStateData.timestamp) || new Date().toISOString(),
            };

        lastAppliedScreenSignature = computeScreenStateSignature(payload);
        const serverTimestampMs = Date.parse(payload.timestamp || '');
        if (!Number.isNaN(serverTimestampMs)) {
            lastAppliedServerTimestampMs = serverTimestampMs;
        }

        applyScreenState(payload);
        requestMediaSync();
    }

    function applyScreenState(screenStateData) {
        const rawState = (screenStateData && screenStateData.state) ? screenStateData.state : 'TIMETABLE';
        const nextState = normalizeScreenStateValue(rawState);
        const nextMedia = normalizeMediaPayload(screenStateData && screenStateData.media ? screenStateData.media : null);
        const nextPosterType = nextState === 'MEDIA' ? rawState : null;
        const stateChanged = nextState !== currentScreenState;
        const posterTypeChanged = nextPosterType !== currentPosterType;

        if (stateChanged || posterTypeChanged) {
            recordCountdownStateTransition(currentScreenState || 'null', nextState, {
                posterTypeChanged,
                phase: screenStateData?.countdown?.phase || null,
            });
            markUpdateSource('screen-state-sync', {
                previousState: currentScreenState,
                nextState,
                previousPosterType: currentPosterType,
                nextPosterType,
                stateVersion: screenStateData && screenStateData.state_version ? screenStateData.state_version : null
            });
            clearAllScreenContent();
        }

        currentScreenState = nextState;
        currentPosterType = nextPosterType;
        console.log('State updated:', nextState);
        debugLog('state', 'State updated', {
            state: nextState,
            posterType: nextPosterType,
            signature: lastAppliedScreenSignature
        });

        if (nextState === 'COUNTDOWN') {
            debugLog('section', 'Updated section: countdown', {
                state: nextState,
                phase: screenStateData && screenStateData.countdown
                    ? screenStateData.countdown.phase
                    : null
            });
            renderCountdownState(screenStateData);
            return;
        }

        if (countdownVerification.sessionKey) {
            logCountdownDebug('session-cleared', {
                previousSessionKey: countdownVerification.sessionKey,
                nextState,
            });
            countdownVerification.sessionKey = null;
        }

        if (nextState === 'MEDIA') {
            debugLog('section', 'Updated section: media', {
                state: nextState,
                posterType: nextPosterType
            });
            renderMediaState(nextMedia, nextPosterType || 'MEDIA', screenStateData && screenStateData.media_version ? screenStateData.media_version : null);
            return;
        }

        debugLog('section', 'Updated section: timetable-screen', {
            state: nextState
        });
        renderTimetableState();
    }

    async function pollMediaState() {
        const pollSequence = ++mediaPollSequence;

        try {
            const screenStateData = await fetchJson('/api/screen-state');

            if (pollSequence !== mediaPollSequence) {
                return;
            }

            if (!screenStateData || !screenStateData.state) {
                return;
            }

            const serverTimestampMs = Date.parse(screenStateData.timestamp || '');
            if (!Number.isNaN(serverTimestampMs) && serverTimestampMs < lastAppliedServerTimestampMs) {
                return;
            }

            logPayloadReceipt('screen-state', screenStateData);
            const { changed, bundle } = detectVersionChanges(screenStateData);
            logDetectedChanges('screen-state', changed, {
                previousVersions: { ...lastReceivedVersions },
                nextVersions: bundle
            });

            const nextScreenSignature = computeScreenStateSignature(screenStateData);
            const shouldApply = nextScreenSignature !== lastAppliedScreenSignature;

            if (!shouldApply) {
                if (screenStateData.state === 'COUNTDOWN') {
                    countdownVerification.pollSkipsSameSignature += 1;
                }
            }

            if (shouldApply) {
                debugLog('poll', 'Applying screen state update', {
                    stateVersion: screenStateData.state_version || null,
                    mediaVersion: screenStateData.media_version || null,
                    state: screenStateData.state,
                    signature: nextScreenSignature
                });

                if (shouldDeferCountdownClear(screenStateData)) {
                    logCountdownDebug('defer-timetable-until-zero', {
                        timer: document.getElementById('countdown-popup-timer')?.textContent?.trim() || null,
                    });
                } else if (shouldRejectPosterDuringCountdown(screenStateData)) {
                    logCountdownDebug('reject-poster-during-countdown', {
                        state: screenStateData.state,
                        timer: document.getElementById('countdown-popup-timer')?.textContent?.trim() || null,
                    });
                    hidePosterOverlay();
                } else {
                    if (!Number.isNaN(serverTimestampMs)) {
                        lastAppliedServerTimestampMs = serverTimestampMs;
                    }
                    lastAppliedScreenSignature = nextScreenSignature;
                    applyScreenState(screenStateData);
                }
            } else if (isCountdownStuckAtZero()) {
                const serverState = screenStateData.state;
                const serverSecondsRaw = screenStateData.countdown
                    ? Number(screenStateData.countdown.seconds_remaining)
                    : null;
                const serverSecondsAtZero = Number.isFinite(serverSecondsRaw) && serverSecondsRaw <= 0;

                if (serverState === 'TIMETABLE' || isPosterScreenState(serverState) || serverSecondsAtZero) {
                    finalizeCountdownCompletion(
                        (serverState === 'TIMETABLE' || isPosterScreenState(serverState))
                            ? screenStateData
                            : {
                                state: 'TIMETABLE',
                                timestamp: screenStateData.timestamp || new Date().toISOString(),
                            }
                    );
                }
            }

            updateReceivedVersions(screenStateData);

            if (changed.includes('timetable')) {
                requestTimetableSync();
            }

            if (changed.includes('announcements')) {
                requestAnnouncementsSync();
            }

            if (changed.includes('config')) {
                requestScreenConfigSync();
            }

            if (changed.includes('slidingTexts')) {
                requestSlidingTextsSync();
            }
        } catch (error) {
            console.error('Error polling media state:', error);
        }
    }

    function requestMediaSync() {
        if (isMediaPollInFlight) {
            pendingMediaPoll = true;
            return;
        }

        isMediaPollInFlight = true;
        pollMediaState().finally(() => {
            isMediaPollInFlight = false;
            if (pendingMediaPoll) {
                pendingMediaPoll = false;
                requestMediaSync();
            }
        });
    }

    function initMediaDisplay() {
        requestMediaSync();
        ensureMediaPollingTimer();
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

        markUpdateSource('timetable-sync', {
            timetableVersion: payload.timetable_version || null,
            serverDate: payload.server_date || null
        });

        if (payload.jamaat_offsets && typeof payload.jamaat_offsets === 'object') {
            Object.assign(jamaatOffsets, payload.jamaat_offsets);
        }

        const todayData = payload.today || {};
        const tomorrowData = payload.tomorrow || {};
        const prayers = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];

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
    }

    async function pollTimetableData() {
        try {
            const data = await fetchJson('/api/timetable');
            logPayloadReceipt('timetable', data);
            const nextTimetableSignature = computeTimetableSignature(data);
            const shouldApply = nextTimetableSignature !== lastAppliedSectionSignatures.timetable;

            if (shouldApply) {
                const { changed, bundle } = detectVersionChanges(data);
                logDetectedChanges('timetable', changed.length > 0 ? changed : ['timetable'], {
                    previousVersions: { ...lastReceivedVersions },
                    nextVersions: bundle
                });
                debugLog('poll', 'Applying timetable update', {
                    timetableVersion: data && data.timetable_version ? data.timetable_version : null
                });
                lastAppliedSectionSignatures.timetable = nextTimetableSignature;
                applyTimetableUpdate(data);
                debugLog('section', 'Updated section: prayer-times', {
                    section: 'timetable'
                });
            }

            updateReceivedVersions(data);
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

    function announcementKey(announcement, index) {
        if (announcement && announcement.id !== null && typeof announcement.id !== 'undefined') {
            return `id:${announcement.id}`;
        }

        return `fallback:${index}:${announcement.title}:${announcement.content.length}`;
    }

    function getAnnouncementPlaceholderNode() {
        let placeholder = document.querySelector('#announcements-content .announcement-placeholder');
        if (placeholder) {
            return placeholder;
        }

        placeholder = document.createElement('div');
        placeholder.className = 'announcement-placeholder';
        placeholder.style.textAlign = 'center';
        placeholder.style.padding = '20px';

        const text = document.createElement('p');
        text.style.margin = '0';
        text.style.fontSize = '0.9rem';
        text.textContent = 'No announcements currently.';
        placeholder.appendChild(text);

        return placeholder;
    }

    function hydrateAnnouncementNodeCache() {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) {
            return;
        }

        announcementNodesByKey.clear();
        Array.from(contentContainer.querySelectorAll('.rotating-announcement')).forEach((node, index) => {
            const announcementId = node.dataset.announcementId;
            const fallbackTitle = node.querySelector('.announcement-title') ? node.querySelector('.announcement-title').textContent || '' : '';
            const fallbackContent = node.querySelector('.announcement-text-scroll') ? node.querySelector('.announcement-text-scroll').textContent || '' : '';
            const key = announcementId
                ? `id:${announcementId}`
                : `fallback:${index}:${fallbackTitle}:${fallbackContent.length}`;

            node.dataset.announcementKey = key;
            announcementNodesByKey.set(key, node);
        });
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatAnnouncementBodyHtml(content) {
        const normalized = String(content || '')
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n');
        return escapeHtml(normalized).replace(/\n/g, '<br>');
    }

    function setAnnouncementBodyContent(scrollEl, content) {
        if (!scrollEl) {
            return;
        }

        const normalized = String(content || '')
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n');
        const previous = scrollEl.dataset.rawContent || '';
        if (previous === normalized && scrollEl.childNodes.length > 0) {
            return;
        }

        scrollEl.dataset.rawContent = normalized;
        scrollEl.innerHTML = formatAnnouncementBodyHtml(normalized);
    }

    function updateAnnouncementNode(item, announcement, index) {
        const normalized = normalizeAnnouncement(announcement);
        const key = announcementKey(normalized, index);
        let title = item.querySelector('.announcement-title');
        let textContainer = item.querySelector('.announcement-text-container');
        let scrollingText = item.querySelector('.announcement-text-scroll');

        if (!title) {
            title = document.createElement('div');
            title.className = 'announcement-title';
            item.appendChild(title);
        }

        if (!textContainer) {
            textContainer = document.createElement('div');
            textContainer.className = 'announcement-text-container';
            item.appendChild(textContainer);
        }

        if (!scrollingText) {
            scrollingText = document.createElement('div');
            scrollingText.className = 'announcement-text-scroll';
            textContainer.appendChild(scrollingText);
        }

        item.dataset.announcementId = normalized.id !== null ? String(normalized.id) : '';
        item.dataset.announcementKey = key;
        item.dataset.index = String(index);
        item.dataset.displayOrder = String(normalized.display_order ?? (index + 1));
        item.dataset.displayDurationSeconds = String(normalized.display_duration ?? 10);
        item.dataset.duration = String((normalized.display_duration ?? 10) * 1000);
        item.style.margin = '0';
        item.style.padding = '12px';
        item.style.boxSizing = 'border-box';
        item.style.wordWrap = 'break-word';
        item.style.wordBreak = 'break-word';
        item.style.overflow = 'hidden';
        item.style.backgroundColor = normalized.background_color;
        item.style.width = '100%';
        item.style.minHeight = '0';
        item.style.boxSizing = 'border-box';

        title.style.fontWeight = 'bold';
        title.style.marginBottom = '8px';
        title.style.color = normalized.text_color;
        title.style.fontSize = normalizeAnnouncementRemFontSize(normalized.title_font_size, '2.25rem');
        title.style.lineHeight = '1.3';
        title.style.whiteSpace = 'normal';
        title.style.overflowWrap = 'break-word';
        title.style.wordBreak = 'normal';
        if (normalized.title) {
            title.style.display = '';
            if (title.textContent !== normalized.title) {
                title.textContent = normalized.title;
            }
        } else {
            title.style.display = 'none';
            title.textContent = '';
        }

        textContainer.style.flex = '1 1 0';
        textContainer.style.minHeight = '0';
        textContainer.style.overflow = 'hidden';
        textContainer.style.position = 'relative';
        textContainer.style.padding = '0';

        title.style.flex = '0 0 auto';
        title.style.flexShrink = '0';

        scrollingText.style.fontSize = normalizeAnnouncementRemFontSize(normalized.font_size, '1.5rem');
        scrollingText.style.color = normalized.text_color;
        scrollingText.style.wordWrap = 'break-word';
        scrollingText.style.overflowWrap = 'break-word';
        scrollingText.style.wordBreak = 'normal';
        scrollingText.style.whiteSpace = 'normal';
        scrollingText.style.lineHeight = '1.45';
        scrollingText.style.margin = '0';
        scrollingText.style.minHeight = '0';

        const previousSpeed = Number(scrollingText.getAttribute('data-scroll-speed')) || 0;
        const previousFont = scrollingText.style.fontSize;
        const nextFont = normalizeAnnouncementRemFontSize(normalized.font_size, '1.5rem');
        const previousContent = scrollingText.dataset.rawContent || '';
        const speedChanged = previousSpeed !== Number(normalized.scroll_speed);
        const fontChanged = previousFont !== nextFont;
        const contentChanged = previousContent !== String(normalized.content || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');

        scrollingText.setAttribute('data-scroll-speed', String(normalized.scroll_speed));
        setAnnouncementBodyContent(scrollingText, normalized.content);

        if (contentChanged || fontChanged || speedChanged || !scrollingText.classList.contains('is-scrolling')) {
            scrollingText.dataset.forceScrollRestart = '1';
        }

        item.dataset.requiredDisplayDurationMs = '';

        announcementNodesByKey.set(key, item);
        return item;
    }

    function createAnnouncementNode(announcement, index) {
        const item = document.createElement('div');
        item.className = 'announcement-item rotating-announcement';
        return updateAnnouncementNode(item, announcement, index);
    }

    function renderAnnouncements(announcements) {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) {
            return;
        }

        announcementsDataCache = sortAnnouncementsByDisplayOrder(
            (announcements || []).map(normalizeAnnouncement)
        );

        if (announcementNodesByKey.size === 0) {
            hydrateAnnouncementNodeCache();
        }

        const normalizedAnnouncements = announcementsDataCache;
        const nextKeys = new Set();

        if (normalizedAnnouncements.length === 0) {
            Array.from(announcementNodesByKey.entries()).forEach(([key, node]) => {
                if (node.parentElement === contentContainer) {
                    contentContainer.removeChild(node);
                }
                announcementNodesByKey.delete(key);
            });

            const placeholder = getAnnouncementPlaceholderNode();
            if (!placeholder.parentElement) {
                contentContainer.appendChild(placeholder);
            }

            markRenderCause('render-announcements-empty', { removed: announcementNodesByKey.size });
            applyAnnouncementsPresentation('render-announcements-empty');
            return;
        }

        const existingPlaceholder = contentContainer.querySelector('.announcement-placeholder');
        if (existingPlaceholder) {
            existingPlaceholder.remove();
        }

        const previousKeys = new Set(announcementNodesByKey.keys());

        normalizedAnnouncements.forEach((announcement, index) => {
            const key = announcementKey(announcement, index);
            nextKeys.add(key);

            let node = announcementNodesByKey.get(key);
            if (!node) {
                node = createAnnouncementNode(announcement, index);
            } else {
                updateAnnouncementNode(node, announcement, index);
            }

            contentContainer.appendChild(node);
        });

        Array.from(announcementNodesByKey.entries()).forEach(([key, node]) => {
            if (nextKeys.has(key)) {
                return;
            }

            if (node.parentElement === contentContainer) {
                contentContainer.removeChild(node);
            }
            announcementNodesByKey.delete(key);
        });

        markRenderCause('render-announcements', { count: normalizedAnnouncements.length });
        const keysChanged = previousKeys.size !== nextKeys.size
            || Array.from(nextKeys).some((key) => !previousKeys.has(key))
            || Array.from(previousKeys).some((key) => !nextKeys.has(key));
        applyAnnouncementsPresentation(keysChanged ? 'render-announcements-replaced' : 'render-announcements-update');
    }

    function normalizeSlidingTextRecord(text) {
        return {
            id: text && typeof text.id !== 'undefined' ? text.id : null,
            text: text && text.text ? text.text : '',
            font_size: text && text.font_size ? text.font_size : null,
            font_weight: text && text.font_weight ? text.font_weight : null,
            text_color: text && text.text_color ? text.text_color : null,
            animation_speed: text && text.animation_speed ? text.animation_speed : null,
            display_order: text && text.display_order ? text.display_order : null
        };
    }

    function applySlidingTextTypography(node, record, boxStyling) {
        const boxFontDefault = boxStyling && boxStyling.font_size ? boxStyling.font_size : null;
        const boxColorDefault = (boxStyling && boxStyling.text_color) || '#000000';
        const boxWeightDefault = (boxStyling && boxStyling.font_weight) || '700';
        const fontSize = normalizeBladeRemFontSize(record.font_size || boxFontDefault, '5rem');
        const fontWeight = String(record.font_weight || boxWeightDefault);
        const textColor = record.text_color || boxColorDefault;

        node.style.setProperty('font-size', fontSize, 'important');
        node.style.setProperty('font-weight', fontWeight, 'important');
        node.style.setProperty('color', textColor, 'important');
    }

    function buildSlidingTextNode(record, boxStyling) {
        const span = document.createElement('span');
        span.className = 'scroll-item';

        if (record.id !== null && typeof record.id !== 'undefined') {
            span.dataset.slidingTextId = String(record.id);
        }

        applySlidingTextTypography(span, record, boxStyling);
        span.textContent = record.text || '';
        return span;
    }

    function updateSlidingTextNode(node, record, boxStyling) {
        applySlidingTextTypography(node, record, boxStyling);

        if (node.textContent !== (record.text || '')) {
            node.textContent = record.text || '';
        }
    }

    function renderSlidingTexts(slidingTexts, boxStyling) {
        const section = document.querySelector('[data-box-root="sliding_text_box"]');
        const wrapper = section ? section.querySelector('.scroll-wrapper') : null;
        if (!wrapper) {
            return;
        }

        if (section && boxStyling && boxStyling.font_size) {
            section.style.setProperty('font-size', normalizeBladeRemFontSize(boxStyling.font_size, '5rem'), 'important');
        }

        const normalized = (Array.isArray(slidingTexts) ? slidingTexts : []).map(normalizeSlidingTextRecord);
        const duplicated = normalized.length > 0 ? [...normalized, ...normalized] : [];
        const placeholder = 'Welcome to the Masjid - No sliding text configured';

        if (duplicated.length === 0) {
            wrapper.replaceChildren(
                buildSlidingTextNode({ text: placeholder }, boxStyling),
                buildSlidingTextNode({ text: placeholder }, boxStyling)
            );
            return;
        }

        const existing = Array.from(wrapper.querySelectorAll('.scroll-item'));
        const canUpdateInPlace = existing.length === duplicated.length && duplicated.every((record, index) => {
            const node = existing[index];
            if (!node) {
                return false;
            }

            if (record.id === null || typeof record.id === 'undefined') {
                return true;
            }

            return node.dataset.slidingTextId === String(record.id);
        });

        if (canUpdateInPlace) {
            duplicated.forEach((record, index) => {
                updateSlidingTextNode(existing[index], record, boxStyling);
            });
            return;
        }

        wrapper.replaceChildren(...duplicated.map((record) => buildSlidingTextNode(record, boxStyling)));
    }

    function applySlidingTextsPayload(payload, boxStyling) {
        if (!payload || typeof payload !== 'object') {
            return;
        }

        if (Array.isArray(payload.sliding_texts)) {
            lastSlidingTextsCache = payload.sliding_texts;
        }

        renderSlidingTexts(lastSlidingTextsCache, boxStyling || lastSlidingBoxStyling || {});
    }

    function extractSlidingTextsFromDom() {
        const wrapper = document.querySelector('[data-box-root="sliding_text_box"] .scroll-wrapper');
        if (!wrapper) {
            return [];
        }

        const seen = new Set();
        const records = [];

        wrapper.querySelectorAll('.scroll-item[data-sliding-text-id]').forEach((node) => {
            const id = node.dataset.slidingTextId;
            if (!id || seen.has(id)) {
                return;
            }

            seen.add(id);
            records.push({
                id,
                text: node.textContent || '',
                font_size: parseFloat(String(node.style.fontSize || '').replace(/rem$/i, '')) || null,
                font_weight: node.style.fontWeight || null,
                text_color: node.style.color || null
            });
        });

        return records;
    }

    async function pollSlidingTextsData() {
        try {
            const response = await fetchJson('/api/sliding-texts');
            logPayloadReceipt('sliding-texts', response);
            const nextSignature = computeSlidingTextsSignature(response);
            const shouldApply = nextSignature !== lastAppliedSectionSignatures.slidingTexts;

            if (shouldApply) {
                const { changed, bundle } = detectVersionChanges(response);
                logDetectedChanges('sliding-texts', changed.length > 0 ? changed : ['slidingTexts'], {
                    previousVersions: { ...lastReceivedVersions },
                    nextVersions: bundle
                });
                markUpdateSource('sliding-texts-sync', {
                    slidingTextsVersion: response && response.sliding_texts_version ? response.sliding_texts_version : null
                });
                lastAppliedSectionSignatures.slidingTexts = nextSignature;
                applySlidingTextsPayload(response, lastSlidingBoxStyling);
                debugLog('section', 'Updated section: sliding-texts', {
                    section: 'sliding-texts'
                });
            }

            updateReceivedVersions(response);
        } catch (error) {
            console.error('Error polling sliding texts:', error);
        }
    }

    function requestSlidingTextsSync() {
        if (isSlidingTextsPollInFlight) {
            slidingTextsSyncQueued = true;
            return;
        }

        isSlidingTextsPollInFlight = true;
        pollSlidingTextsData().finally(() => {
            isSlidingTextsPollInFlight = false;
            if (slidingTextsSyncQueued) {
                slidingTextsSyncQueued = false;
                requestSlidingTextsSync();
            }
        });
    }

    async function pollAnnouncementsData() {
        try {
            const response = await fetchJson('/api/announcements');
            logPayloadReceipt('announcements', response);
            const nextAnnouncementsSignature = computeAnnouncementsSignature(response);
            const shouldApply = nextAnnouncementsSignature !== lastAppliedSectionSignatures.announcements;

            if (shouldApply) {
                const { changed, bundle } = detectVersionChanges(response);
                logDetectedChanges('announcements', changed.length > 0 ? changed : ['announcements'], {
                    previousVersions: { ...lastReceivedVersions },
                    nextVersions: bundle
                });
                markUpdateSource('announcements-sync', {
                    announcementsVersion: response && response.announcements_version ? response.announcements_version : null
                });
                lastAppliedSectionSignatures.announcements = nextAnnouncementsSignature;
                renderAnnouncements(Array.isArray(response && response.announcements) ? response.announcements : []);
                debugLog('section', 'Updated section: announcements', {
                    section: 'announcements'
                });
            }

            updateReceivedVersions(response);
        } catch (error) {
            console.error('Error polling announcements:', error);
        }
    }

    async function pollScreenConfigData() {
        try {
            const response = await fetchJson('/api/screen-config');
            logPayloadReceipt('screen-config', response);
            const nextConfigSignature = computeConfigSignature(response);
            const versionChanged = !!(response && response.config_version)
                && lastReceivedVersions.config !== response.config_version;
            const shouldApply = nextConfigSignature !== lastAppliedSectionSignatures.config || versionChanged;

            if (shouldApply) {
                const { changed, bundle } = detectVersionChanges(response);
                logDetectedChanges('screen-config', changed.length > 0 ? changed : ['config'], {
                    previousVersions: { ...lastReceivedVersions },
                    nextVersions: bundle
                });
                debugLog('poll', 'Applying screen config update without reload', {
                    configVersion: response && response.config_version ? response.config_version : null
                });
                lastAppliedSectionSignatures.config = nextConfigSignature;
                applyScreenConfig(response);
                if (response && response.sliding_texts_version) {
                    lastAppliedSectionSignatures.slidingTexts = computeSlidingTextsSignature(response);
                }
                debugLog('section', 'Updated section: config-layout', {
                    section: 'config'
                });
            }

            updateReceivedVersions(response);
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
            screenConfigSyncQueued = true;
            return;
        }

        isScreenConfigPollInFlight = true;
        pollScreenConfigData().finally(() => {
            isScreenConfigPollInFlight = false;
            if (screenConfigSyncQueued) {
                screenConfigSyncQueued = false;
                requestScreenConfigSync();
            }
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
    }

    function getVisibleAnnouncementElements(contentContainer) {
        const announcements = Array.from(contentContainer.querySelectorAll('.rotating-announcement'));
        const mode = contentContainer.dataset.displayMode || 'rotation';

        if (mode === 'show-all') {
            return announcements;
        }

        return announcements.filter((el) => el.classList.contains('is-active'));
    }

    function resolveAnnouncementLineHeightPx(scrollDiv, computedStyles = null) {
        const styles = computedStyles || window.getComputedStyle(scrollDiv);
        const rawLineHeight = styles.lineHeight;
        if (rawLineHeight && rawLineHeight !== 'normal') {
            const parsed = parseFloat(rawLineHeight);
            // getComputedStyle usually returns px for line-height
            if (Number.isFinite(parsed) && parsed > 1 && String(rawLineHeight).includes('px')) {
                return parsed;
            }
        }

        const fontSizePx = parseFloat(styles.fontSize);
        return (Number.isFinite(fontSizePx) && fontSizePx > 0 ? fontSizePx : 16) * 1.45;
    }

    function performAnnouncementLayoutSync(reason = '') {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) {
            return;
        }

        const announcements = getVisibleAnnouncementElements(contentContainer);
        announcements.forEach((el, index) => {
            const container = el.querySelector('.announcement-text-container');
            const scrollDiv = el.querySelector('.announcement-text-scroll');

            if (!container || !scrollDiv) {
                return;
            }

            // Measure natural content height without temporary end padding.
            scrollDiv.style.paddingBottom = '0.35em';
            void scrollDiv.offsetHeight;

            const computedStyles = window.getComputedStyle(scrollDiv);
            const lineHeight = resolveAnnouncementLineHeightPx(scrollDiv, computedStyles);
            const containerHeight = Math.ceil(container.clientHeight);
            const contentHeight = Math.ceil(scrollDiv.scrollHeight);
            const configuredDisplaySeconds = Math.max(
                1,
                parseInt(el.dataset.displayDurationSeconds, 10) || Math.round((parseInt(el.dataset.duration, 10) || 10000) / 1000)
            );
            const scrollSpeed = Number(scrollDiv.getAttribute('data-scroll-speed')) || 3;
            const plan = (window.AnnouncementScroll && typeof window.AnnouncementScroll.computeAnnouncementScrollPlan === 'function')
                ? window.AnnouncementScroll.computeAnnouncementScrollPlan({
                    contentHeight,
                    containerHeight,
                    lineHeight,
                    scrollSpeed,
                    configuredDisplaySeconds,
                })
                : {
                    isOverflowing: containerHeight > 0 && (contentHeight - containerHeight) > Math.max(2, lineHeight * 0.3),
                    overflowDistance: Math.max(0, contentHeight - containerHeight),
                    endPaddingPx: Math.ceil(Math.max(lineHeight * 1.35, containerHeight * 0.18, 40)),
                    scrollDistance: Math.max(0, contentHeight - containerHeight),
                    animationDurationSec: announcementAnimationDuration(scrollSpeed),
                    startHoldSec: 0.9,
                    scrollMotionSec: announcementAnimationDuration(scrollSpeed),
                };

            if (plan.isOverflowing && plan.endPaddingPx > 0) {
                scrollDiv.style.paddingBottom = `${plan.endPaddingPx}px`;
                void scrollDiv.offsetHeight;
                plan.scrollDistance = Math.max(0, Math.ceil(scrollDiv.scrollHeight) - containerHeight);
            }

            debugLog('announcements', 'Measured announcement layout', {
                reason,
                index,
                containerHeight,
                contentHeight,
                lineHeight,
                overflowDistance: plan.overflowDistance,
                endPaddingPx: plan.endPaddingPx,
                scrollDistance: plan.scrollDistance,
                isOverflowing: plan.isOverflowing,
                animationDurationSec: plan.animationDurationSec,
                startHoldSec: plan.startHoldSec,
            });

            el.dataset.requiredDisplayDurationMs = String(configuredDisplaySeconds * 1000);
            syncAnnouncementScrollState(scrollDiv, container, plan);
        });
    }

    function scheduleAnnouncementLayoutSync(reason = '') {
        requestAnimationFrame(() => {
            performAnnouncementLayoutSync(reason);
            requestAnimationFrame(() => {
                performAnnouncementLayoutSync(`${reason}:second-pass`);
            });
        });
    }

    function checkAnnouncementScrolling() {
        scheduleAnnouncementLayoutSync('checkAnnouncementScrolling');
    }

    function cancelAnnouncementScrollAnimation(scrollDiv) {
        if (!scrollDiv) {
            return;
        }

        if (scrollDiv._announcementScrollAnimation) {
            try {
                scrollDiv._announcementScrollAnimation.cancel();
            } catch (e) {
                // ignore
            }
            scrollDiv._announcementScrollAnimation = null;
        }
    }

    function rewindAnnouncementScrollToStart(scrollDiv) {
        if (!scrollDiv) {
            return;
        }

        cancelAnnouncementScrollAnimation(scrollDiv);
        scrollDiv.classList.add('no-scroll');
        scrollDiv.classList.remove('is-scrolling');
        scrollDiv.style.animation = 'none';
        scrollDiv.style.animationPlayState = 'paused';
        scrollDiv.style.transform = 'translate3d(0, 0, 0)';
        scrollDiv.dataset.forceScrollRestart = '1';
        scrollDiv.dataset.scrollDistancePx = '';
        scrollDiv.dataset.scrollDurationSec = '';
        scrollDiv.dataset.appliedScrollSpeed = '';
        if (scrollDiv.parentElement) {
            scrollDiv.parentElement.scrollTop = 0;
        }
        void scrollDiv.offsetHeight;
    }

    function resetAnnouncementScrollState(scrollDiv) {
        if (!scrollDiv) {
            return;
        }

        rewindAnnouncementScrollToStart(scrollDiv);
        scrollDiv.style.removeProperty('--announcement-scroll-distance');
        scrollDiv.dataset.forceScrollRestart = '';
    }

    function syncAnnouncementScrollState(scrollDiv, container, plan) {
        if (!scrollDiv || !container || !plan) {
            return;
        }

        const item = scrollDiv.closest('.rotating-announcement, .announcement-item');
        const mode = (document.getElementById('announcements-content') || {}).dataset.displayMode || 'rotation';
        const isActive = mode === 'show-all' || !item || item.classList.contains('is-active');

        if (!isActive) {
            rewindAnnouncementScrollToStart(scrollDiv);
            scrollDiv.dataset.forceScrollRestart = '';
            return;
        }

        if (!plan.isOverflowing || plan.scrollDistance <= 1) {
            resetAnnouncementScrollState(scrollDiv);
            scrollDiv.style.transform = 'translate3d(0, 0, 0)';
            return;
        }

        if (plan.endPaddingPx > 0) {
            scrollDiv.style.paddingBottom = `${plan.endPaddingPx}px`;
        }

        const distancePx = Math.ceil(plan.scrollDistance);
        const motionSec = Math.max(2.5, plan.scrollMotionSec || plan.animationDurationSec || 8);
        const startHoldSec = Math.max(0.45, plan.startHoldSec || 0.9);
        const currentSpeed = Number(scrollDiv.getAttribute('data-scroll-speed')) || 3;
        const forceRestart = scrollDiv.dataset.forceScrollRestart === '1';
        const alreadyScrolling = scrollDiv.classList.contains('is-scrolling')
            && scrollDiv.style.animation
            && scrollDiv.style.animation !== 'none'
            && scrollDiv.style.animationPlayState !== 'paused';

        if (
            alreadyScrolling
            && !forceRestart
            && Number(scrollDiv.dataset.appliedScrollSpeed || 0) === currentSpeed
            && Math.abs(Number(scrollDiv.dataset.scrollDistancePx || 0) - distancePx) < 3
            && Math.abs(Number(scrollDiv.dataset.scrollDurationSec || 0) - motionSec) < 0.35
        ) {
            return;
        }

        cancelAnnouncementScrollAnimation(scrollDiv);
        scrollDiv.classList.remove('no-scroll');
        scrollDiv.classList.add('is-scrolling');
        scrollDiv.style.animation = 'none';
        scrollDiv.style.animationPlayState = 'paused';
        scrollDiv.style.setProperty('--announcement-scroll-distance', `${distancePx}px`);
        scrollDiv.style.transform = 'translate3d(0, 0, 0)';
        scrollDiv.dataset.scrollDistancePx = String(distancePx);
        scrollDiv.dataset.scrollDurationSec = String(motionSec);
        scrollDiv.dataset.appliedScrollSpeed = String(currentSpeed);
        scrollDiv.dataset.forceScrollRestart = '';
        void scrollDiv.offsetHeight;

        // Short readable pause on the first lines, then one smooth scroll to the end.
        // fill-mode "both" keeps the first line visible during the start delay.
        scrollDiv.style.animation = `scroll-vertical-measured ${motionSec}s linear ${startHoldSec}s 1 both`;
        scrollDiv.style.animationPlayState = 'running';
    }

    function getAnnouncementDisplayDurationMs(announcementEl) {
        if (!announcementEl) {
            return 10000;
        }

        const seconds = parseInt(announcementEl.dataset.displayDurationSeconds, 10);
        if (Number.isFinite(seconds) && seconds > 0) {
            return seconds * 1000;
        }

        const durationMs = parseInt(announcementEl.dataset.duration, 10);
        if (Number.isFinite(durationMs) && durationMs > 0) {
            return durationMs;
        }

        return 10000;
    }

    function applyAnnouncementItemLayout(el, mode, isVisible) {
        el.style.removeProperty('opacity');
        el.style.removeProperty('z-index');
        el.style.removeProperty('pointer-events');
        el.style.removeProperty('transition');
        el.style.removeProperty('position');
        el.style.removeProperty('inset');
        el.style.removeProperty('display');

        if (mode === 'show-all') {
            el.classList.remove('is-active');
            return;
        }

        el.classList.toggle('is-active', !!isVisible);
    }

    function getAnnouncementNodes(contentContainer = null) {
        const container = contentContainer || document.getElementById('announcements-content');
        if (!container) {
            return [];
        }

        return Array.from(container.querySelectorAll('.rotating-announcement'));
    }

    function normalizeAnnouncementRotationIndex(index, announcementCount) {
        if (!announcementCount) {
            return 0;
        }

        const normalized = Number.isFinite(index) ? index : 0;
        return ((normalized % announcementCount) + announcementCount) % announcementCount;
    }

    function applyAnnouncementRotationVisibility(activeIndex, contentContainer = null, options = {}) {
        const container = contentContainer || document.getElementById('announcements-content');
        const announcements = getAnnouncementNodes(container);
        if (!container || announcements.length === 0) {
            return 0;
        }

        const previousActive = announcements.findIndex((el) => el.classList.contains('is-active'));
        const safeIndex = normalizeAnnouncementRotationIndex(activeIndex, announcements.length);
        announcementRotationIndex = safeIndex;
        const activeChanged = previousActive !== safeIndex;
        const rewindIncoming = activeChanged || options.rewindIncoming === true;

        announcements.forEach((el, index) => {
            const isVisible = index === safeIndex;
            const scrollDiv = el.querySelector('.announcement-text-scroll');

            if (scrollDiv) {
                if (isVisible && rewindIncoming) {
                    rewindAnnouncementScrollToStart(scrollDiv);
                } else if (!isVisible) {
                    rewindAnnouncementScrollToStart(scrollDiv);
                    scrollDiv.dataset.forceScrollRestart = '';
                }
            }

            applyAnnouncementItemLayout(el, 'rotation', isVisible);
        });

        return safeIndex;
    }

    function setAnnouncementDisplayMode(mode, options = {}) {
        const force = options.force === true;
        const restartRotation = options.restartRotation === true;
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) return;

        const announcements = getAnnouncementNodes(contentContainer);
        const normalizedMode = mode === 'show-all' ? 'show-all' : 'rotation';
        const modeChanged = contentContainer.dataset.displayMode !== normalizedMode;
        lastAnnouncementDisplayMode = normalizedMode;

        if (!force && !restartRotation && !modeChanged && contentContainer.dataset.displayMode === normalizedMode) {
            if (normalizedMode === 'show-all') {
                return;
            }

            if (announcementRotationInterval) {
                applyAnnouncementRotationVisibility(announcementRotationIndex, contentContainer);
                return;
            }
        }

        if (normalizedMode === 'show-all' || restartRotation || modeChanged) {
            if (announcementRotationInterval) {
                clearTimeout(announcementRotationInterval);
                announcementRotationInterval = null;
            }
        }

        contentContainer.dataset.displayMode = normalizedMode;

        if (normalizedMode === 'show-all') {
            announcements.forEach((el) => {
                applyAnnouncementItemLayout(el, 'show-all', true);
                const scrollDiv = el.querySelector('.announcement-text-scroll');
                if (scrollDiv) {
                    rewindAnnouncementScrollToStart(scrollDiv);
                }
            });
            scheduleAnnouncementLayoutSync('setAnnouncementDisplayMode:show-all');
            return;
        }

        if (restartRotation || modeChanged) {
            const activeIndex = announcements.findIndex((el) => el.classList.contains('is-active'));
            announcementRotationIndex = activeIndex >= 0 ? activeIndex : announcementRotationIndex;
        }

        applyAnnouncementRotationVisibility(announcementRotationIndex, contentContainer, {
            rewindIncoming: restartRotation || modeChanged,
        });

        if (restartRotation || modeChanged || !announcementRotationInterval) {
            initDynamicAnnouncements(announcementRotationIndex);
        }
    }

    function initDynamicAnnouncements(startIndex = 0) {
        const contentContainer = document.getElementById('announcements-content');
        if (!contentContainer) return;

        const announcements = getAnnouncementNodes(contentContainer);
        if (announcements.length === 0) return;

        if (announcementRotationInterval) {
            clearTimeout(announcementRotationInterval);
            announcementRotationInterval = null;
        }

        let currentIndex = applyAnnouncementRotationVisibility(startIndex, contentContainer, { rewindIncoming: true });

        function showOnly(index) {
            currentIndex = applyAnnouncementRotationVisibility(index, contentContainer, { rewindIncoming: true });
            scheduleAnnouncementLayoutSync(`initDynamicAnnouncements:showOnly:${currentIndex}`);
        }

        function rotate() {
            const liveAnnouncements = getAnnouncementNodes(contentContainer);
            if (liveAnnouncements.length < 2) {
                announcementRotationInterval = null;
                announcementRotationStartedAt = 0;
                return;
            }

            showOnly(currentIndex + 1);

            const active = getAnnouncementNodes(contentContainer)[currentIndex];
            announcementRotationStartedAt = Date.now();
            const nextDuration = getAnnouncementDisplayDurationMs(active);
            announcementRotationInterval = setTimeout(rotate, nextDuration);
        }

        scheduleAnnouncementLayoutSync(`initDynamicAnnouncements:start:${currentIndex}`);

        if (announcements.length < 2) {
            announcementRotationStartedAt = 0;
            return;
        }

        announcementRotationStartedAt = Date.now();
        const firstDuration = getAnnouncementDisplayDurationMs(getAnnouncementNodes(contentContainer)[currentIndex]);
        announcementRotationInterval = setTimeout(rotate, firstDuration);
    }

    function initAnnouncementLayoutHandling() {
        if (!announcementResizeHandler) {
            announcementResizeHandler = () => {
                applyAnnouncementsPresentation('window-resize');
                syncBoardColumnLayout();
            };
            window.addEventListener('resize', announcementResizeHandler);
        }

        if (document.fonts && typeof document.fonts.addEventListener === 'function') {
            document.fonts.addEventListener('loadingdone', () => {
                scheduleAnnouncementLayoutSync('fonts-loadingdone');
                syncBoardColumnLayout();
            });
        }
    }

    function resetDailyRuntimeState() {
        currentScreenState = null;
        currentPosterType = null;
        currentMediaVersionToken = null;
        lastAppliedScreenSignature = null;
        lastAppliedServerTimestampMs = 0;
        lastReceivedVersions.announcements = null;
        lastReceivedVersions.media = null;
        lastReceivedVersions.timetable = null;
        lastReceivedVersions.config = null;
        lastReceivedVersions.state = null;
        lastAppliedSectionSignatures.timetable = null;
        lastAppliedSectionSignatures.announcements = null;
        lastAppliedSectionSignatures.config = null;
        clearAllScreenContent();
    }

    let lastAnnouncementDisplayMode = null;

    document.addEventListener('DOMContentLoaded', async function() {
        fullscreenState.wasActive = !!getFullscreenElement();
        updateFullscreenButton();
        initContentRotation();

        applyAnnouncementsPresentation('dom-ready-immediate');

        // Start screen-state polling immediately so countdown windows are never missed
        // while slower section polls are still seeding their signatures.
        initMediaDisplay();

        await seedInitialPollSignatures();

        if (announcementsDataCache.length === 0) {
            announcementsDataCache = extractAnnouncementsDataFromDom();
        }

        if (lastSlidingTextsCache.length === 0) {
            lastSlidingTextsCache = extractSlidingTextsFromDom();
        }

        applyAnnouncementsPresentation('dom-ready');
        initAnnouncementLayoutHandling();
        syncBoardColumnLayout(@json($boxSettings ?? []));

        initDisplaySyncListeners();

        requestTimetableSync();
        requestAnnouncementsSync();
        requestSlidingTextsSync();
        requestScreenConfigSync();

        timetablePollingTimer = setInterval(requestTimetableSync, POLL_INTERVALS.timetable);
        announcementsPollingTimer = setInterval(requestAnnouncementsSync, POLL_INTERVALS.announcements);
        slidingTextsPollingTimer = setInterval(requestSlidingTextsSync, POLL_INTERVALS.slidingTexts);
        screenConfigPollingTimer = setInterval(requestScreenConfigSync, POLL_INTERVALS.screenConfig);

        window.addEventListener('storage', function(e) {
            if (e.key === DISPLAY_MODE_KEY) {
                applyAnnouncementsPresentation('display-mode-storage');
            }
        });
    });

</script>
@endsection
