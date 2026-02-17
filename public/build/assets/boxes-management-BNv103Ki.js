class a{constructor(){this.init()}init(){this.setupEventListeners(),this.initializeColorPickers(),this.setupPreviewUpdates()}setupEventListeners(){document.addEventListener("input",t=>{t.target.matches("input, select, textarea")&&this.debounce(()=>this.updatePreview(t.target),300)}),document.addEventListener("change",t=>{t.target.type==="color"&&this.updatePreview(t.target)}),document.addEventListener("submit",t=>{t.target.id==="boxEditForm"&&this.validateForm(t)})}initializeColorPickers(){document.querySelectorAll('input[type="color"]').forEach(i=>{i.value||(i.value=this.getDefaultColor(i.id))})}getDefaultColor(t){return{background_color:"#f5f5dc",text_color:"#000000",border_color:"#0066cc",header_background_color:"#0066cc",header_text_color:"#ffffff",title_color:"#000000",accent_color:"#90EE90"}[t]||"#000000"}setupPreviewUpdates(){setInterval(()=>{this.refreshPreviewFrame()},3e4),window.addEventListener("focus",()=>{this.refreshPreviewFrame()})}updatePreview(t){const i=t.closest("form");if(!i)return;const e=new FormData(i),n=this.parseFormData(e);this.updateLivePreview(n),window.location.pathname.includes("/edit")&&this.sendAjaxUpdate(n)}parseFormData(t){const i={};for(let[e,n]of t.entries())if(e.includes("[")&&e.includes("]")){const[o,s]=e.split("["),r=s.replace("]","");i[o]||(i[o]={}),i[o][r]=n}else i[e]=n;return i}updateLivePreview(t){const i=document.getElementById("livePreview");if(!i)return;const e=this.getCurrentBoxType(),n=this.generatePreviewHTML(t,e);n&&(i.innerHTML=n)}getCurrentBoxType(){const i=window.location.pathname.match(/\/edit\/([^\/]+)/);return i?i[1]:null}generatePreviewHTML(t,i){const e=t.styling_settings||{},n=t.content_settings||{};let o=`
            background-color: ${e.background_color||"#f5f5dc"};
            color: ${e.text_color||"#000000"};
            font-family: ${e.font_family||"Arial, sans-serif"};
            font-size: ${e.font_size||"16px"};
            border: ${e.border_width||"1px"} solid ${e.border_color||"#0066cc"};
            border-radius: ${e.border_radius||"0px"};
            padding: ${e.padding||"15px"};
            text-align: ${t.layout_settings?.text_alignment||"left"};
        `;switch(i){case"header_box":return`
                    <div style="${o}">
                        <div style="font-size: ${e.time_font_size||"48px"}; font-weight: bold;">02:24:13 PM</div>
                        <div style="font-size: ${e.date_font_size||"18px"}; margin-top: 5px;">Wed 15 Oct 2025</div>
                        <div style="font-size: ${e.date_font_size||"18px"}; margin-top: 5px;">18 Safar 1447</div>
                        <div style="text-align: right; margin-top: 10px;">
                            <button class="btn btn-sm btn-light">⛶</button>
                        </div>
                    </div>
                `;case"prayer_times_box":return`
                    <div style="${o}">
                        <div style="background-color: ${e.header_background_color||"#0066cc"}; color: ${e.header_text_color||"#ffffff"}; padding: 8px; margin: -15px -15px 10px -15px; text-align: center; font-weight: bold; font-size: ${e.header_font_size||"16px"};">
                            ${n.table_headers?.[0]||"Prayer"} | ${n.table_headers?.[1]||"Beginning"} | ${n.table_headers?.[2]||"Jamaat Time"}
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
                    <div style="${o}">
                        <div style="font-weight: bold; margin-bottom: 15px; text-align: center; font-size: ${e.title_font_size||"20px"}; color: ${e.title_color||"#000000"};">
                            ${n.title||"Hadeeth Of The Day"}
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
                `;case"announcements_box":return`
                    <div style="${o}">
                        <div style="font-weight: bold; margin-bottom: 15px; font-size: ${e.title_font_size||"18px"}; color: ${e.title_color||"#000000"};">
                            ${n.title||"Announcements"}
                        </div>
                        <div style="margin-bottom: 10px;">
                            <strong>Community Iftar</strong><br>
                            <small>Community Iftar every evening during Ramadan. All families are welcome to join.</small>
                        </div>
                        <div>
                            <strong>Donation Appeal</strong><br>
                            <small>Help support our masjid expansion project. Donations are greatly appreciated.</small>
                        </div>
                    </div>
                `;case"welcome_box":return`
                    <div style="${o}">
                        ${n.welcome_text||"Hello imran Welcome to timetable - Manage your prayer times, announcement"}
                    </div>
                `;default:return`
                    <div style="${o}">
                        <div style="text-align: center;">Box Preview</div>
                    </div>
                `}}sendAjaxUpdate(t){const i=this.getCurrentBoxType();i&&fetch(`/admin/boxes/${i}/update-ajax`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")},body:JSON.stringify(t)}).then(e=>e.json()).then(e=>{e.success?this.refreshPreviewFrame():console.error("Update failed:",e.error)}).catch(e=>{console.error("AJAX update error:",e)})}refreshPreviewFrame(){const t=document.getElementById("previewFrame"),i=document.getElementById("fullPreviewFrame");t&&(t.src=t.src),i&&(i.src=i.src)}validateForm(t){const i=t.target;new FormData(i);let e=!0;const n=[];i.querySelectorAll("[required]").forEach(r=>{r.value.trim()?r.classList.remove("is-invalid"):(e=!1,n.push(`${r.previousElementSibling?.textContent||r.name} is required`),r.classList.add("is-invalid"))}),i.querySelectorAll('input[name*="font_size"]').forEach(r=>{r.value&&!r.value.match(/^\d+(\.\d+)?(px|em|rem|%)$/)?(e=!1,n.push(`${r.previousElementSibling?.textContent||"Font size"} must be a valid CSS size (e.g., 16px, 1.2em)`),r.classList.add("is-invalid")):r.classList.remove("is-invalid")}),e||(t.preventDefault(),this.showErrors(n))}showErrors(t){const i=t.map(o=>`<li>${o}</li>`).join(""),e=document.createElement("div");e.className="alert alert-danger alert-dismissible fade show",e.innerHTML=`
            <ul class="mb-0">${i}</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;const n=document.querySelector(".container-fluid");n.insertBefore(e,n.firstChild),setTimeout(()=>{e.remove()},5e3)}debounce(t,i){let e;return function(...o){const s=()=>{clearTimeout(e),t(...o)};clearTimeout(e),e=setTimeout(s,i)}}static toggleBoxActive(t){return fetch(`/admin/boxes/${t}/toggle`,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(i=>i.json())}static resetBox(t){confirm("Are you sure you want to reset this box to default settings? This will overwrite all current customizations.")&&(window.location.href=`/admin/boxes/${t}/reset`)}static initializeDefaults(){if(confirm("This will create default box settings for all box types. Continue?"))return fetch("/admin/boxes/initialize-defaults",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content")}}).then(t=>t.json()).then(t=>{t.success?location.reload():alert("Error: "+(t.error||"Failed to initialize defaults"))}).catch(t=>{console.error("Error:",t),alert("Failed to initialize defaults")})}}document.addEventListener("DOMContentLoaded",function(){new a});window.BoxesManager=a;
