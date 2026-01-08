const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["assets/html2pdf-ByeQZPjk.js","assets/app-CkgTaj06.js","assets/app-CHeiAsE9.css","assets/jspdf.es.min-DqmIK_-L.js"])))=>i.map(i=>d[i]);
import{z as E,_ as S,c as C,w as k,e as p,f as b,o as d,a as i,n as _,h as v,t as n,d as L,b as D,T as A,F as x,j as N}from"./app-CkgTaj06.js";import{_ as P}from"./_plugin-vue_export-helper-DlAUqK2U.js";const M=E({name:"CompletedBooking",props:{show:{type:Boolean,default:!1},loading:{type:Boolean,default:!1},booking:{type:Object,default:null},sale:{type:Object,default:null},sales:{type:Array,default:()=>[]},summary:{type:Object,default:null},currencySymbol:{type:String,default:"LKR"}},emits:["close","rebook","edit-sales"],data(){return{activeTab:"details",moreMenuOpen:!1}},beforeUnmount(){document.removeEventListener("click",this.handleClickOutside)},computed:{servicesSubtotal(){return this.services.reduce((t,e)=>t+(Number(e.baseTotal)||0),0)},servicesDiscounts(){return this.services.reduce((t,e)=>t+(Number(e.discountTotal)||0),0)},servicesTotalAfterDiscount(){return Math.max(0,this.servicesSubtotal-this.servicesDiscounts)},primarySale(){const t=this.sale;if(t&&Object.keys(t).length)return t;const e=Array.isArray(this.sales)?this.sales:[];return e.length?e[e.length-1]:null},client(){return(this.booking||{}).client||null},clientName(){const t=this.booking||{},e=this.client;if(e){const s=e.first_name??e.firstName??"",m=e.last_name??e.lastName??"";let l=`${s} ${m}`.trim();if(l||(l=e.name??e.full_name??""),l)return l}const o=this.$page?.props||{},a=Array.isArray(o.clients)?o.clients:[],r=e?.id??t.client_id??null;if(r&&a.length){const s=a.find(m=>String(m.id)===String(r));if(s?.name)return s.name}const c=e?.email??t.client_email??"";return c||"Walk-in"},clientEmail(){return this.client?.email||""},clientPhone(){return this.client?.phone||this.client?.phone_number||this.client?.mobile||""},clientNote(){return this.client?.note||this.client?.notes||""},clientInitials(){return(this.clientName||"").trim().split(/\s+/).map(o=>o[0]?.toUpperCase()).join("").slice(0,2)||"C"},bookingStaffName(){return(this.booking||{}).staff?.name||""},headerDateLabel(){const t=this.primarySale||this.booking||{},e=t.created_at||t.date_formatted||t.date||t.starts_at||t.startsAt;if(!e)return"";const o=typeof e=="string"?e.replace(" ","T"):String(e),a=new Date(o);return Number.isNaN(a.getTime())?"":a.toLocaleDateString(void 0,{weekday:"short",day:"2-digit",month:"short",year:"numeric"})},canEditSales(){return!!(this.$page?.props?.permission||{})["sales.edit"]},services(){const t=this.booking||{};return(Array.isArray(t.services)?t.services:[]).map(o=>{const a=Math.max(0,Number(o.price??0)||0),r=Math.max(0,Number(o.final_price??a)||0),c=Math.max(0,a-r),s=Number(o.duration_minutes??o.duration??0)+Number(o.extra_minutes??0);let m="";if(s>0)if(s>=60){const l=Math.floor(s/60),f=s%60;m=l+"h"+(f?" "+f+"min":"")}else m=s+"min";return{id:o.id,label:o.label||"Service",durationLabel:m,staffName:o.staff?.name||o.staff_name||"",baseTotal:a,discountTotal:c,finalTotal:r,discount_type:o.discount_type||"",discount_value:o.discount_value??null}})},subtotal(){return this.primarySale&&this.primarySale.base_amount!=null?Number(this.primarySale.base_amount)||0:this.services.length?this.services.reduce((t,e)=>t+(Number(e.total)||0),0):this.summary&&this.summary.total_price!=null&&Number(this.summary.total_price)||0},tipAmount(){return this.primarySale&&this.primarySale.tip_amount!=null&&Number(this.primarySale.tip_amount)||0},totalWithTip(){return this.primarySale&&this.primarySale.total_with_tip!=null?Number(this.primarySale.total_with_tip)||0:this.subtotal+this.tipAmount},totalPaid(){return this.primarySale&&this.primarySale.total_paid!=null?Number(this.primarySale.total_paid)||0:this.summary&&this.summary.total_paid!=null?Number(this.summary.total_paid)||0:this.totalWithTip},paymentMethodLabel(){const t=this.primarySale?.payment_method||this.summary?.payment_method||"",e=String(t).toLowerCase();return{cash:"Cash",card:"Card",split:"Split",other:"Other"}[e]||"Cash"},paidAtLabel(){const t=this.primarySale?.created_at;if(!t)return"";const e=String(t).replace(" ","T"),o=new Date(e);if(Number.isNaN(o.getTime()))return"";const a=o.toLocaleDateString(void 0,{weekday:"short",day:"2-digit",month:"short",year:"numeric"}),r=o.toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"});return`${a} at ${r}`},saleNumberLabel(){return this.primarySale&&this.primarySale.id?`Sale #${this.primarySale.id}`:"Sale"},canRebook(){return!!(this.booking&&this.booking.id)},activityItems(){const t=Array.isArray(this.sales)?this.sales:[],e=t.length?t:this.primarySale?[this.primarySale]:[],o=this.$page?.props||{},a=Array.isArray(o.staff)?o.staff:[],r=s=>{const m=s?.approved_by_name||s?.approvedBy?.name||s?.approved_by?.name||s?.approved_by_staff?.name||s?.completed_by_name||s?.completedBy?.name||s?.staff?.name||s?.user?.name||"";if(m)return m;const l=s?.approved_by_id||s?.approved_by||s?.approvedById||null;if(l&&a.length){const f=a.find(h=>String(h.id)===String(l));if(f?.name)return f.name}return this.bookingStaffName||""},c=s=>s?.source||s?.via||s?.origin||s?.channel||(s?.is_webhook?"webhook":"")||"";return e.map(s=>{const m=s.created_at?new Date(String(s.created_at).replace(" ","T")):null,l=m?m.toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"}):"",f=s?.status??this.summary?.status??this.booking?.status??"completed",h=String(f).toLowerCase(),u=h.replace(/_/g," ").replace(/\b\w/g,$=>$.toUpperCase()),y=r(s),g=c(s),T=y||g?`${h==="approved"?"Approved by":h==="completed"?"Completed by":"Updated by"} ${y||"—"}${g?" | "+g:""}`:"";return{id:s.id,title:`Sale ${s.id} created`,subtitle:l?`Today at ${l}`:"",meta:`Status: ${u}`,byline:T}})},currentMonth(){return new Date().toLocaleDateString(void 0,{month:"long"})}},watch:{show(t){t&&(this.activeTab="details",this.moreMenuOpen=!1)}},methods:{async openReceiptPrintDialog(t){const e=this.buildExportHtml(),o=`<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${t}</title>
  <style>
    /* helps print background colors */
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  </style>
</head>
<body>
  ${e}
</body>
</html>`,a=window.open("","_blank","width=900,height=650");if(!a){alert("Popup blocked. Please allow popups for printing.");return}a.document.open(),a.document.write(o),a.document.close();const r=()=>Promise.all(Array.from(a.document.images).map(c=>c.complete?Promise.resolve(!0):new Promise(s=>{c.onload=()=>s(!0),c.onerror=()=>s(!0)})));try{await r(),a.document.fonts?.ready&&await a.document.fonts.ready}catch{}a.focus(),a.print(),a.addEventListener("afterprint",()=>a.close(),{once:!0})},toggleMoreMenu(){this.moreMenuOpen=!this.moreMenuOpen,this.moreMenuOpen?this.$nextTick(()=>{document.addEventListener("click",this.handleClickOutside)}):document.removeEventListener("click",this.handleClickOutside)},closeMoreMenu(){this.moreMenuOpen=!1,document.removeEventListener("click",this.handleClickOutside)},formatAmount(t){if(t==null)return"0";const e=typeof t=="number"?t:Number(String(t).replace(/[^\d.-]/g,""));return Number.isNaN(e)?String(t):e.toLocaleString(void 0,{minimumFractionDigits:0,maximumFractionDigits:0})},async onDownloadPdf(){this.closeMoreMenu();const t=this.primarySale?.id,e=t?`Invoice-${t}.pdf`:"Invoice.pdf";try{const{default:o}=await S(async()=>{const{default:l}=await import("./html2pdf-ByeQZPjk.js").then(f=>f.h);return{default:l}},__vite__mapDeps([0,1,2,3])),a=document.createElement("div");a.style.position="fixed",a.style.left="-99999px",a.style.top="0",a.style.width="210mm",a.style.background="#ffffff";let r=this.buildExportHtml();r=this.cleanOklchColors(r),a.innerHTML=r,document.body.appendChild(a),this.removeAllOklchReferences(a);const c=a.querySelectorAll("img");await Promise.all(Array.from(c).map(l=>(l.src&&!l.src.startsWith("http")&&(l.src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png"),l.complete?Promise.resolve():new Promise(f=>{l.onload=()=>{console.log("Image loaded:",l.src),f(!0)},l.onerror=h=>{console.warn("Image failed to load:",l.src,h),f(!0)}})))),await new Promise(l=>setTimeout(l,300));const s=a.querySelector(".invoice-scope");if(!s)throw new Error("Invoice element not found");const m=s.style.width;s.style.width="100%",s.style.maxWidth="none",await o().set({filename:e,margin:[10,10,10,10],html2canvas:{scale:2,backgroundColor:"#ffffff",useCORS:!0,logging:!0,letterRendering:!0,allowTaint:!0,onclone:l=>{this.removeAllOklchReferences(l),l.querySelectorAll("img").forEach(u=>{u.src&&!u.src.startsWith("http")&&(u.src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png"),u.crossOrigin="anonymous"});const h=l.querySelector(".invoice-scope");if(h){h.style.padding="0",h.style.margin="0",h.style.width="100%",h.style.maxWidth="none";const u=h.querySelector(".footer");u&&(u.style.position="absolute",u.style.bottom="0")}}},jsPDF:{unit:"mm",format:"a4",orientation:"portrait",compress:!0}}).from(s).save(),s.style.width=m,a.remove()}catch(o){console.error("Download PDF failed",o),alert(o?.message||"Could not download the PDF.")}finally{this.cleanupAfterPdf()}},cleanOklchColors(t){const e=[{pattern:/oklch\([^)]*0\.97[^)]*\)/g,replacement:"#f3f4f6"},{pattern:/oklch\([^)]*0\.96[^)]*\)/g,replacement:"#f8fafc"},{pattern:/oklch\([^)]*0\.92[^)]*\)/g,replacement:"#f1f5f9"},{pattern:/oklch\([^)]*0\.87[^)]*\)/g,replacement:"#e2e8f0"},{pattern:/oklch\([^)]*0\.71[^)]*\)/g,replacement:"#94a3b8"},{pattern:/oklch\([^)]*0\.24[^)]*\)/g,replacement:"#374151"},{pattern:/oklch\([^)]*0\.14[^)]*\)/g,replacement:"#111827"},{pattern:/oklch\([^)]*0\.58[^)]*27\.325[^)]*\)/g,replacement:"#dc2626"},{pattern:/oklch\([^)]*0\.28[^)]*256\.847[^)]*\)/g,replacement:"#3b82f6"},{pattern:/oklch\([^)]*0\.65[^)]*142\.5[^)]*\)/g,replacement:"#10b981"},{pattern:/oklch\([^)]*0\.62[^)]*241\.997[^)]*\)/g,replacement:"#8b5cf6"},{pattern:/oklch\([^)]+\)/g,replacement:"#111827"},{pattern:/var\([^)]*oklch[^)]*\)/g,replacement:"#111827"}];let o=t;return e.forEach(({pattern:a,replacement:r})=>{o=o.replace(a,r)}),o=o.replace(/class="[^"]*oklch[^"]*"/g,'class=""'),o},removeAllOklchReferences(t){if(!t)return;t.querySelectorAll("[style]").forEach(r=>{const c=r.getAttribute("style");if(c&&c.includes("oklch")){const s=c.replace(/oklch\([^)]+\)/g,"#111827");r.setAttribute("style",s)}}),t.querySelectorAll("style").forEach(r=>{r.textContent.includes("oklch")&&(r.textContent=this.cleanOklchColors(r.textContent))}),t.querySelectorAll("*").forEach(r=>{for(const c of r.attributes)c.value&&c.value.includes("oklch")&&r.removeAttribute(c.name)})},onEditSales(){this.closeMoreMenu();const t=this.primarySale&&this.primarySale.id?this.primarySale.id:null;t&&this.$emit("edit-sales",t)},buildExportHtml(){const t=this.currencySymbol,e=u=>{const y=typeof u=="number"?u:Number(String(u).replace(/[^\d.-]/g,""));return Number.isNaN(y)?"0.00":y.toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2})},o=this.primarySale&&this.primarySale.invoice_number?this.primarySale.invoice_number:`IN${String(this.primarySale?.id||"001").padStart(3,"0")}`,a=this.booking?.id||"",r=this.bookingStaffName||"Staff",c=this.primarySale?.created_at?new Date(String(this.primarySale.created_at).replace(" ","T")).toLocaleDateString(void 0,{month:"short",day:"2-digit",year:"numeric"}):this.headerDateLabel,s=this.primarySale?.created_at?new Date(String(this.primarySale.created_at).replace(" ","T")).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"}):"",m=this.primarySale?.tax_amount||this.primarySale?.tax||0,l=this.servicesDiscounts||0,f=r,h=this.services.map(u=>{const y=u.label||"Service",g=u.staffName||f,w=u.finalTotal||0;return`
            <tr>
                <td>${y}</td>
                <td class="center nowrap">${g}</td>
                <td class="right nowrap">${e(w)}</td>
            </tr>
        `}).join("");return`
<style data-receipt-style="1">
    /* DOMPDF-safe styling (avoid flex/grid; use tables + block layout) */
    @page { margin: 18mm 18mm 18mm 18mm; }
    
    body, .invoice-scope, .invoice-scope * {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
        font-size: 12px;
        color: #111827 !important;
        margin: 0;
        padding: 0;
        background: #ffffff !important;
        box-sizing: border-box;
    }
    
    /* Layout helpers */
    .invoice-scope table { width: 100%; border-collapse: collapse; }
    .invoice-scope .right { text-align: right; }
    .invoice-scope .center { text-align: center; }
    .invoice-scope .nowrap { white-space: nowrap; }
    
    /* Header */
    .invoice-scope .header td { vertical-align: top; }
    .invoice-scope .title {
        font-size: 34px;
        font-weight: 700;
        margin: 0 0 6px 0;
        letter-spacing: -0.02em;
        color: #111827;
    }
    
    /* Meta (Invoice number + Booking id) */
    .invoice-scope .meta-table { width: auto; border-collapse: collapse; }
    .invoice-scope .meta-table td { padding: 2px 0; }
    .invoice-scope .meta-label {
        width: 115px;
        color: #9ca3af;
        font-size: 12px;
    }
    .invoice-scope .meta-value {
        color: #111827;
        font-weight: 700;
        font-size: 12px;
        padding-left: 18px;
        white-space: nowrap;
    }
    
    .invoice-scope .logo-wrap { text-align: right !important;
    vertical-align: top !important; }
    .invoice-scope .logo {
        width: 84px !important;
        height: 84px !important;
        object-fit: contain !important;
        display: block !important;
        margin-left: auto !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Cards */
    .invoice-scope .card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }
    .invoice-scope .card-pad { padding: 14px 16px; }
    
    .invoice-scope .two-col td { vertical-align: top; padding: 14px 16px; }
    .invoice-scope .two-col .sep { width: 1px; padding: 0; background: #e5e7eb; }
    
    .invoice-scope .small-label {
        font-size: 12px;
        color: #9ca3af;
        margin: 0 0 6px 0;
    }
    .invoice-scope .strong { font-weight: 700; color: #111827; line-height: 1.4; }
    .invoice-scope .muted { color: #6b7280; }

    .invoice-scope td .strong {
    display: block;
    line-height: 1.4;
    margin-bottom: 2px;
}
.invoice-scope .two-col td { 
    vertical-align: top; 
    padding: 16px 18px; /* Increased padding slightly */
}
    
    /* Services table box */
    .invoice-scope .box {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .invoice-scope .svc thead th {
        background: #f3f4f6 !important;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    .invoice-scope .svc thead th.center { text-align: center; }
    .invoice-scope .svc thead th.right { text-align: right; }
    
    .invoice-scope .svc tbody td {
        padding: 12px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }
    .invoice-scope .svc tbody tr:last-child td { border-bottom: none; }

        /* Add this important rule to catch any stray oklch */
    *[style*="oklch"], *[class*="oklch"] {
        background-color: transparent !important;
        color: inherit !important;
    }
    
    /* Column alignment */
    .invoice-scope .col-service { width: 55%; }
    .invoice-scope .col-staff { width: 25%; }
    .invoice-scope .col-total { width: 20%; }
    
    /* Totals (right card) */
    .invoice-scope .totals-wrap { width: 100%; margin-top: 16px; }
    .invoice-scope .totals-table td { padding: 7px 0; }
    .invoice-scope .totals-table .label { color: #6b7280; }
    .invoice-scope .totals-table .val { text-align: right; white-space: nowrap; }
    .invoice-scope .totals-divider { border-top: 1px solid #e5e7eb; margin: 12px 0; }
    .invoice-scope .grand { font-weight: 700; font-size: 13px; }
    
    .invoice-scope .notes-rule { border-top: 1px solid #e5e7eb; margin: 6px 0 12px 0; }
    
    /* Footer bar */
    .invoice-scope .footer {
        position:fixed;
        left: -18mm;
        right: -18mm;
        bottom: 0mm;
        height: 20mm;
        background: #c45b3a !important;
        color: #ffffff !important;
        font-size: 13px;
        line-height: 14mm;
    }
    .invoice-scope .footer table { width: 100%; height: 14mm; border-collapse: collapse; }
    .invoice-scope .footer td { padding: 0 18mm; vertical-align: middle; line-height: 14mm; }
    .invoice-scope .footer .right { text-align: right; }

        /* CRITICAL FIX: Ensure logo is never hidden */
    div[style*="position: fixed"][style*="left: -9999px"] {
        display: none !important;
    }
    
    .invoice-scope img[src*="Asset%202%201.png"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        max-width: 100% !important;
        height: auto !important;
    }
</style>

<div class="invoice-scope">
    <!-- Logo URL - update with your actual logo -->
    <div style="position: fixed; left: -9999px;">
        <img src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png" 
             onload="this.parentNode.remove()" />
    </div>

    <!-- HEADER -->
    <table class="header">
        <tr>
            <td style="width: 82%;">
                <div class="title">Invoice</div>
                
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Invoice Number</td>
                        <td class="meta-value">${o}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Booking Id</td>
                        <td class="meta-value">${a}</td>
                    </tr>
                </table>
            </td>
            
            <td class="logo-wrap" style="width: 28%;">
                <img class="logo" 
                     src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png" 
                     alt="Logo">
            </td>
        </tr>
    </table>
    
    <div style="height: 18px;"></div>
    
    <!-- BILLED TO / ISSUED BY -->
    <div class="card">
        <table class="two-col">
            <tr>
                <td style="width: 58%;">
                    <div class="small-label">Billed To</div>
                    <div class="strong" style="margin-bottom: 6px;">${this.clientName}</div>
                    ${this.clientEmail?`<div style="margin-bottom: 3px;">${this.clientEmail}</div>`:""}
                    ${this.clientPhone?`<div>${this.clientPhone}</div>`:""}
                </td>
                
                <td class="sep"></td>
                
                <td style="width: 42%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="small-label" style="padding:5px; width: 70px;">Issued By</td>
                            <td style="padding:5px; " class="strong">${r}</td>
                        </tr>
                        <tr><td style="height: 12px;"></td><td></td></tr>
                        <tr>
                            <td class="small-label" style="padding:5px; width: 70px; vertical-align: top;">Created</td>
                            <td style="padding:5px;">
                                <div class="strong" style="line-height: 1.4; margin-bottom: 3px;">${c}</div>
                                <div class="strong" style="line-height: 1.4;">${s}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="height: 16px;"></div>
    
    <!-- SERVICES TABLE -->
    <div class="box">
        <table class="svc">
            <thead>
                <tr>
                    <th class="col-service">Service</th>
                    <th class="col-staff center nowrap">Staff</th>
                    <th class="col-total right nowrap">Total (${t})</th>
                </tr>
            </thead>
            <tbody>
                ${h||'<tr><td class="muted" colspan="3">—</td></tr>'}
            </tbody>
        </table>
    </div>
    
    <!-- TOTALS (RIGHT) -->
    <div class="totals-wrap">
        <table>
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <div class="card card-pad">
                        <table class="totals-table">
                            <tr>
                                <td class="label">Subtotal</td>
                                <td class="val">${t} ${e(this.subtotal)}</td>
                            </tr>
                            ${l>0?`
                            <tr>
                                <td class="label">Discount</td>
                                <td class="val">-${t} ${e(l)}</td>
                            </tr>
                            `:""}
                            ${m>0?`
                            <tr>
                                <td class="label">Tax</td>
                                <td class="val">${t} ${e(m)}</td>
                            </tr>
                            `:""}
                            ${this.tipAmount>0?`
                            <tr>
                                <td class="label">Tip</td>
                                <td class="val">${t} ${e(this.tipAmount)}</td>
                            </tr>
                            `:""}
                        </table>
                        
                        <div class="totals-divider"></div>
                        
                        <table>
                            <tr>
                                <td class="grand">Total</td>
                                <td class="right nowrap grand">${t} ${e(this.totalWithTip)}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    

    
    <!-- FOOTER BAR -->
    <div class="footer">
        <table>
            <tr>
                <td class="nowrap"></td>
                <td class="right nowrap"></td>
            </tr>
        </table>
    </div>
</div>
`},cleanupAfterPdf(){try{document.querySelectorAll(".html2canvas-container").forEach(t=>t.parentNode?.removeChild(t)),document.querySelectorAll(".html2pdf__overlay, .html2pdf__progress").forEach(t=>t.parentNode?.removeChild(t)),document.querySelectorAll(".modal-backdrop").forEach(t=>t.parentNode?.removeChild(t)),document.body.classList.remove("modal-open"),document.body.style.pointerEvents=""}catch(t){console.warn("cleanupAfterPdf failed",t)}},handleClickOutside(t){const e=this.$el.querySelector('button[aria-label="More options"]')||this.$el.querySelector("button:has(.bx-dots-vertical-rounded)"),o=this.$refs.moreDropdown;this.moreMenuOpen&&o&&!o.contains(t.target)&&e&&!e.contains(t.target)&&this.closeMoreMenu()},async onExportPdf(){this.closeMoreMenu();const t=this.primarySale?.id,e=t?`Sale-${t}`:"Sale";await this.openReceiptPrintDialog(e)},async onExportExcel(){this.closeMoreMenu();try{const t=await S(()=>import("./xlsx-BvJTHLik.js"),[]),e=t.utils.book_new(),o=[];o.push(["Sale",this.saleNumberLabel]),o.push(["Date",this.headerDateLabel]),o.push(["Client",this.clientName]),this.clientEmail&&o.push(["Email",this.clientEmail]),this.clientPhone&&o.push(["Phone",this.clientPhone]),o.push([]),o.push(["#","Service","Duration","Staff","Total"]),this.services.forEach((c,s)=>{o.push([s+1,c.label||"",c.durationLabel||"",c.staffName||"",Number(c.total)||0])}),o.push([]),o.push(["","","","Subtotal",this.subtotal]),this.tipAmount>0&&o.push(["","","","Tip",this.tipAmount]),o.push(["","","","Total",this.totalWithTip]),o.push(["","","","Total Paid",this.totalPaid]);const a=t.utils.aoa_to_sheet(o);t.utils.book_append_sheet(e,a,"Sale");const r=(this.primarySale&&this.primarySale.id?`Sale-${this.primarySale.id}`:"sale")+".xlsx";t.writeFile(e,r)}catch(t){console.error("Failed to export Excel",t),alert('Excel export requires the "xlsx" package. Please install it first.')}}}}),I={key:0,class:"fixed inset-0 z-[260] flex"},O={class:"relative flex h-full w-full max-w-2xl flex-col bg-white"},j={class:"flex flex-1 overflow-hidden"},B={class:"w-32 border-r bg-neutral-50 border-t border-neutral-200"},R={class:"flex-1 flex flex-col overflow-hidden"},F={class:"border-neutral-200 px-6 py-4"},q={class:"flex items-start justify-between"},z={class:"flex flex-col"},W={class:"flex flex-col"},H={class:"text-[11px] md:text-xs text-neutral-500 mt-0.5"},U={key:0},V={class:"flex items-center gap-2"},G={class:"relative"},K={key:0,ref:"moreDropdown",class:"absolute right-0 mt-2 w-44 rounded-2xl border border-neutral-200 bg-white shadow-xl z-[300]"},X={class:"flex-1 overflow-y-auto px-6 py-5"},Y={key:0,class:"py-16 text-center text-sm text-neutral-500"},J={key:0},Q={key:1,class:"space-y-4"},Z={class:"flex items-center gap-3 rounded-2xl border border-neutral-200 bg-white px-4 py-3"},tt={class:"flex h-10 w-10 items-center justify-center rounded-full bg-neutral-900 text-base font-semibold text-white"},et={class:"min-w-0"},st={class:"truncate text-[15px] md:text-base font-semibold text-neutral-900"},it={class:"mt-0.5 space-y-0.5 text-[11px] md:text-xs text-neutral-500"},ot={key:0,class:"truncate"},at={key:1,class:"truncate"},rt={key:2,class:"truncate"},nt={class:"space-y-4 rounded-2xl border border-neutral-200 bg-white px-5 py-4"},lt={class:"flex items-baseline justify-between"},ct={class:"text-[15px] md:text-base font-semibold"},dt={class:"text-[11px] md:text-xs text-neutral-500"},pt={key:0,class:"space-y-3 border-t border-neutral-100 pt-3"},mt={class:"min-w-0"},ut={class:"truncate text-[14px] md:text-[15px] font-medium text-neutral-900"},ht={class:"text-[11px] md:text-xs text-neutral-500"},bt={key:0},ft={key:1},yt={key:0},gt={class:"flex flex-col items-end shrink-0 text-right"},vt={class:"whitespace-nowrap text-sm md:text-[15px] font-semibold tabular-nums"},xt={key:0,class:"mt-0.5 text-[11px] text-rose-600 whitespace-nowrap"},wt={class:"space-y-2 border-t border-neutral-100 pt-4"},St={class:"flex justify-between"},kt={class:"tabular-nums"},_t={key:0,class:"flex justify-between"},At={class:"tabular-nums"},Nt={key:1,class:"flex justify-between"},Tt={class:"tabular-nums"},$t={class:"flex justify-between text-[13px] md:text-[15px] font-semibold"},Et={class:"tabular-nums text-neutral-900"},Ct={class:"mt-3 border-t border-neutral-100 pt-3 text-[11px] md:text-xs"},Lt={class:"flex justify-between"},Dt={class:"text-neutral-500"},Pt={class:"tabular-nums text-sm md:text-[15px] font-semibold text-neutral-900"},Mt={class:"mt-1 text-[11px] md:text-xs text-neutral-500"},It={key:2,class:"space-y-5"},Ot={class:"space-y-1"},jt={class:"text-xs font-semibold uppercase tracking-wide text-neutral-500"},Bt={key:0,class:"space-y-4"},Rt={class:"flex-1 rounded-xl border border-neutral-200 bg-white px-4 py-3"},Ft={class:"text-sm font-semibold"},qt={class:"text-xs text-neutral-500"},zt={key:0,class:"mt-1 text-[11px] text-neutral-500"},Wt={key:1,class:"mt-1 text-[11px] text-neutral-600"},Ht={key:1,class:"text-xs text-neutral-500"};function Ut(t,e,o,a,r,c){return d(),C(A,{name:"completed-offcanvas"},{default:k(()=>[t.show?(d(),p("div",I,[i("div",{class:"flex-1 bg-black/40 cursor-pointer",onClick:e[0]||(e[0]=s=>t.$emit("close"))}),i("div",O,[i("div",j,[i("div",B,[i("button",{type:"button",class:_(["flex w-full cursor-pointer items-center justify-between border-l-4 px-6 py-6 text-xs md:text-sm font-medium",t.activeTab==="details"?"bg-[var(--brand,_var(--brand-fallback))]/10 text-neutral-900 border-[var(--brand,_var(--brand-fallback))]":"text-neutral-500 hover:bg-neutral-100 border-transparent"]),onClick:e[1]||(e[1]=s=>t.activeTab="details")},[...e[8]||(e[8]=[i("div",{class:"flex items-center gap-2"},[i("i",{class:"bx bx-detail text-base"}),i("span",null,"Details")],-1)])],2),i("button",{type:"button",class:_(["flex w-full cursor-pointer items-center justify-between border-l-4 px-6 py-6 text-xs md:text-sm font-medium",t.activeTab==="activity"?"bg-[var(--brand,_var(--brand-fallback))]/10 text-neutral-900 border-[var(--brand,_var(--brand-fallback))]":"text-neutral-500 hover:bg-neutral-100 border-transparent"]),onClick:e[2]||(e[2]=s=>t.activeTab="activity")},[...e[9]||(e[9]=[i("div",{class:"flex items-center gap-2"},[i("i",{class:"bx bx-history text-base"}),i("span",null,"Activity")],-1)])],2)]),i("div",R,[i("div",F,[i("div",q,[i("div",z,[e[11]||(e[11]=i("span",{class:"inline-flex items-center rounded-full bg-[var(--brand,_var(--brand-fallback))]/10 px-5 py-2.5 text-sm font-semibold text-[var(--brand,_var(--brand-fallback))] w-fit mb-3"},[i("i",{class:"bx bx-check-circle mr-2 text-base"}),v(" Completed ")],-1)),i("div",W,[e[10]||(e[10]=i("span",{class:"text-xl md:text-2xl font-bold text-neutral-900"}," Sale ",-1)),i("span",H,[v(n(t.headerDateLabel)+" ",1),t.bookingStaffName?(d(),p("span",U," · "+n(t.bookingStaffName),1)):b("",!0)])])]),i("div",V,[i("div",G,[i("button",{type:"button",class:"flex h-8 w-10 cursor-pointer items-center justify-center rounded-full hover:bg-neutral-100",onClick:e[3]||(e[3]=D((...s)=>t.toggleMoreMenu&&t.toggleMoreMenu(...s),["stop"]))},[...e[12]||(e[12]=[i("span",{class:"sr-only"},"More options",-1),i("i",{class:"bx bx-dots-vertical-rounded text-xl text-neutral-600"},null,-1)])]),L(A,{name:"dropdown"},{default:k(()=>[t.moreMenuOpen?(d(),p("div",K,[i("button",{type:"button",class:"flex w-full items-center px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer",onClick:e[4]||(e[4]=(...s)=>t.onExportPdf&&t.onExportPdf(...s))},[...e[13]||(e[13]=[i("i",{class:"bx bx-file-pdf text-base text-rose-500"},null,-1),i("span",null,"Print Receipt",-1)])]),i("button",{type:"button",class:"flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer",onClick:e[5]||(e[5]=(...s)=>t.onDownloadPdf&&t.onDownloadPdf(...s))},[...e[14]||(e[14]=[i("span",null,"Download PDF",-1)])]),t.canEditSales?(d(),p("button",{key:0,type:"button",class:"flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer",onClick:e[6]||(e[6]=(...s)=>t.onEditSales&&t.onEditSales(...s))},[...e[15]||(e[15]=[i("span",null,"Edit Sales",-1)])])):b("",!0)],512)):b("",!0)]),_:1})]),i("button",{type:"button",class:"flex h-8 w-8 cursor-pointer items-center justify-center rounded-full hover:bg-neutral-100",onClick:e[7]||(e[7]=s=>t.$emit("close"))},[...e[16]||(e[16]=[i("span",{class:"sr-only"},"Close",-1),i("i",{class:"bx bx-x text-lg text-neutral-600"},null,-1)])])])])]),i("div",X,[t.loading?(d(),p("div",Y," Loading sale details… ")):(d(),p(x,{key:1},[t.booking?t.activeTab==="details"?(d(),p("div",Q,[i("div",Z,[i("div",tt,n(t.clientInitials),1),i("div",et,[i("div",st,n(t.clientName),1),i("div",it,[t.clientEmail?(d(),p("div",ot,n(t.clientEmail),1)):b("",!0),t.clientPhone?(d(),p("div",at,n(t.clientPhone),1)):b("",!0),t.clientNote?(d(),p("div",rt,n(t.clientNote),1)):b("",!0)])])]),i("div",nt,[i("div",lt,[i("div",null,[i("div",ct,n(t.saleNumberLabel),1),i("div",dt,n(t.headerDateLabel),1)])]),t.services.length?(d(),p("div",pt,[(d(!0),p(x,null,N(t.services,s=>(d(),p("div",{key:s.id,class:"flex items-start justify-between gap-4"},[i("div",mt,[i("div",ut,n(s.label),1),i("div",ht,[s.durationLabel?(d(),p("span",bt,n(s.durationLabel),1)):b("",!0),s.staffName?(d(),p("span",ft,[s.durationLabel?(d(),p("span",yt," · ")):b("",!0),v(n(s.staffName),1)])):b("",!0)])]),i("div",gt,[i("div",vt,n(t.currencySymbol)+" "+n(t.formatAmount(s.finalTotal)),1),s.discountTotal>0?(d(),p("div",xt," Discount: -"+n(t.currencySymbol)+" "+n(t.formatAmount(s.discountTotal)),1)):b("",!0)])]))),128))])):b("",!0),i("div",wt,[i("div",St,[e[18]||(e[18]=i("span",{class:"text-neutral-500"},"Subtotal",-1)),i("span",kt,n(t.currencySymbol)+" "+n(t.formatAmount(t.subtotal)),1)]),t.servicesDiscounts>0?(d(),p("div",_t,[e[19]||(e[19]=i("span",{class:"text-neutral-500"},"Discounts",-1)),i("span",At," -"+n(t.currencySymbol)+" "+n(t.formatAmount(t.servicesDiscounts)),1)])):b("",!0),t.tipAmount>0?(d(),p("div",Nt,[e[20]||(e[20]=i("span",{class:"text-neutral-500"},"Tip",-1)),i("span",Tt,n(t.currencySymbol)+" "+n(t.formatAmount(t.tipAmount)),1)])):b("",!0),i("div",$t,[e[21]||(e[21]=i("span",{class:"text-neutral-900"},"Total",-1)),i("span",Et,n(t.currencySymbol)+" "+n(t.formatAmount(t.totalWithTip)),1)])]),i("div",Ct,[i("div",Lt,[i("span",Dt," Paid with "+n(t.paymentMethodLabel),1),i("span",Pt,n(t.currencySymbol)+" "+n(t.formatAmount(t.totalPaid)),1)]),i("div",Mt,n(t.paidAtLabel),1)])])])):(d(),p("div",It,[i("div",Ot,[i("div",jt,n(t.currentMonth),1)]),t.activityItems.length?(d(),p("div",Bt,[(d(!0),p(x,null,N(t.activityItems,s=>(d(),p("div",{key:s.id,class:"flex items-start gap-3"},[i("div",Rt,[i("div",Ft,n(s.title),1),i("div",qt,n(s.subtitle),1),s.meta?(d(),p("div",zt,n(s.meta),1)):b("",!0),s.byline?(d(),p("div",Wt,n(s.byline),1)):b("",!0)])]))),128))])):(d(),p("div",Ht," No recent activity for this sale. "))])):(d(),p("div",J,[...e[17]||(e[17]=[i("p",{class:"text-sm text-neutral-500"}," Booking data could not be loaded. ",-1)])]))],64))])])])])])):b("",!0)]),_:1})}const Kt=P(M,[["render",Ut],["__scopeId","data-v-80ad6bb9"]]);export{Kt as default};
