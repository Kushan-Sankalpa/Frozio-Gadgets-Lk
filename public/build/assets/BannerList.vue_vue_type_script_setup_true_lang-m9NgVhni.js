import{D as p}from"./Datatable-B1lxjAh4.js";import{d as h,c as f,b as t,j as v,s as x,p as _,P as s,h as g,y as l,i as y}from"./app-2EE6_-rJ.js";const w={class:"rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm"},C=h({__name:"BannerList",setup(k){const i=_(()=>s("homebanners.data")),d=g(0),c=[{data:"id",name:"id"},{data:"name",name:"name"},{data:"video_url",name:"video_path",orderable:!1,searchable:!1},{data:"description",name:"description"},{data:"actions",name:"actions",orderable:!1,searchable:!1}],u=[{targets:2,render:e=>e?`
        <video
          src="${e}"
          muted
          playsinline
          preload="metadata"
          controls
          class="h-16 w-28 rounded-lg border border-neutral-200 bg-black object-cover"
        ></video>
      `:'<span class="text-xs text-neutral-400">No Video</span>'},{targets:4,render:e=>e}];function m(e){const n=e.target.closest("button[data-action]");if(!n)return;e.preventDefault(),e.stopPropagation();const r=n.dataset.action,o=n.dataset.id,b=n.dataset.name;if(!(!r||!o)){if(r==="edit"){l.visit(s("homebanners.edit",Number(o)));return}if(r==="delete"){if(!confirm(`Delete home banner "${b||""}"? This cannot be undone.`))return;l.delete(s("homebanners.destroy",Number(o)),{preserveScroll:!0,onSuccess:()=>d.value=Date.now()})}}}return(e,a)=>(y(),f("div",w,[a[1]||(a[1]=t("div",{class:"font-semibold text-neutral-800 mb-4"},"All Home Banners",-1)),t("div",{onClick:m},[v(p,{id:"homeBannerTable",url:i.value,columns:c,columnDefs:u,order:[[0,"desc"]],reloadKey:d.value},{header:x(()=>[...a[0]||(a[0]=[t("tr",null,[t("th",{style:{width:"60px"}},"#"),t("th",null,"Banner Name"),t("th",{style:{width:"180px"}},"Video"),t("th",null,"Description"),t("th",{style:{width:"220px"}},"Actions")],-1)])]),_:1},8,["url","reloadKey"])])]))}});export{C as _};
