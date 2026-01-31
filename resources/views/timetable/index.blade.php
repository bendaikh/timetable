@extends('layouts.app')

@section('title', $settings['masjid_name'] . ' - Prayer Timetable')

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
<div class="container-fluid digital-board" style="{{ $backgroundStyle }}; padding: 0; margin: 0;">
    <!-- Unified Container for Consistent Width -->
    <div class="unified-container" style="padding: 0; margin: 0;">
        <!-- Top Header Row -->
        @if($useBoxesStyling && isset($boxSettings['header_box']))
            @php
                $headerBox = $boxSettings['header_box'] ?? null;
                $headerStyling = $headerBox['styling_settings'] ?? [];
            @endphp
            <div class="board-header" style="
                background-color: {{ $headerStyling['background_color'] ?? '#1e4d2b' }};
                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                border: {{ $headerStyling['border_width'] ?? '2px' }} solid {{ $headerStyling['border_color'] ?? '#0066cc' }};
                border-radius: {{ $headerStyling['border_radius'] ?? '0px' }};
                padding: 10px 20px;
                margin: 0;
            ">
                <div class="row align-items-center m-0">
                    <!-- Current Time -->
                    <div class="col-md-4 p-0">
                        <div class="current-time-display">
                            <div class="time-large" id="current-time" style="
                                font-size: {{ $headerStyling['time_font_size'] ?? '5rem' }};
                                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                margin: 0;
                            ">{{ $now->format('h:i:s A') }}</div>
                        </div>
                    </div>
                    
                    <!-- Gregorian Date -->
                    <div class="col-md-4 text-center p-0">
                        <div class="date-display">
                            <div class="gregorian-date" style="
                                font-size: {{ $headerStyling['date_font_size'] ?? '4rem' }};
                                font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                margin: 0;
                            ">{{ $now->format('D j M Y') }}</div>
                        </div>
                    </div>
                    
                    <!-- Islamic Date and Fullscreen Button -->
                    <div class="col-md-4 p-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="islamic-date-display text-center" style="flex: 1;">
                                <div class="islamic-date" style="
                                    font-size: {{ $headerStyling['date_font_size'] ?? '4rem' }};
                                    font-family: {{ $headerStyling['font_family'] ?? 'Arial, sans-serif' }};
                                    color: {{ $headerStyling['text_color'] ?? '#000000' }};
                                    margin: 0;
                                ">{{ $islamicDate['day'] }} {{ $islamicDate['month'] }} {{ $islamicDate['year'] }}</div>
                            </div>
                            <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenBtn" style="font-size: 1.5rem;">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$useBoxesStyling)
            <!-- Original Clean Header Design -->
            @if(!isset($activeBoxTypes) || in_array('header_box', $activeBoxTypes))
            <div class="board-header">
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
                            <div class="gregorian-date">{{ $now->format('D j M Y') }}</div>
                        </div>
                    </div>
                    
                    <!-- Islamic Date and Fullscreen Button -->
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="islamic-date-display text-center" style="flex: 1;">
                                <div class="islamic-date">{{ $islamicDate['day'] }} {{ $islamicDate['month'] }} {{ $islamicDate['year'] }}</div>
                            </div>
                            <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenBtn">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endif

        <!-- Main Content Area -->
        <div class="board-main-content" style="padding: 0; margin: 0;">
            <div class="row h-100 m-0">
            <!-- Left Column - Prayer Times (2/3 width) -->
            @php $__showPrayer = ($useBoxesStyling ? isset($boxSettings['prayer_times_box']) : (!isset($activeBoxTypes) || in_array('prayer_times_box', $activeBoxTypes))); @endphp
            @if($__showPrayer)
            <div class="col-md-8 p-0">
                @php
                    $prayerBox = $useBoxesStyling && isset($boxSettings['prayer_times_box']) ? $boxSettings['prayer_times_box'] : null;
                    $prayerStyling = $prayerBox['styling_settings'] ?? [];
                    $prayerLayout = $prayerBox['layout_settings'] ?? [];
                @endphp
                @if($useBoxesStyling && isset($boxSettings['prayer_times_box']))
                    <div class="prayer-times-section" style="
                        background-color: {{ $prayerStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $prayerStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $prayerStyling['font_family'] ?? 'Arial, sans-serif' }};
                        font-size: {{ $prayerStyling['font_size'] ?? '3.5rem' }};
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
                            font-size: {{ $prayerStyling['header_font_size'] ?? '3.5rem' }};
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
                    <div class="prayer-times-section" @if($settings['logo_path'] ?? false) style="--logo-bg-image: url('{{ app()->environment('production') ? url('public/storage/' . $settings['logo_path']) : asset('storage/' . $settings['logo_path']) }}')" @endif>
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
                                        font-size: {{ $prayerStyling['next_prayer_font_size'] ?? '1.4rem' }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $prayerStyling['next_prayer_countdown_font_size'] ?? '1.4rem' }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $prayerStyling['next_prayer_name_font_size'] ?? '0.9rem' }} !important; 
                                        margin-top: 5px; 
                                        opacity: 0.8;
                                        color: {{ $prayerStyling['next_prayer_name_color'] ?? '#666666' }} !important;
                                    ">Calculating...</div>
                                </div>
                            @endif
                        @endif
                        <div class="prayer-list">
                            <div class="prayer-row" data-prayer-name="fajr" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: 3rem; font-weight: bold;">Fajr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: 3rem; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: 3rem;">{{ $prayerTimes->fajr_jamaat ? \Carbon\Carbon::parse($prayerTimes->fajr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->fajr)->addMinutes((int)$settings['fajr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="zohar" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: 3rem; font-weight: bold;">Zohar</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: 3rem; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: 3rem;">{{ $prayerTimes->zohar_jamaat ? \Carbon\Carbon::parse($prayerTimes->zohar_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->zohar)->addMinutes((int)$settings['zohar_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="asr" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: 3rem; font-weight: bold;">Asr</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: 3rem; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->asr)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: 3rem;">{{ $prayerTimes->asr_jamaat ? \Carbon\Carbon::parse($prayerTimes->asr_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->asr)->addMinutes((int)$settings['asr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="maghrib" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: 3rem; font-weight: bold;">Maghrib</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: 3rem; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->maghrib)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: 3rem;">{{ $prayerTimes->maghrib_jamaat ? \Carbon\Carbon::parse($prayerTimes->maghrib_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->maghrib)->addMinutes((int)$settings['maghrib_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row" data-prayer-name="isha" style="display: grid; grid-template-columns: {{ $prayerLayout['column_widths'][0] ?? '45%' }} {{ $prayerLayout['column_widths'][1] ?? '25%' }} {{ $prayerLayout['column_widths'][2] ?? '25%' }}; gap: 10px; margin-bottom: 8px; text-align: center; align-items: center;">
                                <div class="prayer-name" style="text-align: left; font-size: 3rem; font-weight: bold;">Isha</div>
                                <div class="prayer-time" data-time-type="beginning" style="font-size: 3rem; margin-left: -{{ $prayerLayout['beginning_column_spacing'] ?? '0' }}px;">{{ \Carbon\Carbon::parse($prayerTimes->isha)->format('h:i') }}</div>
                                <div class="prayer-jamaat" data-time-type="jamaat" style="font-size: 3rem;">{{ $prayerTimes->isha_jamaat ? \Carbon\Carbon::parse($prayerTimes->isha_jamaat)->format('h:i') : \Carbon\Carbon::parse($prayerTimes->isha)->addMinutes((int)$settings['isha_jamaat_offset'])->format('h:i') }}</div>
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
                                        font-size: {{ $prayerStyling['next_prayer_font_size'] ?? '1.4rem' }} !important;
                                        color: {{ $prayerStyling['next_prayer_text_color'] ?? '#000000' }} !important;
                                    ">Next prayer in:</div>
                                    <div id="next-prayer-countdown" class="next-prayer-countdown" style="
                                        font-size: {{ $prayerStyling['next_prayer_countdown_font_size'] ?? '1.4rem' }} !important; 
                                        font-weight: bold;
                                        color: {{ $prayerStyling['next_prayer_countdown_color'] ?? '#000000' }} !important;
                                    ">--:--:--</div>
                                    <div id="next-prayer-name" class="next-prayer-name" style="
                                        font-size: {{ $prayerStyling['next_prayer_name_font_size'] ?? '0.9rem' }} !important; 
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
                            <div class="next-prayer-info" style="
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
            <div class="col-md-4 p-0">
                @if($useBoxesStyling && isset($boxSettings['announcements_box']))
                    @php
                        $announcementsBox = $boxSettings['announcements_box'] ?? null;
                        $announcementsStyling = $announcementsBox['styling_settings'] ?? [];
                        $announcementsLayout = $announcementsBox['layout_settings'] ?? [];
                    @endphp
                    <div class="announcements-section" style="
                        background-color: {{ $announcementsStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                        color: {{ $announcementsStyling['text_color'] ?? '#000000' }};
                        font-family: {{ $announcementsStyling['font_family'] ?? 'Arial, sans-serif' }};
                        font-size: {{ $announcementsStyling['font_size'] ?? '3rem' }};
                        border: {{ $announcementsStyling['border_width'] ?? '1px' }} solid {{ $announcementsStyling['border_color'] ?? '#0066cc' }};
                        border-radius: 0px;
                        padding: 10px 15px;
                        margin: 0;
                    ">
                        <div class="announcements-header" style="
                            color: {{ $announcementsStyling['title_color'] ?? '#000000' }};
                            font-size: {{ $announcementsStyling['title_font_size'] ?? '3.5rem' }};
                            font-weight: bold;
                            margin-bottom: 15px;
                            text-align: center;
                            margin: 0 0 10px 0;
                        ">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @elseif(!$useBoxesStyling)
                    <div class="announcements-section" style=\"padding: 10px 15px; margin: 0;\">
                        <div class="announcements-header" style=\"font-size: 3.5rem; margin: 0 0 10px 0;\">{{ $announcementsContent['title'] ?? 'Announcements' }}</div>
                @endif
                    <div class="announcements-content" id="announcements-content" style="margin: 0;">
                        @if($announcements->count() > 0)
                            @foreach($announcements as $index => $announcement)
                                <div class="announcement-item rotating-announcement" data-index="{{ $index }}" style="display: none; margin: 0;">
                                    <div class="announcement-title" style="font-weight: bold; margin-bottom: 5px; color: {{ $announcementsStyling['text_color'] ?? '#000000' }}; font-size: 2.5rem;">{{ $announcement->title }}</div>
                                    <div class="announcement-text" style="font-size: 2.5rem; color: {{ $announcementsStyling['text_color'] ?? '#000000' }}; max-height: 180px; overflow: hidden; word-wrap: break-word; margin: 0;">{{ Str::limit($announcement->content, 300, '...') }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="announcement-placeholder">
                                <p>Announcements should be centered in large and clear text.</p>
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
            <div class="board-bottom-times" style="
                background-color: {{ $specialStyling['background_color'] ?? 'rgba(253, 247, 230, 0.9)' }};
                color: {{ $specialStyling['text_color'] ?? '#000000' }};
                font-family: {{ $specialStyling['font_family'] ?? 'Courier New, monospace' }};
                font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }};
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
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[0] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[0] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[1] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[1] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[2] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[2] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[3] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[3] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[4] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[4] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[5] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[5] }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label" style="font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; color: {{ $specialStyling['header_text_color'] ?? '#000000' }};">{{ $tableHeaders[6] }}</div>
                                <div class="time-value" style="color: {{ $specialStyling['text_color'] ?? '#000000' }}; font-size: {{ $specialStyling['font_size'] ?? '2.5rem' }}; font-weight: bold;">{{ $times[6] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$useBoxesStyling)
            <div class="board-bottom-times">
                <div class="row">
                    <div class="col">
                        <div class="additional-times">
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['sehri_ends_title'] ?? 'Sehri Ends' }}</div>
                                <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['sun_rise_title'] ?? 'Sun Rise' }}</div>
                                <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->sun_rise)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['noon_title'] ?? 'Noon' }}</div>
                                <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['jumah_1_title'] ?? 'Jumu\'ah 1' }}</div>
                                <div class="time-value">{{ $prayerTimes && $prayerTimes->jumah_1 ? \Carbon\Carbon::parse($prayerTimes->jumah_1)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['jumah_2_title'] ?? 'Jumu\'ah 2' }}</div>
                                <div class="time-value">{{ $prayerTimes && $prayerTimes->jumah_2 ? \Carbon\Carbon::parse($prayerTimes->jumah_2)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['eid_prayer_1_title'] ?? 'Eid Prayer 1' }}</div>
                                <div class="time-value">{{ $prayerTimes && $prayerTimes->eid_prayer_1 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_1)->format('h:i') : '--:--' }}</div>
                            </div>
                            <div class="time-item">
                                <div class="time-label">{{ $specialTimesContent['eid_prayer_2_title'] ?? 'Eid Prayer 2' }}</div>
                                <div class="time-value">{{ $prayerTimes && $prayerTimes->eid_prayer_2 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_2)->format('h:i') : '--:--' }}</div>
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
            <div class="welcome-box" style="
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
            <div class="scrolling-text-area" style="
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
                                <span class="scroll-item">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                <span class="scroll-item">{{ $slidingText->text }}</span>
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
            <div class="scrolling-text-area">
                <div class="scrolling-content">
                    <div class="scrolling-text">
                        <div class="scroll-wrapper">
                            @if($slidingTexts->count() > 0)
                                @foreach($slidingTexts as $slidingText)
                                <span class="scroll-item">{{ $slidingText->text }}</span>
                                @endforeach
                                @foreach($slidingTexts as $slidingText)
                                <span class="scroll-item">{{ $slidingText->text }}</span>
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
    
    // Function to check if jamaat times have passed and update accordingly
    function checkAndUpdatePrayerTimes() {
        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        const currentSeconds = now.getSeconds();
        const currentTotalSeconds = currentMinutes * 60 + currentSeconds;
        
        // Check each prayer
        const prayers = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];
        
        prayers.forEach(prayerName => {
            if (updatedPrayers.has(prayerName)) {
                return; // Already updated
            }
            
            // Get today's jamaat time for this prayer
            const todayJamaatTime = originalTodayTimes.jamaat[prayerName];
            if (!todayJamaatTime) return;
            
            const jamaatMinutes = timeToMinutes(todayJamaatTime);
            if (jamaatMinutes === null) return;
            
            const jamaatTotalSeconds = jamaatMinutes * 60;
            
            // Check if current time has passed the jamaat time (immediately after it ends)
            // No buffer - update right when jamaat time passes
            if (currentTotalSeconds > jamaatTotalSeconds) {
                // Jamaat time has passed, update to tomorrow
                updatePrayerToTomorrow(prayerName);
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
    // Store current date to detect when midnight passes
    let lastKnownDate = new Date().toLocaleDateString('en-US');
    
    // Update current time display and date every second
    function updateTimeAndDate() {
        const now = new Date();
        const currentDateStr = now.toLocaleDateString('en-US');
        
        // Check if date has changed (midnight has passed)
        if (currentDateStr !== lastKnownDate) {
            console.log('🕐 Midnight detected! Refreshing page to update all dates and prayers...');
            lastKnownDate = currentDateStr;
            
            // Page reload to refresh dates, prayer times, and Hijri calendar
            location.reload();
        }
        
        // Update current time display in real-time (every second)
        const timeElements = document.querySelectorAll('#current-time');
        if (timeElements.length > 0) {
            // Convert to 12-hour format with AM/PM
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            hours = String(hours).padStart(2, '0');
            
            // Format as HH:MM:SS AM/PM
            const timeFormat = `${hours}:${minutes}:${seconds} ${ampm}`;
            
            timeElements.forEach(element => {
                if (element.textContent !== timeFormat) {
                    element.textContent = timeFormat;
                }
            });
        }
    }
    
    // Update time every second
    setInterval(updateTimeAndDate, 1000);
    updateTimeAndDate(); // Initial call
    
    // Calculate time until midnight for page refresh
    function getTimeUntilMidnight() {
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(0, 0, 0, 0);
        
        const timeUntilMidnight = tomorrow - now;
        return timeUntilMidnight;
    }
    
    // Schedule exact page refresh at midnight
    function schedulePageRefreshAtMidnight() {
        const timeUntilMidnight = getTimeUntilMidnight();
        
        console.log(`📅 Page will auto-refresh at midnight (in ${Math.round(timeUntilMidnight / 1000)} seconds)`);
        
        // Schedule refresh for exactly midnight
        setTimeout(() => {
            console.log('🔄 Midnight reached! Refreshing page...');
            location.reload();
        }, timeUntilMidnight);
    }
    
    // Schedule the first midnight refresh
    schedulePageRefreshAtMidnight();

    
    // Countdown timer for next prayer (legacy)
    function updateCountdown() {
        const countdownElement = document.querySelector('.countdown-timer');
        if (!countdownElement) return;
        
        let seconds = parseInt(countdownElement.getAttribute('data-seconds'));
        
        if (seconds <= 0) {
            location.reload(); // Refresh when prayer time is reached
            return;
        }
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;
        
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        countdownElement.textContent = timeString;
        
        countdownElement.setAttribute('data-seconds', seconds - 1);
    }
    
    // Update countdown every second
    setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call
    
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

    // Media Display System
    let currentMedia = null;
    let currentMediaData = null;
    let mediaDisplayTimer = null;
    let countdownTimer = null;
    let mediaCheckInterval = null;
    let lastScheduleId = null;
    let lastMediaId = null;
    let scheduleMediaSequence = new Set(); // Track media within current schedule sequence

    // Initialize media display system
    function initMediaDisplay() {
        checkForMedia();
        // Check for media updates every 3 seconds for responsive sequencing
        mediaCheckInterval = setInterval(checkForMedia, 3000);
    }

    // Check for current media to display
    async function checkForMedia() {
        try {
            const response = await fetch('/api/current-media');
            const data = await response.json();
            
            // If we have media data
            if (data.media) {
                const scheduleId = data.media.schedule_id || 'unknown';
                const mediaId = data.media.id;
                
                // Check if this is a new media or different from what's currently showing
                const isNewMedia = mediaId !== lastMediaId;
                const isDifferentMedia = !currentMedia || currentMedia.id !== mediaId;
                
                if (isNewMedia && isDifferentMedia) {
                    console.log('New media in sequence, displaying:', data.media);
                    lastMediaId = mediaId;
                    displayMedia(data.media);
                }
            } else {
                // No media to display, hide current media if showing
                if (currentMedia) {
                    console.log('No media to display, hiding current');
                    hideMedia();
                }
            }
            
            // Also check for countdown
            checkCountdown();
        } catch (error) {
            console.error('Error checking media:', error);
        }
    }

    // Display media in fullscreen overlay
    function displayMedia(media) {
        console.log('displayMedia called with:', media);
        currentMedia = media;
        currentMediaData = media;
        
        const overlay = document.getElementById('media-overlay');
        const content = document.getElementById('media-content');
        
        // Clear any existing timers
        clearTimeout(mediaDisplayTimer);
        clearTimeout(countdownTimer);
        
        // Hide countdown if showing
        document.getElementById('media-countdown').style.display = 'none';
        
        // Create media element
        let mediaElement;
        if (media.type === 'image') {
            mediaElement = document.createElement('img');
            mediaElement.src = media.file_url;
            mediaElement.alt = media.title;
            mediaElement.style.width = '100%';
            mediaElement.style.height = '100%';
            mediaElement.style.objectFit = 'contain';
            mediaElement.style.display = 'block';
            mediaElement.style.position = 'relative';
            mediaElement.style.zIndex = '1';

            // Add error handling for image loading
            mediaElement.onload = function() {
                console.log('Image loaded successfully:', media.file_url);
                console.log('Image natural dimensions:', this.naturalWidth, 'x', this.naturalHeight);
                console.log('Image display style:', this.style.display);
                console.log('Image visibility:', this.style.visibility);
                console.log('Image opacity:', this.style.opacity);
                console.log('Content children:', content.children.length);
                console.log('Media element tag name:', this.tagName);
                console.log('Image computed display:', window.getComputedStyle(this).display);
                console.log('Image computed width:', window.getComputedStyle(this).width);
                console.log('Image computed height:', window.getComputedStyle(this).height);
                console.log('Content dimensions:', content.offsetWidth, 'x', content.offsetHeight);
            };

            mediaElement.onerror = function() {
                console.error('Failed to load image:', media.file_url);
                console.error('Image natural dimensions:', this.naturalWidth, 'x', this.naturalHeight);
                // Show error message instead of black screen
                content.innerHTML = '<div style="color: white; text-align: center; padding: 20px;">Failed to load image: ' + media.title + '</div>';
            };

            console.log('Loading image from:', media.file_url);
        } else if (media.type === 'video') {
            mediaElement = document.createElement('video');
            mediaElement.src = media.file_url;
            mediaElement.autoplay = true;
            mediaElement.loop = true;
            mediaElement.muted = true;
            mediaElement.style.width = '100%';
            mediaElement.style.height = '100%';
            mediaElement.style.objectFit = 'contain';
        }
        
        // Clear content and add new media
        content.innerHTML = '';
        console.log('Content dimensions before adding image:', content.offsetWidth, 'x', content.offsetHeight);

        // Clear any existing animations
        mediaElement.style.animation = 'none';

        content.appendChild(mediaElement);

        // Force reflow to ensure animation plays
        mediaElement.offsetHeight;

        // Start the slide-in animation
        mediaElement.style.animation = 'slideInFromRight 1s ease-out';

        console.log('Content dimensions after adding image:', content.offsetWidth, 'x', content.offsetHeight);
        console.log('Media element dimensions:', mediaElement.offsetWidth, 'x', mediaElement.offsetHeight);

        // Show overlay
        console.log('Showing media overlay');
        overlay.style.display = 'flex';
        overlay.style.visibility = 'visible';
        overlay.style.opacity = '1';

        // Debug: Check if overlay is actually visible
        console.log('Overlay display style:', overlay.style.display);
        console.log('Overlay computed display:', window.getComputedStyle(overlay).display);
        console.log('Overlay z-index:', window.getComputedStyle(overlay).zIndex);
        console.log('Overlay background:', window.getComputedStyle(overlay).backgroundColor);

        // Skip fullscreen for now - just show the media overlay
        // User can manually click fullscreen button if they want

        // Set timer to hide media after duration
        console.log('Setting timer to hide media after', media.display_duration, 'seconds');
        console.log('Timer ID:', mediaDisplayTimer);
        mediaDisplayTimer = setTimeout(() => {
            console.log('Hiding media after duration');
            hideMedia();
        }, media.display_duration * 1000);

        // Prevent multiple timers
        if (window.mediaDisplayTimer) {
            clearTimeout(window.mediaDisplayTimer);
        }
        window.mediaDisplayTimer = mediaDisplayTimer;

        // Also set a timer to check if the media is still visible after 1 second
        setTimeout(() => {
            console.log('Checking media visibility after 1 second');
            console.log('Overlay display:', overlay.style.display);
            console.log('Overlay visibility:', overlay.style.visibility);
            console.log('Overlay opacity:', overlay.style.opacity);
            console.log('Current media:', currentMedia?.title || 'none');
            console.log('Media element in DOM:', content.contains(mediaElement));
            console.log('Media element visible:', mediaElement.offsetWidth > 0 && mediaElement.offsetHeight > 0);
        }, 1000);

        // Debug: Check media element
        console.log('Media element in content:', content.contains(mediaElement));
        console.log('Media element src:', mediaElement.src);
        console.log('Media element style:', mediaElement.style.cssText);
    }

    // Hide media overlay
    function hideMedia() {
        console.log('hideMedia called, currentMedia:', currentMedia?.title || 'none');
        const overlay = document.getElementById('media-overlay');
        overlay.style.display = 'none';
        currentMedia = null;
        currentMediaData = null;
        clearTimeout(mediaDisplayTimer);
        
        // Immediately check for next media in sequence
        setTimeout(checkForMedia, 500);
    }

    // Check for countdown timer
    async function checkCountdown() {
        try {
            const response = await fetch('/api/countdown-info');
            const data = await response.json();
            
            if (data.countdown && data.countdown.is_countdown_time) {
                showCountdown(data.countdown);
            } else {
                hideCountdown();
            }
        } catch (error) {
            console.error('Error checking countdown:', error);
        }
    }

    // Show countdown timer
    function showCountdown(countdownInfo) {
        // Don't show countdown if media is currently displaying
        if (currentMedia) return;
        
        const overlay = document.getElementById('media-overlay');
        const countdownDiv = document.getElementById('media-countdown');
        const prayerName = document.getElementById('countdown-prayer-name');
        const countdownTime = document.getElementById('countdown-time');
        
        prayerName.textContent = countdownInfo.prayer_name;
        
        // Show overlay
        overlay.style.display = 'flex';
        countdownDiv.style.display = 'flex';
        
        // Skip fullscreen for countdown - just show the overlay
        // User can manually click fullscreen button if they want
        
        // Start countdown timer
        startCountdownTimer(countdownInfo.prayer_time);
    }

    // Start countdown timer
    function startCountdownTimer(prayerTime) {
        const targetTime = new Date(prayerTime).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetTime - now;
            
            if (distance < 0) {
                hideCountdown();
                // Refresh page to update prayer times
                location.reload();
                return;
            }
            
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-time').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        countdownTimer = setInterval(updateCountdown, 1000);
    }

    // Hide countdown
    function hideCountdown() {
        // Don't hide overlay if media is currently displaying
        if (currentMedia) {
            const countdownDiv = document.getElementById('media-countdown');
            countdownDiv.style.display = 'none';
            clearInterval(countdownTimer);
            return;
        }

        const overlay = document.getElementById('media-overlay');
        const countdownDiv = document.getElementById('media-countdown');

        countdownDiv.style.display = 'none';
        overlay.style.display = 'none';

        clearInterval(countdownTimer);
    }

    // Countdown Popup System
    let countdownPopupTimer = null;
    let countdownPopupInterval = null;
    let currentCountdownSeconds = 30;
    
    // Initialize countdown popup system
    function initCountdownPopup() {
        checkForCountdownPopups();
        // Check every second for countdown opportunities
        setInterval(checkForCountdownPopups, 1000);
    }
    
    // Check if we should show countdown popup
    function checkForCountdownPopups() {
        const now = new Date();
        const currentTimeInSeconds = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
        
        // Check all prayer times for countdown opportunities
        const allPrayerTimes = [
            // Adhan times
            { name: 'Fajr Adhan', time: adhanTimesData.fajr_adhan, type: 'adhan' },
            { name: 'Zohar Adhan', time: adhanTimesData.zohar_adhan, type: 'adhan' },
            { name: 'Asr Adhan', time: adhanTimesData.asr_adhan, type: 'adhan' },
            { name: 'Maghrib Adhan', time: adhanTimesData.maghrib_adhan, type: 'adhan' },
            { name: 'Isha Adhan', time: adhanTimesData.isha_adhan, type: 'adhan' },
            // Jamaat times
            { name: 'Fajr Jamaat', time: prayerTimesData.fajr, type: 'jamaat' },
            { name: 'Zohar Jamaat', time: prayerTimesData.zohar, type: 'jamaat' },
            { name: 'Asr Jamaat', time: prayerTimesData.asr, type: 'jamaat' },
            { name: 'Maghrib Jamaat', time: prayerTimesData.maghrib, type: 'jamaat' },
            { name: 'Isha Jamaat', time: prayerTimesData.isha, type: 'jamaat' }
        ];
        
        for (const prayer of allPrayerTimes) {
            if (!prayer.time) continue;
            
            const [hours, minutes] = prayer.time.split(':').map(Number);
            const prayerTimeInSeconds = hours * 3600 + minutes * 60;
            const timeDifferenceInSeconds = prayerTimeInSeconds - currentTimeInSeconds;
            
            // Check if we're within the 30-second window before the prayer time
            // Show countdown when we're between 30 and 1 seconds before the prayer time
            if (timeDifferenceInSeconds > 0 && timeDifferenceInSeconds <= 30) {
                showCountdownPopup(prayer.name, prayer.type, timeDifferenceInSeconds);
                return; // Only show one popup at a time
            }
        }
    }
    
    // Show countdown popup
    function showCountdownPopup(prayerName, type, initialSeconds = 30) {
        // Don't show if already showing or if media is displaying
        if (document.getElementById('countdown-popup').style.display !== 'none' || currentMedia) {
            return;
        }
        
        const popup = document.getElementById('countdown-popup');
        const titleElement = document.getElementById('countdown-popup-title');
        const timerElement = document.getElementById('countdown-popup-timer');
        const prayerElement = document.getElementById('countdown-popup-prayer');
        
        // Set popup content
        titleElement.textContent = type === 'adhan' ? 'Adhan starting in' : 'Jamaat starting in';
        prayerElement.textContent = prayerName;
        timerElement.textContent = initialSeconds;
        
        // Show popup
        popup.style.display = 'flex';
        
        // Start countdown with the actual remaining seconds
        currentCountdownSeconds = initialSeconds;
        countdownPopupInterval = setInterval(() => {
            currentCountdownSeconds--;
            timerElement.textContent = currentCountdownSeconds;
            
            if (currentCountdownSeconds <= 0) {
                hideCountdownPopup();
            }
        }, 1000);
        
        // Auto-hide after the remaining seconds as backup
        countdownPopupTimer = setTimeout(() => {
            hideCountdownPopup();
        }, initialSeconds * 1000);
    }
    
    // Hide countdown popup
    function hideCountdownPopup() {
        const popup = document.getElementById('countdown-popup');
        popup.style.display = 'none';
        
        // Clear timers
        if (countdownPopupTimer) {
            clearTimeout(countdownPopupTimer);
            countdownPopupTimer = null;
        }
        if (countdownPopupInterval) {
            clearInterval(countdownPopupInterval);
            countdownPopupInterval = null;
        }
        
        currentCountdownSeconds = 30;
    }
    

    // Initialize media display when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMediaDisplay();
        initContentRotation();
        initCountdownPopup();
        
        // Reset at midnight each day
        scheduleMidnightReset();
    });

    // Schedule reset at midnight
    function scheduleMidnightReset() {
        const now = new Date();
        const midnight = new Date(now);
        midnight.setHours(24, 0, 0, 0); // Next midnight
        
        const msUntilMidnight = midnight.getTime() - now.getTime();
        
        setTimeout(() => {
            lastScheduleId = null;
            lastMediaId = null;
            scheduleMediaSequence.clear();
            // Schedule the next reset
            scheduleMidnightReset();
        }, msUntilMidnight);
    }

    // Content rotation for hadeeths and announcements
    function initContentRotation() {
        // Rotate hadeeths
        const hadeeths = document.querySelectorAll('.rotating-hadeeth');
        if (hadeeths.length > 1) {
            let currentHadeethIndex = 0;
            setInterval(() => {
                hadeeths[currentHadeethIndex].style.display = 'none';
                currentHadeethIndex = (currentHadeethIndex + 1) % hadeeths.length;
                hadeeths[currentHadeethIndex].style.display = 'block';
            }, 30 * 1000); // Fixed 30 second rotation duration
        }

        // Initialize dynamic announcement layout
        initDynamicAnnouncements();
    }
    
    // Dynamic announcement layout system
    function initDynamicAnnouncements() {
        const announcements = document.querySelectorAll('.rotating-announcement');
        const contentContainer = document.getElementById('announcements-content');
        
        if (announcements.length === 0) return;
        
        // Calculate optimal layout on load and resize
        function calculateOptimalLayout() {
            const containerHeight = contentContainer.offsetHeight;
            const containerPadding = parseFloat(getComputedStyle(contentContainer).paddingTop) + 
                                   parseFloat(getComputedStyle(contentContainer).paddingBottom);
            const availableHeight = containerHeight - containerPadding;
            
            // Get actual gap size from CSS
            const gapSize = parseFloat(getComputedStyle(contentContainer).gap) || 15;
            
            // More accurate height estimation based on content
            let estimatedItemHeight = 60; // Base minimum height
            
            // If we have announcements, measure the first one for better accuracy
            if (announcements.length > 0) {
                const firstAnnouncement = announcements[0];
                firstAnnouncement.style.display = 'block';
                firstAnnouncement.style.visibility = 'hidden';
                firstAnnouncement.style.position = 'absolute';
                firstAnnouncement.style.top = '-9999px';
                
                estimatedItemHeight = firstAnnouncement.offsetHeight;
                
                // Reset styles
                firstAnnouncement.style.display = 'none';
                firstAnnouncement.style.visibility = 'visible';
                firstAnnouncement.style.position = 'static';
                firstAnnouncement.style.top = 'auto';
            }
            
            // Calculate how many announcements can fit
            let maxVisible = Math.floor((availableHeight + gapSize) / (estimatedItemHeight + gapSize));
            maxVisible = Math.max(1, Math.min(maxVisible, announcements.length));
            
            // Apply layout class based on number of announcements and available space
            contentContainer.classList.remove('dynamic-layout', 'centered-layout', 'compact-layout');
            
            if (announcements.length <= 2) {
                contentContainer.classList.add('centered-layout');
            } else if (maxVisible >= announcements.length) {
                contentContainer.classList.add('dynamic-layout');
            } else {
                contentContainer.classList.add('compact-layout');
            }
            
            return maxVisible;
        }
        
        // Show announcements with rotation
        let currentStartIndex = 0;
        let maxVisible = calculateOptimalLayout();
        
        function showAnnouncements() {
            // Hide all announcements first
            announcements.forEach(el => el.style.display = 'none');
            
            // Show the optimal number of announcements
            for (let i = 0; i < maxVisible; i++) {
                const index = (currentStartIndex + i) % announcements.length;
                announcements[index].style.display = 'block';
            }
        }
        
        // Add smooth transitions for better UX
        function addSmoothTransitions() {
            announcements.forEach((el, index) => {
                el.style.transition = 'opacity 0.5s ease, transform 0.3s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
                
                // Stagger the appearance for a nice effect
                setTimeout(() => {
                    if (el.style.display === 'block') {
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }
                }, index * 100);
            });
        }
        
        // Initial display
        showAnnouncements();
        addSmoothTransitions();
        
        // Rotate announcements every 15 seconds
        setInterval(() => {
            if (announcements.length > maxVisible) {
                currentStartIndex = (currentStartIndex + maxVisible) % announcements.length;
                showAnnouncements();
                addSmoothTransitions();
            }
        }, 15000);
        
        // Recalculate on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                maxVisible = calculateOptimalLayout();
                showAnnouncements();
            }, 250);
        });
    }
</script>
@endsection
