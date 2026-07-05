/**
 * Boxes Management JavaScript
 * Enhanced functionality for the boxes management system
 */

class BoxesManager {

    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeColorPickers();
        this.setupPreviewUpdates();
    }

    setupEventListeners() {
        // Real-time preview updates
        document.addEventListener('input', (e) => {
            if (e.target.matches('input, select, textarea')) {
                this.debounce(() => this.updatePreview(e.target), 300);
            }
        });

        // Color picker updates
        document.addEventListener('change', (e) => {
            if (e.target.type === 'color') {
                this.updatePreview(e.target);
            }
        });

        // Form submission with validation
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'boxEditForm') {
                this.validateForm(e);
            }
        });
    }

    initializeColorPickers() {
        // Initialize color pickers with default values
        const colorInputs = document.querySelectorAll('input[type="color"]');
        colorInputs.forEach(input => {
            if (!input.value) {
                input.value = this.getDefaultColor(input.id);
            }
        });
    }

    getDefaultColor(inputId) {
        const defaults = {
            'background_color': '#f5f5dc',
            'text_color': '#000000',
            'border_color': '#0066cc',
            'header_background_color': '#0066cc',
            'header_text_color': '#ffffff',
            'title_background_color': '#1E4D2B',
            'title_color': '#ffffff',
            'accent_color': '#90EE90'
        };
        return defaults[inputId] || '#000000';
    }

    normalizeRemFontSize(value, defaultRem = '1.2rem') {
        const trimmed = String(value ?? '').trim();

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

    parseTitleRemValue(value, fallbackRem = 1.2) {
        const trimmed = String(value ?? '').trim();
        if (!trimmed) {
            return fallbackRem;
        }

        if (/rem$/i.test(trimmed)) {
            const numeric = parseFloat(trimmed);
            return Number.isFinite(numeric) ? numeric : fallbackRem;
        }

        if (/px$/i.test(trimmed)) {
            const numeric = parseFloat(trimmed);
            return Number.isFinite(numeric) ? numeric / 16 : fallbackRem;
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            return parseFloat(trimmed);
        }

        return fallbackRem;
    }

    getPreviewAnnouncementsFontSizes(styling) {
        const titleRem = this.parseTitleRemValue(styling.title_font_size, 1.2);
        const titleScale = Math.min(1, 1.25 / titleRem);
        const previewTitleRem = Math.max(0.85, titleRem * titleScale);

        return {
            title: `${previewTitleRem.toFixed(2)}rem`,
            body: '0.8rem',
            bodyStrong: '0.85rem',
            bodySmall: '0.72rem',
        };
    }

    getPreviewPadding(value, fallbackPx = '12px', maxPx = 16) {
        const trimmed = String(value ?? '').trim();
        const normalized = /^\d+(\.\d+)?$/.test(trimmed) ? `${trimmed}px` : (trimmed || fallbackPx);
        const numeric = parseInt(normalized, 10);
        const safeNumeric = Number.isFinite(numeric) ? numeric : parseInt(fallbackPx, 10);
        return `${Math.min(safeNumeric, maxPx)}px`;
    }

    setupPreviewUpdates() {
        // Auto-refresh preview every 30 seconds
        setInterval(() => {
            this.refreshPreviewFrame();
        }, 30000);

        // Update preview on window focus
        window.addEventListener('focus', () => {
            this.refreshPreviewFrame();
        });
    }

    updatePreview(element) {
        const form = element.closest('form');
        if (!form) return;

        const formData = new FormData(form);
        const data = this.parseFormData(formData);

        // Update live preview
        this.updateLivePreview(data);

        // Send AJAX update if on edit page
        if (window.location.pathname.includes('/edit')) {
            this.sendAjaxUpdate(data);
        }
    }

    parseFormData(formData) {
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.includes('[') && key.includes(']')) {
                // Handle nested arrays like content_settings[key]
                const [parent, child] = key.split('[');
                const childKey = child.replace(']', '');
                
                if (!data[parent]) data[parent] = {};
                data[parent][childKey] = value;
            } else {
                data[key] = value;
            }
        }

        return data;
    }

    updateLivePreview(data) {
        const previewElement = document.getElementById('livePreview');
        if (!previewElement) return;

        // Generate preview HTML based on current box type
        const boxType = this.getCurrentBoxType();
        const previewHTML = this.generatePreviewHTML(data, boxType);
        
        if (previewHTML) {
            previewElement.innerHTML = previewHTML;
        }
    }

    getCurrentBoxType() {
        const path = window.location.pathname;
        const boxesMatch = path.match(/\/boxes\/([^/]+)\/edit/);
        if (boxesMatch) {
            return boxesMatch[1];
        }

        const legacyMatch = path.match(/\/edit\/([^/]+)/);
        return legacyMatch ? legacyMatch[1] : null;
    }

    generatePreviewHTML(data, boxType) {
        const styling = data.styling_settings || {};
        const content = data.content_settings || {};
        
        let styleString = `
            background-color: ${styling.background_color || '#f5f5dc'};
            color: ${styling.text_color || '#000000'};
            font-family: ${styling.font_family || 'Arial, sans-serif'};
            font-size: ${styling.font_size || '16px'};
            border: ${styling.border_width || '1px'} solid ${styling.border_color || '#0066cc'};
            border-radius: ${styling.border_radius || '0px'};
            padding: ${styling.padding || '15px'};
            text-align: ${data.layout_settings?.text_alignment || 'left'};
        `;
        
        switch(boxType) {
            case 'header_box':
                return `
                    <div style="${styleString}">
                        <div style="font-size: ${styling.time_font_size || '48px'}; font-weight: bold;">02:24:13 PM</div>
                        <div style="font-size: ${styling.date_font_size || '18px'}; margin-top: 5px;">Wed 15 Oct 2025</div>
                        <div style="font-size: ${styling.date_font_size || '18px'}; margin-top: 5px;">18 Safar 1447</div>
                        <div style="text-align: right; margin-top: 10px;">
                            <button class="btn btn-sm btn-light">⛶</button>
                        </div>
                    </div>
                `;
                
            case 'prayer_times_box':
                return `
                    <div style="${styleString}">
                        <div style="background-color: ${styling.header_background_color || '#0066cc'}; color: ${styling.header_text_color || '#ffffff'}; padding: 8px; margin: -15px -15px 10px -15px; text-align: center; font-weight: bold; font-size: ${styling.header_font_size || '16px'};">
                            ${content.table_headers?.[0] || 'Prayer'} | ${content.table_headers?.[1] || 'Beginning'} | ${content.table_headers?.[2] || 'Jamaat Time'}
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span>Fajr</span>
                            <span>05:38</span>
                            <span>06:45</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span>Zohar</span>
                            <span>12:58</span>
                            <span>01:30</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px;">
                            <span>Asr</span>
                            <span>04:16</span>
                            <span>05:00</span>
                        </div>
                    </div>
                `;
                
            case 'hadeeth_box':
                return `
                    <div style="${styleString}">
                        <div style="font-weight: bold; margin-bottom: 15px; text-align: center; font-size: ${styling.title_font_size || '20px'}; color: ${styling.title_color || '#000000'};">
                            ${content.title || 'Hadeeth Of The Day'}
                        </div>
                        <div style="font-family: ${styling.arabic_font_family || 'serif'}; font-size: 16px; text-align: center; margin-bottom: 10px;">
                            قَالَ رَسُولُ اللَّهِ صَلَّى اللهُ عَلَيْهِ وَسَلَّمَ
                        </div>
                        <div style="font-size: 14px; text-align: center; margin-bottom: 5px; font-family: ${styling.english_font_family || 'Arial, sans-serif'};">
                            "Actions are but by intention"
                        </div>
                        <div style="font-size: 12px; text-align: center; color: #666;">
                            Sahih Bukhari 1
                        </div>
                    </div>
                `;
                
            case 'announcements_box': {
                const previewFonts = this.getPreviewAnnouncementsFontSizes(styling);
                const previewPadding = this.getPreviewPadding(styling.padding, '12px', 16);
                const titleBackground = styling.title_background_color || '#1E4D2B';
                const titleTextColor = styling.title_color || '#ffffff';
                const titleText = content.title || 'Announcements';

                return `
                    <div class="box-preview">
                        <div style="
                            background-color: ${styling.background_color || '#f5f5dc'};
                            color: ${styling.text_color || '#000000'};
                            font-family: ${styling.font_family || 'Arial, sans-serif'};
                            font-size: ${previewFonts.body};
                            border: ${styling.border_width || '1px'} solid ${styling.border_color || '#0066cc'};
                            border-radius: ${styling.border_radius || '0px'};
                            padding: ${previewPadding};
                            width: 100%;
                            max-width: 100%;
                            box-sizing: border-box;
                            overflow: hidden;
                        ">
                            <div style="
                                font-weight: bold;
                                margin: 0 0 8px 0;
                                padding: 6px 8px;
                                text-align: center;
                                font-size: ${previewFonts.title};
                                line-height: 1.2;
                                background-color: ${titleBackground};
                                color: ${titleTextColor};
                                word-break: break-word;
                                overflow: hidden;
                            ">${titleText}</div>
                            <div style="margin-bottom: 8px; line-height: 1.35;">
                                <strong style="font-size: ${previewFonts.bodyStrong};">Community Iftar</strong><br>
                                <span style="font-size: ${previewFonts.bodySmall};">Community Iftar every evening during Ramadan. All families are welcome to join.</span>
                            </div>
                            <div style="line-height: 1.35;">
                                <strong style="font-size: ${previewFonts.bodyStrong};">Donation Appeal</strong><br>
                                <span style="font-size: ${previewFonts.bodySmall};">Help support our masjid expansion project. Donations are greatly appreciated.</span>
                            </div>
                        </div>
                    </div>
                `;
            }
                
            case 'welcome_box':
                return `
                    <div style="${styleString}">
                        ${content.welcome_text || 'Hello imran Welcome to timetable - Manage your prayer times, announcement'}
                    </div>
                `;
                
            default:
                return `
                    <div style="${styleString}">
                        <div style="text-align: center;">Box Preview</div>
                    </div>
                `;
        }
    }

    sendAjaxUpdate(data) {
        const boxType = this.getCurrentBoxType();
        if (!boxType) return;

        fetch(`/admin/boxes/${boxType}/update-ajax`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                try {
                    localStorage.setItem('timetable-display-sync', String(Date.now()));
                    const channel = new BroadcastChannel('timetable-display');
                    channel.postMessage({ type: 'sync', at: Date.now() });
                    channel.close();
                } catch (error) {
                    // Ignore unsupported browsers.
                }
                this.refreshPreviewFrame();
            } else {
                console.error('Update failed:', result.error);
            }
        })
        .catch(error => {
            console.error('AJAX update error:', error);
        });
    }

    refreshPreviewFrame() {
        const frame = document.getElementById('previewFrame');
        const fullFrame = document.getElementById('fullPreviewFrame');
        
        if (frame) {
            frame.src = frame.src;
        }
        if (fullFrame) {
            fullFrame.src = fullFrame.src;
        }
    }

    validateForm(event) {
        const form = event.target;
        const formData = new FormData(form);
        let isValid = true;
        const errors = [];

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                errors.push(`${field.previousElementSibling?.textContent || field.name} is required`);
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validate font sizes
        const fontSizeFields = form.querySelectorAll('input[name*="font_size"]');
        fontSizeFields.forEach(field => {
            if (field.value && !field.value.match(/^\d+(\.\d+)?(px|em|rem|%)$/)) {
                isValid = false;
                errors.push(`${field.previousElementSibling?.textContent || 'Font size'} must be a valid CSS size (e.g., 16px, 1.2em)`);
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            event.preventDefault();
            this.showErrors(errors);
        }
    }

    showErrors(errors) {
        const errorHtml = errors.map(error => `<li>${error}</li>`).join('');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            <ul class="mb-0">${errorHtml}</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertBefore(errorDiv, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public methods for external use
    static toggleBoxActive(boxType) {
        return fetch(`/admin/boxes/${boxType}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json());
    }

    static resetBox(boxType) {
        if (confirm('Are you sure you want to reset this box to default settings? This will overwrite all current customizations.')) {
            window.location.href = `/admin/boxes/${boxType}/reset`;
        }
    }

    static initializeDefaults() {
        if (confirm('This will create default box settings for all box types. Continue?')) {
            return fetch('/admin/boxes/initialize-defaults', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to initialize defaults'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to initialize defaults');
            });
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new BoxesManager();
});

// Export for global use
window.BoxesManager = BoxesManager;
