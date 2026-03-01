<template>
  <div v-if="modals.memberDetail" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-2xl h-[85vh] flex flex-col overflow-hidden shadow-2xl scale-in">
      
      <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-white sticky top-0 z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-black">
            {{ modals.memberDetail.nama.charAt(0) }}
          </div>
          <div>
            <h2 class="text-lg font-black leading-none">{{ modals.memberDetail.nama }}</h2>
            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-widest">
              {{ modals.memberDetail.no_anggota }}
            </p>
          </div>
        </div>
        <button @click="modals.memberDetail = null" class="cursor-pointer text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-x"></i>
        </button>
      </div>
      
      <div class="flex-grow overflow-y-auto p-6 space-y-6">
        
        <div class="grid grid-cols-4 gap-3">
          <div v-for="item in [
            { label: 'Pokok', key: 'saldo_pokok' },                
            { label: 'Wajib', key: 'saldo_wajib' },                
            { label: 'Sukarela', key: 'total_sukarela' }, 
            { label: 'Khusus', key: 'saldo_khusus' }
          ]" :key="item.key" class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            
            <div class="text-[9px] uppercase font-bold text-slate-400 mb-1">{{ item.label }}</div>
            <div class="text-[11px] font-bold text-slate-800">
              {{ formatIDR(modals.memberDetail[item.key] || 0) }}
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <h3 class="text-xs font-black text-slate-400 uppercase">Riwayat Anggota</h3>
          </div>
          
          <div v-for="t in memberTransactions" :key="t.id" class="flex justify-between items-center p-3 border border-slate-50 rounded-xl">
            <div class="flex gap-3 items-center">
              <div :class="[
                  'p-2 rounded-lg flex items-center justify-center', 
                  t.kredit > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'
                ]">
                <i :class="['fa-solid fa-arrow-down', t.kredit > 0 ? 'rotate-225' : 'rotate-45']" style="font-size: 14px;"></i>
              </div>
              
              <div>
                <div class="text-xs font-bold">{{ t.keterangan }}</div>
                <div class="text-[9px] text-slate-400">{{ t.created_at }}</div>
              </div>
            </div>
            
            <div :class="[
              'text-sm font-black', 
              t.kredit > 0 ? 'text-rose-600' : 'text-emerald-600'
            ]">
              {{ t.kredit > 0 ? '-' : '+' }}
              {{ formatIDR(t.kredit > 0 ? t.kredit : t.debit) }}
            </div>
          </div>
          
          <div v-if="memberTransactions.length === 0" class="py-10 text-center text-slate-400 border-2 border-dashed border-slate-200 rounded-xl">
            <div class="text-xs font-semibold">Belum ada aktivitas transaksi</div>
          </div>
        </div>
      </div>
      
      <div class="p-6 bg-slate-50 border-slate-200 border-t flex justify-between items-center">
        <span class="text-[10px] font-black text-slate-400 uppercase">Total Saldo</span>
        <span class="text-xl font-black text-emerald-600">
          {{ formatIDR(
            (modals.memberDetail.saldo_pokok || 0) + 
            (modals.memberDetail.saldo_wajib || 0) + 
            (modals.memberDetail.total_sukarela || 0) + 
            (modals.memberDetail.saldo_khusus || 0)
          ) }}
        </span>
      </div>
    </div>
  </div>
</template>
<script setup>
import { formatIDR } from '@/lib/global';

const props = defineProps({
  modals: Object,
  memberTransactions: Array
})
</script>