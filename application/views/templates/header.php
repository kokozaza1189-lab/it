<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= isset($title) ? htmlspecialchars($title) : 'IT Finance System' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ['Sarabun','sans-serif'] },
        colors: {
          navy: { 900:'#0f172a', 800:'#1e293b', 700:'#334155' }
        }
      }
    }
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
*{ font-family:'Sarabun',sans-serif; box-sizing:border-box }
body{ margin:0; background:#f0f2f8 }
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}

/* Layout */
.sidebar{width:260px;height:100vh;background:#0f172a;position:fixed;left:0;top:0;z-index:70;transition:transform .3s;display:flex;flex-direction:column}
.main{margin-left:260px;min-height:100vh}
@media(max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0;padding-bottom:80px}}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:69;display:none}
.overlay.show{display:block}

/* Nav */
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:10px;cursor:pointer;color:#94a3b8;font-size:14px;font-weight:500;transition:all .2s;margin:2px 8px;text-decoration:none;overflow:hidden;white-space:nowrap}
.nav-item:hover{background:rgba(255,255,255,.07);color:#e2e8f0}
.nav-item.active{background:#3b82f6;color:white;box-shadow:0 4px 12px rgba(59,130,246,.4)}
.nav-item .nav-icon{flex-shrink:0;font-size:17px;width:20px;text-align:center;line-height:1}
.nav-item .nav-label{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis}
.nav-section{color:#475569;font-size:10px;font-weight:700;letter-spacing:1px;padding:12px 24px 4px;text-transform:uppercase}

/* Cards */
.card{background:white;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:20px}
.card-sm{background:white;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:16px}
.kpi{background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.07);position:relative;overflow:hidden}

/* Badges */
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.b-paid{background:#d1fae5;color:#065f46}
.b-overdue{background:#fee2e2;color:#b91c1c}
.b-pending{background:#fef3c7;color:#b45309}
.b-none{background:#f1f5f9;color:#64748b}
.b-draft{background:#f1f5f9;color:#64748b}
.b-submitted{background:#dbeafe;color:#1d4ed8}
.b-approved{background:#d1fae5;color:#065f46}
.b-rejected{background:#fee2e2;color:#b91c1c}
.b-completed{background:#ede9fe;color:#6d28d9}

/* Table */
.tbl{width:100%;border-collapse:collapse}
.tbl th{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;padding:11px 14px;background:#f8fafc;border-bottom:1px solid #f1f5f9;text-align:left;white-space:nowrap}
.tbl td{padding:11px 14px;font-size:13.5px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover td{background:#f8fafc}

/* Inputs */
.inp{width:100%;border:1.5px solid #e2e8f0;border-radius:9px;padding:9px 13px;font-size:14px;font-family:'Sarabun',sans-serif;outline:none;transition:all .2s;color:#0f172a;background:white}
.inp:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
select.inp{background:white;cursor:pointer}
.lbl{display:block;font-size:12.5px;font-weight:600;color:#64748b;margin-bottom:5px}

/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;font-family:'Sarabun',sans-serif;font-weight:600;font-size:13.5px;padding:9px 20px;border-radius:9px;border:none;cursor:pointer;transition:all .2s;white-space:nowrap;text-decoration:none}
.btn:disabled{opacity:.55;cursor:not-allowed}
.btn-blue{background:#3b82f6;color:white}.btn-blue:hover:not(:disabled){background:#2563eb}
.btn-green{background:#10b981;color:white}.btn-green:hover:not(:disabled){background:#059669}
.btn-red{background:#ef4444;color:white}.btn-red:hover:not(:disabled){background:#dc2626}
.btn-violet{background:#8b5cf6;color:white}.btn-violet:hover:not(:disabled){background:#7c3aed}
.btn-gray{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0}.btn-gray:hover:not(:disabled){background:#e2e8f0}
.btn-sm{padding:6px 14px;font-size:12px;border-radius:7px}
.btn-xs{padding:4px 10px;font-size:11px;border-radius:6px}
.btn-icon{padding:8px;border-radius:8px;background:#f1f5f9;color:#64748b;border:none;cursor:pointer;font-size:16px;transition:all .2s}.btn-icon:hover{background:#e2e8f0}

/* Modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;align-items:flex-end;justify-content:center;padding:0}
@media(min-width:640px){.modal-bg{align-items:center;padding:20px}}
.modal-box{background:white;border-radius:20px 20px 0 0;width:100%;max-width:680px;max-height:95vh;overflow-y:auto}
@media(min-width:640px){.modal-box{border-radius:16px}}
.modal-header{padding:20px 24px 16px;border-bottom:1px solid #f1f5f9;position:sticky;top:0;background:white;z-index:10;border-radius:20px 20px 0 0}
@media(min-width:640px){.modal-header{border-radius:16px 16px 0 0}}
.modal-body{padding:20px 24px}
.modal-footer{padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:10px;position:sticky;bottom:0;background:white}
@media(max-width:640px){.modal-body{padding:14px 16px}.modal-footer{padding:12px 16px}.modal-header{padding:16px 16px 12px}}

/* KPI icon */
.kpi-icon{font-size:36px;position:absolute;right:16px;top:14px;opacity:.12}

/* Progress / stat */
.progress-bar{height:8px;border-radius:8px;background:#e2e8f0;overflow:hidden}
.progress-fill{height:100%;border-radius:8px;transition:width .5s ease}
.dot{width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0}

/* Month grid cells */
.month-cell{border-radius:10px;padding:10px 8px;text-align:center;font-size:12px;cursor:pointer;transition:all .2s;border:1.5px solid transparent}
.month-cell:hover{transform:translateY(-1px)}
.mc-paid{background:#d1fae5;border-color:#6ee7b7;color:#065f46}
.mc-overdue{background:#fee2e2;border-color:#fca5a5;color:#b91c1c}
.mc-pending{background:#fef3c7;border-color:#fcd34d;color:#92400e}
.mc-advance{background:#dbeafe;border-color:#93c5fd;color:#1e40af}
.mc-none{background:#f8fafc;border-color:#e2e8f0;color:#94a3b8}

/* Toast */
#toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;display:none;padding:12px 24px;border-radius:16px;color:white;font-weight:600;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.2);white-space:nowrap}
@media(max-width:768px){#toast{bottom:88px}}

/* Mobile bottom nav */
.bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:#0f172a;z-index:60;border-top:1px solid rgba(255,255,255,.08);padding:6px 0 calc(6px + env(safe-area-inset-bottom,0px))}
@media(max-width:768px){.bottom-nav{display:flex}}
.bnav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:4px 2px;cursor:pointer;color:#64748b;border:none;background:none;font-family:'Sarabun',sans-serif;font-size:10px;font-weight:500;text-decoration:none}
.bnav-item.active{color:#3b82f6}
.bnav-item .bicon{font-size:22px;line-height:1}
.bnav-badge{position:relative;display:inline-block}
.bnav-badge::after{content:attr(data-count);position:absolute;top:-4px;right:-8px;background:#ef4444;color:white;font-size:8px;font-weight:700;border-radius:10px;padding:1px 4px;min-width:14px;text-align:center}

/* Responsive helpers */
.mob-only{display:block}
.desk-only{display:none}
@media(min-width:640px){.mob-only{display:none!important}.desk-only{display:block!important}}
.mob-flex{display:flex}
@media(min-width:640px){.mob-flex{display:none!important}}
.hide-on-mobile{display:block}
@media(max-width:768px){.hide-on-mobile{display:none!important}}

/* Page wrap */
.page-wrap{padding:12px}
@media(min-width:640px){.page-wrap{padding:20px}}

/* FAB */
.fab{position:fixed;right:18px;bottom:88px;width:52px;height:52px;border-radius:50%;background:#3b82f6;color:white;font-size:26px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(59,130,246,.5);border:none;cursor:pointer;z-index:50;transition:transform .15s;text-decoration:none}
@media(min-width:769px){.fab{display:none}}
.fab:active{transform:scale(.93)}

/* Mobile card list item */
.mcard{padding:14px 16px;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .15s;display:flex;align-items:center;gap:12px}
.mcard:last-child{border-bottom:none}
.mcard:active{background:#f8fafc}

/* Animations */
.fade-enter-active,.fade-leave-active{transition:opacity .2s}
.fade-enter-from,.fade-leave-to{opacity:0}
.slide-up-enter-active,.slide-up-leave-active{transition:all .3s ease}
.slide-up-enter-from,.slide-up-leave-to{opacity:0;transform:translateY(24px)}
@keyframes spin{to{transform:rotate(360deg)}}
.spin{animation:spin .7s linear infinite;display:inline-block}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.pulse{animation:pulse 2s infinite}
</style>
</head>
<body>
