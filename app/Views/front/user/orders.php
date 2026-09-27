<?php
require_once __DIR__ . '/../layouts/header.php';

$user   = $user ?? [];
$orders = $user['orders'] ?? [];

// Build JS-safe orders array
$jsOrders = json_encode($orders);
?>

<style>
  body { background: #F1F5F9; }
  .sidebar-link { transition: all .15s ease; border-radius: 10px; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes scaleIn { from { transform:scale(.95); opacity:0; } to { transform:scale(1); opacity:1; } }
  .scale-in { animation: scaleIn .25s ease-out both; }
  @keyframes slideInRight { from { transform:translateX(100%); opacity:0; } to { transform:translateX(0); opacity:1; } }
  @keyframes slideOutRight { from { transform:translateX(0); opacity:1; } to { transform:translateX(100%); opacity:0; } }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }
  .modal-scroll::-webkit-scrollbar { width:4px; }
  .modal-scroll::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:4px; }
  .star-input { cursor:pointer; transition:transform .1s ease; display:inline-block; }
  .star-input:hover { transform:scale(1.2); }
  .tab-btn { padding:10px 18px; font-size:13px; font-weight:600; border-bottom:2.5px solid transparent; color:#64748B; transition:all .2s ease; white-space:nowrap; cursor:pointer; background:none; border-top:none; border-left:none; border-right:none; }
  .tab-btn:hover { color:#2D82FF; }
  .tab-btn.active { color:#2D82FF; border-bottom-color:#2D82FF; }
  .tab-count { font-size:10px; min-width:18px; height:18px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; padding:0 5px; font-weight:700; margin-left:5px; }
  .order-row { border:1.5px solid #E2E8F0; border-radius:14px; background:#fff; transition:all .2s ease; cursor:pointer; }
  .order-row:hover { border-color:#2D82FF; box-shadow:0 4px 16px rgba(45,130,255,.08); }
  .order-row.cancelled-row { border-left:4px solid #EF4444; }
  .order-row.return-row { border-left:4px solid #F97316; }
  .sbadge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:6px; font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
  .sb-pending          { background:#FEF3C7; color:#92400E; }
  .sb-confirmed        { background:#DBEAFE; color:#1E40AF; }
  .sb-processing       { background:#E0E7FF; color:#3730A3; }
  .sb-shipped          { background:#EDE9FE; color:#5B21B6; }
  .sb-delivered        { background:#D1FAE5; color:#065F46; }
  .sb-cancelled        { background:#FEE2E2; color:#991B1B; }
  .sb-return_requested { background:#FFEDD5; color:#9A3412; }
  .sb-returned         { background:#F1F5F9; color:#475569; }
  .sb-refunded         { background:#CCFBF1; color:#115E59; }
  .sb-paid             { background:#D1FAE5; color:#065F46; }
  .sb-unpaid           { background:#FEF3C7; color:#92400E; }
  .thumb { width:52px; height:52px; border-radius:10px; background:#F8FAFC; border:1px solid #E2E8F0; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; }
  .thumb img { max-height:100%; max-width:100%; object-fit:contain; padding:4px; }
  .act-btn { padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600; transition:all .15s ease; display:inline-flex; align-items:center; gap:4px; cursor:pointer; border:1.5px solid #E2E8F0; color:#475569; background:#fff; }
  .act-btn:hover { border-color:#2D82FF; color:#2D82FF; background:#F0F6FF; }
  .act-btn.primary { border-color:#2D82FF; color:#fff; background:#2D82FF; }
  .act-btn.primary:hover { background:#1D6FE0; }
  .act-btn.danger { border-color:#FCA5A5; color:#DC2626; }
  .act-btn.danger:hover { background:#FEF2F2; border-color:#EF4444; }
  .act-btn.orange { border-color:#FDBA74; color:#C2410C; }
  .act-btn.orange:hover { background:#FFF7ED; border-color:#F97316; }
  .tldot { width:11px; height:11px; border-radius:50%; border:2.5px solid #E2E8F0; background:#fff; flex-shrink:0; }
  .tldot.done { border-color:#10B981; background:#10B981; }
  .tldot.now { border-color:#2D82FF; background:#2D82FF; box-shadow:0 0 0 4px rgba(45,130,255,.12); }
  .tlline { width:2px; min-height:18px; background:#E2E8F0; margin-left:4px; flex:1; }
  .tlline.done { background:#10B981; }
  .stat-card { border:1.5px solid #E2E8F0; border-radius:14px; background:#fff; transition:all .2s ease; }
  .stat-card:hover { box-shadow:0 2px 12px rgba(15,23,42,.06); }
</style>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-4 py-6 pb-24 md:pb-6">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 mb-5">
    <a href="<?= $baseUrl ?>/" class="hover:text-[#2D82FF] transition">Home</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <a href="<?= $baseUrl ?>/user/dashboard" class="hover:text-[#2D82FF] transition">My Account</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-slate-800 font-semibold">My Orders</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Mobile Sidebar Toggle Button -->
      <button onclick="toggleMobileSidebar(true)" class="lg:hidden flex items-center justify-between w-full bg-white border border-slate-200 p-3.5 rounded-2xl shadow-xs text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-lg">menu</span>
          <span>Account Navigation Menu</span>
        </div>
        <span class="bg-[#2D82FF]/10 text-[#2D82FF] text-[10px] font-extrabold px-2.5 py-1 rounded-lg">MENU</span>
      </button>

      <!-- Page Header + Stats -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 fade-up">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
            <span class="material-icons text-[#2D82FF] text-xl">shopping_bag</span>
          </div>
          <div>
            <h1 class="font-bold text-xl text-slate-900">My Orders</h1>
            <p class="text-xs text-slate-400">Track, manage, and review all your purchases</p>
          </div>
        </div>
        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="stats-row"></div>
      </div>

      <!-- Search + Sort Bar -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row gap-3 fade-up" style="animation-delay:.08s">
        <div class="relative flex-1">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
          <input type="text" id="order-search" oninput="renderOrders()"
                 placeholder="Search by order number..."
                 class="pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm w-full focus:border-[#2D82FF] focus:ring-2 focus:ring-blue-100 transition outline-none">
        </div>
        <select id="sort-select" onchange="renderOrders()"
                class="border border-slate-200 rounded-xl text-sm px-4 py-2.5 cursor-pointer focus:border-[#2D82FF] shrink-0 outline-none bg-white">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="highest">Highest Value</option>
          <option value="lowest">Lowest Value</option>
        </select>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-t-2xl border border-b-0 border-slate-200 overflow-x-auto scrollbar-none fade-up" style="animation-delay:.12s">
        <div class="flex min-w-max px-2 pt-1" id="tabs-bar"></div>
      </div>

      <!-- Orders List -->
      <div class="bg-white rounded-b-2xl border border-t-0 border-slate-200 p-4 sm:p-5 min-h-[200px] fade-up" style="animation-delay:.16s" id="orders-container"></div>

      <!-- Empty State -->
      <div id="empty-state" class="hidden bg-white rounded-b-2xl border border-t-0 border-slate-200 p-10 sm:p-14 text-center">
        <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
          <span class="material-icons text-4xl text-slate-300" id="empty-icon">inventory_2</span>
        </div>
        <h3 class="font-bold text-lg text-slate-800 mb-1" id="empty-title">No orders found</h3>
        <p class="text-sm text-slate-500 max-w-sm mx-auto mb-5" id="empty-desc"></p>
        <a href="<?= $baseUrl ?>/" class="bg-[#2D82FF] hover:bg-blue-600 text-white font-bold px-7 py-3 rounded-xl transition shadow-md inline-flex items-center gap-2 text-sm">
          <span class="material-icons text-[18px]">explore</span> Start Shopping
        </a>
      </div>

      <!-- Load More -->
      <div id="load-more-wrap" class="text-center mt-3 hidden">
        <button onclick="loadMore()" class="border-2 border-slate-200 hover:border-[#2D82FF] text-slate-600 hover:text-[#2D82FF] font-semibold px-8 py-3 rounded-xl transition text-sm inline-flex items-center gap-2">
          <span class="material-icons text-[18px]">expand_more</span> Load More Orders
        </button>
      </div>

    </div>
  </div>
</main>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex justify-around py-2 shadow-2xl">
  <a href="<?= $baseUrl ?>/" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">home</span><span class="text-[9px] font-semibold">Home</span>
  </a>
  <a href="<?= $baseUrl ?>/user/orders" class="flex flex-col items-center text-[#2D82FF] py-1">
    <span class="material-icons text-2xl">shopping_bag</span><span class="text-[9px] font-semibold">Orders</span>
  </a>
  <a href="<?= $baseUrl ?>/wishlist" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">favorite_border</span><span class="text-[9px] font-semibold">Wishlist</span>
  </a>
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<!-- ORDER DETAIL MODAL -->
<div id="order-modal" class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center overflow-hidden hidden">
  <div onclick="closeOrderModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-t-2xl sm:rounded-2xl max-w-2xl w-full shadow-2xl relative z-10 max-h-[90vh] flex flex-col scale-in mx-0 sm:mx-4">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0">
      <h3 id="modal-title" class="font-bold text-base text-slate-900"></h3>
      <button onclick="closeOrderModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>
    <div id="modal-body" class="flex-1 overflow-y-auto modal-scroll p-5 space-y-5"></div>
  </div>
</div>

<!-- CONFIRM / CANCEL MODAL -->
<div id="confirm-modal" class="fixed inset-0 z-[80] flex items-center justify-center overflow-hidden hidden">
  <div onclick="closeConfirmModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-3xl max-w-md w-[92%] shadow-2xl relative z-10 p-6 sm:p-7 scale-in">
    <button onclick="closeConfirmModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition">
      <span class="material-icons">close</span>
    </button>
    <div class="text-center">
      <div id="cfm-icon-wrap" class="w-14 h-14 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-4">
        <span id="cfm-icon" class="material-icons text-red-500 text-2xl">cancel</span>
      </div>
      <h3 id="cfm-title" class="font-bold text-lg text-slate-900 mb-1">Cancel Order</h3>
      <p id="cfm-desc" class="text-xs text-slate-500 mb-4"></p>

      <div id="cfm-reason-wrap" class="hidden text-left space-y-3.5 mb-5">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Reason for Cancellation *</label>
          <select id="cfm-reason" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-medium focus:border-[#2D82FF] focus:ring-2 focus:ring-blue-100 outline-none">
            <option value="">-- Select Reason --</option>
            <option value="Ordered by mistake">Ordered by mistake</option>
            <option value="Found better price elsewhere">Found better price elsewhere</option>
            <option value="Delivery time too long">Delivery time too long</option>
            <option value="Shipping address mistake">Shipping address mistake</option>
            <option value="Changed my mind">Changed my mind</option>
            <option value="Other">Other reason</option>
          </select>
        </div>

        <!-- PhonePe / Online Refund UPI ID Field -->
        <div id="upi-refund-field" class="hidden">
          <label class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-1">
            <span class="inline-flex items-center gap-1"><span class="w-4 h-4 rounded bg-[#5F259F] text-white text-[9px] font-black flex items-center justify-center">पे</span> Refund UPI ID / Phone *</span>
          </label>
          <input type="text" id="cfm-upi-id" placeholder="e.g. yourname@ybl, name@upi or Mobile Number" class="w-full border border-purple-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold text-purple-800 bg-purple-50/50 focus:border-[#5F259F] focus:ring-2 focus:ring-purple-100 outline-none">
          <p class="text-[10px] text-slate-400 mt-1">Your refund for PhonePe payment will be credited to this UPI ID.</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Additional Comments (Optional)</label>
          <textarea id="cfm-comment" rows="2" placeholder="Tell us more about why you are cancelling..." class="w-full border border-slate-200 rounded-xl p-3 text-xs focus:border-[#2D82FF] outline-none"></textarea>
        </div>
      </div>

      <div class="flex gap-3">
        <button onclick="closeConfirmModal()" id="cfm-cancel-btn" class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-3 rounded-xl transition text-xs">Keep Order</button>
        <button id="cfm-action-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-xl transition text-xs shadow-md"></button>
      </div>
    </div>
  </div>
</div>

<script>
// ============================================================
// DATA — passed from PHP
// ============================================================
const ORDERS_DB = <?= $jsOrders ?>.map(o => ({
  ...o,
  total_amount: parseFloat(o.grand_total || o.total_amount || 0),
  placed_at: o.created_at || o.placed_at || '',
  items: o.items || [],
  history: o.history || [],
  discount_amount: parseFloat(o.discount_amount || 0),
  shipping_charge: parseFloat(o.shipping_charge || 0),
  tax_amount: parseFloat(o.tax_amount || 0),
}));

let activeTab = 'all';
let visibleCount = 20;
let pendingCancelId = null;
let pendingReturnId = null;

const TABS = [
  {key:'all',        label:'All Orders',   icon:'inbox'},
  {key:'pending',    label:'Pending',      icon:'schedule'},
  {key:'confirmed',  label:'Confirmed',    icon:'check_circle'},
  {key:'shipped',    label:'Shipped',      icon:'local_shipping'},
  {key:'delivered',  label:'Delivered',    icon:'done_all'},
  {key:'cancelled',  label:'Cancelled',    icon:'cancel'},
];

// ============================================================
// HELPERS
// ============================================================
function fp(n){return '₹'+Math.round(n).toLocaleString('en-IN')}
function fd(iso){if(!iso)return '—';try{return new Date(iso).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'})}catch(e){return iso}}
function ft(iso){if(!iso)return '';try{return new Date(iso).toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',hour12:true})}catch(e){return ''}}
function fdt(iso){return fd(iso)+(ft(iso)?' · '+ft(iso):'') }
function sLabel(s){return{pending:'Pending',confirmed:'Confirmed',processing:'Processing',shipped:'Shipped',delivered:'Delivered',cancelled:'Cancelled',return_requested:'Return Req.',returned:'Returned',refunded:'Refunded'}[s]||s}
function sIcon(s){return{pending:'schedule',confirmed:'check_circle',processing:'sync',shipped:'local_shipping',delivered:'done_all',cancelled:'cancel',return_requested:'replay',returned:'assignment_return',refunded:'currency_rupee'}[s]||'help'}
function sBadgeClass(s){return'sb-'+s.replace(/_/g,'-').toLowerCase()||'sb-pending'}

function getEst(o){
  if(o.delivered_at)return 'Delivered on '+fd(o.delivered_at);
  if(o.shipped_at){const d=new Date(o.shipped_at);d.setDate(d.getDate()+3);return 'Expected by '+d.toLocaleDateString('en-IN',{weekday:'short',day:'numeric',month:'short'})}
  if(o.confirmed_at){const d=new Date(o.confirmed_at);d.setDate(d.getDate()+5);return 'Expected by '+d.toLocaleDateString('en-IN',{weekday:'short',day:'numeric',month:'short'})}
  return 'Processing...';
}

// ============================================================
// TOAST
// ============================================================
function showToast(msg,type='success'){
  const c=document.getElementById('toast-container');
  const cl={success:'bg-green-600',error:'bg-red-500',info:'bg-[#2D82FF]',warning:'bg-amber-500'};
  const ic={success:'check_circle',error:'error',info:'info',warning:'warning'};
  const t=document.createElement('div');
  t.className=`toast-in pointer-events-auto flex items-center gap-3 ${cl[type]||cl.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML=`<span class="material-icons text-lg">${ic[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{t.classList.replace('toast-in','toast-out');setTimeout(()=>t.remove(),300)},3500);
}

// ============================================================
// RENDER STATS
// ============================================================
function renderStats(){
  const total=ORDERS_DB.length;
  const spent=ORDERS_DB.filter(o=>o.payment_status==='paid').reduce((s,o)=>s+o.total_amount,0);
  const pending=ORDERS_DB.filter(o=>['pending','confirmed','processing','shipped'].includes(o.status)).length;
  const delivered=ORDERS_DB.filter(o=>o.status==='delivered').length;

  document.getElementById('stats-row').innerHTML=`
    <div class="stat-card p-3.5 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0"><span class="material-icons text-[#2D82FF] text-lg">receipt_long</span></div>
      <div><p class="text-lg font-extrabold text-slate-900 font-heading">${total}</p><p class="text-[11px] text-slate-500">Total Orders</p></div>
    </div>
    <div class="stat-card p-3.5 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0"><span class="material-icons text-orange-500 text-lg">account_balance_wallet</span></div>
      <div><p class="text-lg font-extrabold text-slate-900 font-heading">${fp(spent)}</p><p class="text-[11px] text-slate-500">Total Spent</p></div>
    </div>
    <div class="stat-card p-3.5 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0"><span class="material-icons text-amber-600 text-lg">pending_actions</span></div>
      <div><p class="text-lg font-extrabold text-slate-900 font-heading">${pending}</p><p class="text-[11px] text-slate-500">In Progress</p></div>
    </div>
    <div class="stat-card p-3.5 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0"><span class="material-icons text-green-600 text-lg">done_all</span></div>
      <div><p class="text-lg font-extrabold text-slate-900 font-heading">${delivered}</p><p class="text-[11px] text-slate-500">Delivered</p></div>
    </div>
  `;
}

// ============================================================
// TABS
// ============================================================
function renderTabs(){
  const bar=document.getElementById('tabs-bar');
  bar.innerHTML=TABS.map(t=>{
    const count=t.key==='all'?ORDERS_DB.length:ORDERS_DB.filter(o=>o.status===t.key).length;
    const isActive=activeTab===t.key;
    return `<button class="tab-btn ${isActive?'active':''}" onclick="setTab('${t.key}')">
      <span class="inline-flex items-center gap-1.5"><span class="material-icons text-[15px]">${t.icon}</span>${t.label}</span>
      <span class="tab-count ${isActive?'bg-[#2D82FF] text-white':'bg-slate-100 text-slate-500'}">${count}</span>
    </button>`;
  }).join('');
}

function setTab(key){ activeTab=key; visibleCount=20; renderTabs(); renderOrders(); }

// ============================================================
// FILTER & SORT
// ============================================================
function getFiltered(){
  let list=[...ORDERS_DB];
  const q=(document.getElementById('order-search').value||'').trim().toUpperCase();
  if(q) list=list.filter(o=>(o.order_number||'').toUpperCase().includes(q)||(o.items||[]).some(i=>(i.product_name||'').toUpperCase().includes(q)));
  if(activeTab!=='all') list=list.filter(o=>o.status===activeTab);
  const sort=document.getElementById('sort-select').value;
  switch(sort){
    case'oldest': list.sort((a,b)=>new Date(a.placed_at)-new Date(b.placed_at)); break;
    case'highest': list.sort((a,b)=>b.total_amount-a.total_amount); break;
    case'lowest':  list.sort((a,b)=>a.total_amount-b.total_amount); break;
    default: list.sort((a,b)=>new Date(b.placed_at)-new Date(a.placed_at));
  }
  return list;
}

// ============================================================
// RENDER ORDERS
// ============================================================
function renderOrders(){
  const list=getFiltered();
  const container=document.getElementById('orders-container');
  const emptyEl=document.getElementById('empty-state');
  const loadWrap=document.getElementById('load-more-wrap');
  const visible=list.slice(0,visibleCount);

  if(visible.length===0){
    container.innerHTML=''; container.classList.add('hidden');
    emptyEl.classList.remove('hidden'); loadWrap.classList.add('hidden');
    const icons={all:'inventory_2',pending:'schedule',confirmed:'check_circle',shipped:'local_shipping',delivered:'done_all',cancelled:'cancel'};
    const titles={all:'No orders yet',pending:'No pending orders',confirmed:'No confirmed orders',shipped:'No orders in transit',delivered:'No delivered orders',cancelled:'No cancelled orders'};
    const descs={all:"You haven't placed any orders yet. Explore our catalog!",pending:'All your orders have been processed.',confirmed:'No orders awaiting confirmation.',shipped:'No orders currently in transit.',delivered:'No delivered orders yet.',cancelled:"You haven't cancelled any orders."};
    document.getElementById('empty-icon').textContent=icons[activeTab]||'inventory_2';
    document.getElementById('empty-title').textContent=titles[activeTab]||'No orders found';
    document.getElementById('empty-desc').textContent=descs[activeTab]||'';
    return;
  }

  emptyEl.classList.add('hidden');
  container.classList.remove('hidden');
  loadWrap.classList.toggle('hidden', list.length<=visibleCount);

  container.innerHTML=visible.map((o,idx)=>{
    const isCancelled=o.status==='cancelled';
    const isReturn=o.status==='return_requested';
    const rowClass=isCancelled?'order-row cancelled-row':isReturn?'order-row return-row':'order-row';
    const totalQty=(o.items||[]).reduce((s,i)=>s+(parseInt(i.qty)||1),0);

    // Product thumbnails
    const items=o.items||[];
    const thumbsHtml=items.slice(0,3).map(i=>`
      <div class="thumb">${i.img?`<img src="${i.img}" alt="" loading="lazy">`:
        `<span class="material-icons text-slate-400">inventory_2</span>`}
      </div>`).join('');
    const more=items.length-3;

    return `
    <div class="${rowClass} p-4 sm:p-5 mb-3 fade-up" style="animation-delay:${Math.min(idx*.05,.3)}s;opacity:0" onclick="openOrderModal(${o.id})">
      <!-- Top: Order # + Status -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-3">
        <div class="flex flex-wrap items-center gap-2">
          <span class="font-extrabold text-sm text-[#2D82FF]">${o.order_number||'ORD-'+o.id}</span>
          <span class="sbadge ${sBadgeClass(o.status)}">
            <span class="material-icons" style="font-size:10px">${sIcon(o.status)}</span>${sLabel(o.status)}
          </span>
          ${o.payment_status==='refunded'?'<span class="sbadge sb-refunded"><span class="material-icons" style="font-size:10px">currency_rupee</span>Refunded</span>':''}
        </div>
        <div class="text-right">
          <p class="font-extrabold text-base text-slate-900">${fp(o.total_amount)}</p>
          ${o.discount_amount>0?`<p class="text-[11px] text-green-600 font-medium">Saved ${fp(o.discount_amount)}</p>`:''}
        </div>
      </div>
      <!-- Middle: Item thumbs + ETA -->
      <div class="flex items-center gap-2.5">
        <div class="flex gap-2">${thumbsHtml}</div>
        ${more>0?`<div class="thumb items-center justify-center text-xs font-bold text-slate-500">+${more}</div>`:''}
        <div class="flex-1"></div>
        <span class="text-xs text-slate-500 hidden sm:block shrink-0">${getEst(o)}</span>
        <span class="material-icons text-slate-300 text-xl">chevron_right</span>
      </div>
      <!-- Bottom: Meta + Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
        <p class="text-[11px] text-slate-400">
          Placed ${fdt(o.placed_at)} · ${totalQty} item${totalQty!==1?'s':''}
        </p>
        <div class="flex gap-1.5 flex-wrap">
          ${['confirmed','processing','shipped'].includes(o.status)?`<button onclick="showToast('Opening tracking details...','info')" class="act-btn primary text-[11px] py-1.5 px-3"><span class="material-icons" style="font-size:12px">local_shipping</span>Track</button>`:''}
          ${o.status==='delivered'?`<button onclick="openRateModal(${o.id})" class="act-btn orange text-[11px] py-1.5 px-3"><span class="material-icons" style="font-size:12px">star_rate</span>Rate</button>`:''}
          ${['pending','confirmed'].includes(o.status)?`<button onclick="confirmCancel(${o.id})" class="act-btn danger text-[11px] py-1.5 px-3"><span class="material-icons" style="font-size:12px">cancel</span>Cancel</button>`:''}
          ${o.status==='delivered'?`<button onclick="doReorder(${o.id})" class="act-btn text-[11px] py-1.5 px-3"><span class="material-icons" style="font-size:12px">refresh</span>Reorder</button>`:''}
          ${o.status==='delivered'?`<a href="${BASE_URL}/user/orders/invoice/${o.encrypted_id || o.id}" target="_blank" onclick="event.stopPropagation()" class="act-btn text-[11px] py-1.5 px-3"><span class="material-icons" style="font-size:12px">download</span>Invoice</a>`:''}
        </div>
      </div>
    </div>`;
  }).join('');
}

// ============================================================
// ORDER DETAIL MODAL
// ============================================================
function openOrderModal(id){
  const o=ORDERS_DB.find(x=>parseInt(x.id)===id);
  if(!o)return;
  document.getElementById('modal-title').textContent='#'+(o.order_number||'ORD-'+o.id);

  const statusOrder=['pending','confirmed','processing','shipped','delivered'];
  const cIdx=o.status==='cancelled'?-1:o.status==='return_requested'?statusOrder.indexOf('delivered'):statusOrder.indexOf(o.status);
  const hMap={};(o.history||[]).forEach(h=>{hMap[h.status]=h});

  const timeline=statusOrder.map((s,idx)=>{
    const done=idx<=cIdx&&cIdx>=0;
    const now=idx===cIdx&&cIdx>=0;
    const h=hMap[s];
    return `<div class="flex gap-3">
      <div class="flex flex-col items-center">
        <div class="tldot ${done?'done':''} ${now?'now':''}"></div>
        ${idx<4?`<div class="tlline ${done?'done':''}"></div>`:''}
      </div>
      <div class="pb-4 flex-1">
        <p class="text-xs font-semibold ${done?'text-slate-800':'text-slate-400'}">${sLabel(s)}</p>
        ${h?`<p class="text-[11px] text-slate-500 mt-0.5">${h.note||''}</p><p class="text-[10px] text-slate-400">${fdt(h.at)}</p>`:'<p class="text-[11px] text-slate-400">Pending</p>'}
      </div>
    </div>`;
  }).join('');

  let timelineExtra='';
  if(o.status==='cancelled'){
    timelineExtra=`<div class="flex gap-3"><div class="flex flex-col items-center"><div class="tldot" style="border-color:#EF4444;background:#EF4444"></div></div><div class="pb-2"><p class="text-xs font-semibold text-red-600">Cancelled</p><p class="text-[11px] text-slate-500">${hMap['cancelled']?.note||''}</p><p class="text-[10px] text-slate-400">${hMap['cancelled']?fdt(hMap['cancelled'].at):''}</p></div></div>`;
  }else if(o.status==='return_requested'){
    timelineExtra=`<div class="flex gap-3"><div class="flex flex-col items-center"><div class="tldot" style="border-color:#F97316;background:#F97316;box-shadow:0 0 0 4px rgba(249,115,22,.12)"></div></div><div class="pb-2"><p class="text-xs font-semibold text-orange-700">Return Requested</p><p class="text-[11px] text-slate-500">${hMap['return_requested']?.note||''}</p><p class="text-[10px] text-slate-400">${hMap['return_requested']?fdt(hMap['return_requested'].at):''}</p></div></div>`;
  }

  const items=(o.items||[]);
  const itemsHtml=items.length>0?items.map(i=>{
    const pName = i.name || i.product_name || 'Product Item';
    const pImg  = i.img || i.primary_image || '';
    const pQty  = parseInt(i.qty || i.quantity || 1);
    const pPrc  = parseFloat(i.unit_price || i.sale_price || 0);
    const pTot  = parseFloat(i.total_price || i.total || (pPrc * pQty));
    return `<div class="flex gap-3 py-3 border-b border-slate-100 last:border-0">
      <div class="w-14 h-14 rounded-xl bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 overflow-hidden">
        ${pImg?`<img src="${pImg}" class="max-h-full max-w-full object-contain p-1" loading="lazy" alt="">`:'<span class="material-icons text-slate-300 text-xl">inventory_2</span>'}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-bold text-slate-800 truncate">${pName}</p>
        <p class="text-[11px] text-slate-400">Qty: ${pQty} × ${fp(pPrc)}</p>
      </div>
      <div class="text-right shrink-0">
        <p class="font-extrabold text-xs text-slate-900">${fp(pTot)}</p>
      </div>
    </div>`;
  }).join(''):'<p class="text-xs text-slate-400 py-4 text-center">No item details available.</p>';

  let actionsHtml='';
  if(['confirmed','processing','shipped'].includes(o.status)) actionsHtml+=`<button onclick="showToast('Opening tracking...','info')" class="act-btn primary"><span class="material-icons" style="font-size:14px">local_shipping</span>Track Order</button>`;
  if(['pending','confirmed'].includes(o.status)) actionsHtml+=`<button onclick="confirmCancel(${o.id});closeOrderModal()" class="act-btn danger"><span class="material-icons" style="font-size:14px">cancel</span>Cancel Order</button>`;
  if(o.status==='delivered') actionsHtml+=`<button onclick="doReorder(${o.id});closeOrderModal()" class="act-btn"><span class="material-icons" style="font-size:14px">refresh</span>Reorder</button><button onclick="openRateModal(${o.id});closeOrderModal()" class="act-btn orange"><span class="material-icons" style="font-size:14px">star_rate</span>Rate & Review</button><a href="${BASE_URL}/user/orders/invoice/${o.encrypted_id || o.id}" target="_blank" onclick="event.stopPropagation()" class="act-btn"><span class="material-icons" style="font-size:14px">download</span>Invoice</a>`;

  document.getElementById('modal-body').innerHTML=`
    <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-3.5">
      <span class="material-icons text-[#2D82FF] text-xl">schedule</span>
      <div>
        <p class="text-sm font-semibold text-slate-800">${getEst(o)}</p>
        <p class="text-[10px] text-slate-400">Placed on ${fdt(o.placed_at)}</p>
      </div>
    </div>
    <div>
      <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Order Items (${items.reduce((s,i)=>s+(parseInt(i.qty)||1),0)})</h4>
      ${itemsHtml}
    </div>
    <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm">
      <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>${fp(o.subtotal||o.total_amount)}</span></div>
      ${o.discount_amount>0?`<div class="flex justify-between"><span class="text-slate-500">Discount</span><span class="text-green-600">−${fp(o.discount_amount)}</span></div>`:''}
      <div class="flex justify-between"><span class="text-slate-500">Delivery</span><span class="${(o.shipping_charge||0)===0?'text-green-600':'text-slate-800'}">${(o.shipping_charge||0)===0?'FREE':fp(o.shipping_charge)}</span></div>
      ${o.tax_amount>0?`<div class="flex justify-between"><span class="text-slate-500">Tax (GST)</span><span>${fp(o.tax_amount)}</span></div>`:''}
      <div class="flex justify-between font-bold pt-2 border-t border-dashed border-slate-200">
        <span>Grand Total</span><span class="text-[#FF5100] text-base">${fp(o.total_amount)}</span>
      </div>
      <div class="flex justify-between text-xs pt-1">
        <span class="text-slate-400">Payment Status</span>
        <span class="font-bold ${o.payment_status==='paid'?'text-green-600':o.payment_status==='refunded'?'text-teal-600':'text-amber-600'}">${(o.payment_status||'pending').charAt(0).toUpperCase()+(o.payment_status||'pending').slice(1)}</span>
      </div>
    </div>
    <div>
      <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Order Timeline</h4>
      ${timeline}${timelineExtra}
    </div>
    <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100">${actionsHtml}</div>
  `;

  document.getElementById('order-modal').classList.remove('hidden');
}

function closeOrderModal(){document.getElementById('order-modal').classList.add('hidden');}

// ============================================================
// CANCEL ORDER
// ============================================================
function confirmCancel(id){
  pendingCancelId=id;
  const o=ORDERS_DB.find(x=>parseInt(x.id)===id);
  if(!o)return;

  const payMethod=(o.payment_method||'').toLowerCase();
  const isOnlinePay=payMethod!=='cod' && payMethod!=='cash on delivery';

  document.getElementById('cfm-title').textContent='Cancel Order #'+(o.order_number||'ORD-'+id);
  if(isOnlinePay){
    document.getElementById('cfm-desc').innerHTML=`Order total: <strong>${fp(o.total_amount)}</strong> paid via <strong>${o.payment_method||'PhonePe'}</strong>. Select a reason and enter your UPI ID for refund.`;
    document.getElementById('upi-refund-field').classList.remove('hidden');
  } else {
    document.getElementById('cfm-desc').innerHTML=`Order total: <strong>${fp(o.total_amount)}</strong> (Cash on Delivery). Please select a reason for cancellation.`;
    document.getElementById('upi-refund-field').classList.add('hidden');
  }

  document.getElementById('cfm-reason-wrap').classList.remove('hidden');
  document.getElementById('cfm-reason').value='';
  document.getElementById('cfm-upi-id').value='';
  document.getElementById('cfm-comment').value='';

  document.getElementById('cfm-action-btn').textContent='Confirm Cancellation';
  document.getElementById('cfm-action-btn').className='flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-xl transition text-xs shadow-md';
  document.getElementById('cfm-action-btn').onclick=executeCancel;
  document.getElementById('cfm-icon').textContent='cancel';
  document.getElementById('cfm-icon-wrap').className='w-14 h-14 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-4';
  document.getElementById('cfm-cancel-btn').textContent='Keep Order';
  document.getElementById('confirm-modal').classList.remove('hidden');
}

function executeCancel(){
  const o=ORDERS_DB.find(x=>parseInt(x.id)===pendingCancelId);
  if(!o)return;

  const reason=document.getElementById('cfm-reason').value;
  if(!reason){
    showToast('Please select a reason for cancellation.','warning');
    return;
  }

  const payMethod=(o.payment_method||'').toLowerCase();
  const isOnlinePay=payMethod!=='cod' && payMethod!=='cash on delivery';
  const upiId=(document.getElementById('cfm-upi-id').value||'').trim();

  if(isOnlinePay && !upiId){
    showToast('Please enter your UPI ID for PhonePe refund.','warning');
    return;
  }

  const comments=(document.getElementById('cfm-comment').value||'').trim();

  fetch(`${BASE_URL}/api/user/cancel-order`,{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({order_id:o.id,reason:reason,upi_id:upiId,comments:comments})
  }).catch(e=>console.error(e));

  o.status='cancelled'; o.cancelled_at=new Date().toISOString(); 
  if(isOnlinePay) o.payment_status='refund_pending';

  closeConfirmModal(); renderStats(); renderTabs(); renderOrders();
  if(isOnlinePay){
    showToast('Order '+o.order_number+' cancelled. Refund will be sent to UPI: '+upiId,'warning');
  } else {
    showToast('Order '+o.order_number+' cancelled successfully.','warning');
  }
}

// ============================================================
// RATE & REVIEW MODAL
// ============================================================
function openRateModal(id){
  const o=ORDERS_DB.find(x=>parseInt(x.id)===id);
  if(!o)return;
  document.getElementById('cfm-title').textContent='Rate Your Order';
  document.getElementById('cfm-desc').innerHTML=`How was your experience with order <strong>${o.order_number||'ORD-'+id}</strong>?`;
  document.getElementById('cfm-icon').textContent='star_rate';
  document.getElementById('cfm-icon-wrap').className='w-14 h-14 mx-auto rounded-full bg-amber-50 flex items-center justify-center mb-4';
  document.getElementById('cfm-reason-wrap').classList.add('hidden');
  document.getElementById('cfm-cancel-btn').textContent='Skip';
  document.getElementById('cfm-action-btn').textContent='Submit Rating';
  document.getElementById('cfm-action-btn').className='flex-1 bg-[#2D82FF] hover:bg-blue-600 text-white font-semibold py-2.5 rounded-xl transition text-sm';
  document.getElementById('cfm-action-btn').onclick=function(){closeConfirmModal();showToast('Thank you for your feedback!','success')};
  const descEl=document.getElementById('cfm-desc');
  let starsHtml='<div class="flex justify-center gap-2 my-3">';
  for(let i=1;i<=5;i++) starsHtml+=`<span class="star-input material-icons text-4xl text-slate-300 hover:text-amber-400" onclick="selectStar(this,${i})" data-star="${i}">star</span>`;
  starsHtml+='</div><p id="star-label" class="text-xs text-slate-400 text-center mt-1">Tap to rate</p>';
  descEl.innerHTML=descEl.innerHTML+starsHtml;
  document.getElementById('confirm-modal').classList.remove('hidden');
}

function selectStar(el,n){
  document.querySelectorAll('.star-input').forEach(s=>{
    const v=parseInt(s.dataset.star);
    s.textContent=v<=n?'star':'star_border';
    s.style.color=v<=n?'#FFB800':'#CBD5E1';
  });
  const labels=['','Terrible','Bad','Average','Good','Excellent!'];
  const label=document.getElementById('star-label');
  if(label){label.textContent=labels[n]||'';label.style.color='#FFB800';}
}

function closeConfirmModal(){document.getElementById('confirm-modal').classList.add('hidden');}

// ============================================================
// REORDER
// ============================================================
function doReorder(id){
  const o=ORDERS_DB.find(x=>parseInt(x.id)===id);
  if(!o)return;
  showToast((o.items||[]).length+' item(s) from '+o.order_number+' added to cart','success');
}

// ============================================================
// LOAD MORE
// ============================================================
function loadMore(){ visibleCount+=10; renderOrders(); }

// ============================================================
// KEYBOARD
// ============================================================
document.addEventListener('keydown', e=>{ if(e.key==='Escape'){closeOrderModal();closeConfirmModal();} });

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', ()=>{ renderStats(); renderTabs(); renderOrders(); });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
