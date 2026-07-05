class a{constructor(){this.init()}init(){this.setupEventListeners(),this.initializeColorPickers(),this.setupPreviewUpdates()}setupEventListeners(){document.addEventListener("input",t=>{t.target.matches("input, select, textarea")&&this.debounce(()=>this.updatePreview(t.target),300)}),document.addEventListener("change",t=>{t.target.type==="color"&&this.updatePreview(t.target)}),document.addEventListener("submit",t=>{t.target.id==="boxEditForm"&&this.validateForm(t)})}initializeColorPickers(){document.querySelectorAll('input[type="color"]').forEach(i=>{i.value||(i.value=this.getDefaultColor(i.id))})}getDefaultColor(t){return{background_color:"#f5f5dc",text_color:"#000000",border_color:"#0066cc",header_background_color:"#0066cc",header_text_color:"#ffffff",title_background_color:"#1E4D2B",title_color:"#ffffff",accent_color:"#90EE90"}[t]||"#000000"}normalizeRemFontSize(t,i="1.2rem"){const e=String(t??"").trim();if(!e)return i;if(/rem$/i.test(e))return e;if(/px$/i.test(e)){const r=parseFloat(e);return Number.isFinite(r)?`${Math.round(r/16*1e3)/1e3}rem`:i}return/^-?\d+(\.\d+)?$/.test(e)?`${Math.round(parseFloat(e)/16*1e3)/1e3}rem`:e}parseTitleRemValue(t,i=1.2){const e=String(t??"").trim();if(!e)return i;if(/rem$/i.test(e)){const r=parseFloat(e);return Number.isFinite(r)?r:i}if(/px$/i.test(e)){const r=parseFloat(e);return Number.isFinite(r)?r/16:i}return/^-?\d+(\.\d+)?$/.test(e)?parseFloat(e):i}getPreviewAnnouncementsFontSizes(t){const i=this.parseTitleRemValue(t.title_font_size,1.2),e=Math.min(1,1.25/i);return{title:`${Math.max(.85,i*e).toFixed(2)}rem`,body:"0.8rem",bodyStrong:"0.85rem",bodySmall:"0.72rem"}}getPreviewPadding(t,i="12px",e=16){const r=String(t??"").trim(),n=/^\d+(\.\d+)?$/.test(r)?`${r}px`:r||i,s=parseInt(n,10),o=Number.isFinite(s)?s:parseInt(i,10);return`${Math.min(o,e)}px`}setupPreviewUpdates(){setInterval(()=>{this.refreshPreviewFrame()},3e4),window.addEventListener("focus",()=>{this.refreshPreviewFrame()})}updatePreview(t){const i=t.closest("form");if(!i)return;const e=new FormData(i),r=this.parseFormData(e);this.updateLivePreview(r),window.location.pathname.includes("/edit")&&this.sendAjaxUpdate(r)}parseFormData(t){const i={};for(let[e,r]of t.entries())if(e.includes("[")&&e.includes("]")){const[n,s]=e.split("["),o=s.replace("]","");i[n]||(i[n]={}),i[n][o]=r}else i[e]=r;return i}updateLivePreview(t){const i=document.getElementById("livePreview");if(!i)return;const e=this.getCurrentBoxType(),r=this.generatePreviewHTML(t,e);r&&(i.innerHTML=r)}getCurrentBoxType(){const t=window.location.pathname,i=t.match(/\/boxes\/([^/]+)\/edit/);if(i)return i[1];const e=t.match(/\/edit\/([^/]+)/);return e?e[1]:null}generatePreviewHTML(t,i){const e=t.styling_settings||{},r=t.content_settings||{};let n=`
            background-color: ${e.background_color||"#f5f5dc"};
            color: ${e.text_color||"#000000"};
            font-family: ${e.font_family||"Arial, sans-serif"};
            font-size: ${e.font_size||"16px"};
            border: ${e.border_width||"1px"} solid ${e.border_color||"#0066cc"};
            border-radius: ${e.border_radius||"0px"};
            padding: ${e.padding||"15px"};
            text-align: ${t.layout_settings?.text_alignment||"left"};
        `;switch(i){case"header_box":return`
                    <div style="${n}">
                        <div style="font-size: ${e.time_font_size||"48px"}; font-weight: bold;">02:24:13 PM</div>
                        <div style="font-size: ${e.date_font_size||"18px"}; margin-top: 5px;">Wed 15 Oct 2025</div>
                        <div style="font-size: ${e.date_font_size||"18px"}; margin-top: 5px;">18 Safar 1447</div>
                        <div style="text-align: right; margin-top: 10px;">
                            <button class="btn btn-sm btn-light">⛶</button>
                        </div>
                    </div>
                `;case"prayer_times_box":return`
                    <div style="${n}">
                        <div style="background-color: ${e.header_background_color||"#0066cc"}; color: ${e.header_text_color||"#ffffff"}; padding: 8px; margin: -15px -15px 10px -15px; text-align: center; font-weight: bold; font-size: ${e.header_font_size||"16px"};">
                            ${r.table_headers?.[0]||"Prayer"} | ${r.table_headers?.[1]||"Beginning"} | ${r.table_headers?.[2]||"Jamaat Time"}
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
                `;case"hadeeth_box":return`
                    <div style="${n}">
                        <div style="font-weight: bold; margin-bottom: 15px; text-align: center; font-size: ${e.title_font_size||"20px"}; color: ${e.title_color||"#000000"};">
                            ${r.title||"Hadeeth Of The Day"}
                        </div>
                        <div style="font-family: ${e.arabic_font_family||"serif"}; font-size: 16px; text-align: center; margin-bottom: 10px;">
                            قَالَ رَسُولُ اللَّهِ صَلَّى اللهُ عَلَيْهِ وَسَلَّمَ
                        </div>
                        <div style="font-size: 14px; text-align: center; margin-bottom: 5px; font-family: ${e.english_font_family||"Arial, sans-serif"};">
                            "Actions are but by intention"
                        </div>
                        <div style="font-size: 12px; text-align: center; color: #666;">
                            Sahih Bukhari 1
                        </div>
                    </div>
                `;case"announcements_box":{const s=this.getPreviewAnnouncementsFontSizes(e),o=this.getPreviewPadding(e.padding,"12px",16),l=e.title_background_color||"#1E4D2B",d=e.title_color||"#ffffff",c=r.title||"Announcements";return`
                    <div class="box-preview">
                        <div style="
                            background-color: ${e.background_color||"#f5f5dc"};
                            color: ${e.text_color||"#000000"};
                            font-family: ${e.font_family||"Arial, sans-serif"};
                            font-size: ${s.body};
                            border: ${e.border_width||"1px"} solid ${e.border_color||"#0066cc"};
                            border-radius: ${e.border_radius||"0px"};
                            padding: ${o};
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
                                font-size: ${s.title};
                                line-height: 1.2;
                                background-color: ${l};
                                color: ${d};
                                word-break: break-word;
                                overflow: hidden;
                            ">${c}</div>
                            <div style="margin-bottom: 8px; line-height: 1.35;">
                                <strong style="font-size: ${s.bodyStrong};">Community Iftar</strong><br>
                                <span style="font-size: ${s.bodySmall};">Community Iftar every evening during Ramadan. All families are welcome to join.</span>
                            </div>
                            <div style="line-height: 1.35;">
                                <strong style="font-size: ${s.bodyStrong};">Donation Appeal</strong><br>
                                <span style="font-size: ${s.bodySmall};">Help support our masjid expansion project. Donations are greatly appreciated.</span>
                            </div>
                        </div>
                    </div>
                `}case"welcome_box":return`
                    <div style="${n}">
                        ${r.welcome_text||"Hello imran Welcome to timetable - Manage your prayer times, announcement"}
                    </div>
                `;default:return`
                    <div style="${n}">
                        <div style="text-align: center;">Box Preview</div>
                    </div>
                `}}sendAjaxUpdate(t){const i=this.getCurrentBoxType();i&&fetch(`/admin/boxes/${i}/update-ajax`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify(t)}).then(e=>e.json()).then(e=>{if(e.success){try{localStorage.setItem("timetable-display-sync",String(Date.now()));const r=new BroadcastChannel("timetable-display");r.postMessage({type:"sync",at:Date.now()}),r.close()}catch{}this.refreshPreviewFrame()}else console.error("Update failed:",e.error)}).catch(e=>{console.error("AJAX update error:",e)})}refreshPreviewFrame(){const t=document.getElementById("previewFrame"),i=document.getElementById("fullPreviewFrame");t&&(t.src=t.src),i&&(i.src=i.src)}validateForm(t){const i=t.target;new FormData(i);let e=!0;const r=[];i.querySelectorAll("[required]").forEach(o=>{o.value.trim()?o.classList.remove("is-invalid"):(e=!1,r.push(`${o.previousElementSibling?.textContent||o.name} is required`),o.classList.add("is-invalid"))}),i.querySelectorAll('input[name*="font_size"]').forEach(o=>{o.value&&!o.value.match(/^\d+(\.\d+)?(px|em|rem|%)$/)?(e=!1,r.push(`${o.previousElementSibling?.textContent||"Font size"} must be a valid CSS size (e.g., 16px, 1.2em)`),o.classList.add("is-invalid")):o.classList.remove("is-invalid")}),e||(t.preventDefault(),this.showErrors(r))}showErrors(t){const i=t.map(n=>`<li>${n}</li>`).join(""),e=document.createElement("div");e.className="alert alert-danger alert-dismissible fade show",e.innerHTML=`
            <ul class="mb-0">${i}</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;const r=document.querySelector(".container-fluid");r.insertBefore(e,r.firstChild),setTimeout(()=>{e.remove()},5e3)}debounce(t,i){let e;return function(...n){const s=()=>{clearTimeout(e),t(...n)};clearTimeout(e),e=setTimeout(s,i)}}static toggleBoxActive(t){return fetch(`/admin/boxes/${t}/toggle`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(i=>i.json())}static resetBox(t){confirm("Are you sure you want to reset this box to default settings? This will overwrite all current customizations.")&&(window.location.href=`/admin/boxes/${t}/reset`)}static initializeDefaults(){if(confirm("This will create default box settings for all box types. Continue?"))return fetch("/admin/boxes/initialize-defaults",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(t=>t.json()).then(t=>{t.success?location.reload():alert("Error: "+(t.error||"Failed to initialize defaults"))}).catch(t=>{console.error("Error:",t),alert("Failed to initialize defaults")})}}document.addEventListener("DOMContentLoaded",function(){new a});window.BoxesManager=a;
