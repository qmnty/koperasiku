<template>
  <div class="space-y-3">
    <div v-if="loading" class="text-center py-8">
      <i class="fa-solid fa-circle-notch animate-spin text-slate-300 text-2xl"></i>
    </div>
    
    <div v-else-if="history.length > 0" class="space-y-2">
      <div v-for="(h, i) in history" :key="i" 
           class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-emerald-200 transition">
        <div class="flex items-center gap-4">
          <div class="p-2 bg-white rounded-xl shadow-sm group-hover:bg-emerald-500 group-hover:text-white transition">
            <i :class="[
              'text-sm',
              h.payment_method === 'cash' ? 'fas fa-money-bill' : 'far fa-credit-card'
              ]"
            >
            </i>
          </div>
          <div>
            <p class="text-xs font-black text-slate-700">Angsuran Ke-{{ h.angsuran_ke }}</p>
            <p class="text-[10px] text-slate-400 font-mono">{{ h.tanggal_bayar }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-xs font-black text-emerald-600">{{ formatIDR(h.total_bayar) }}</p>
          <span v-if="h.is_lunas" class="text-[8px] bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded font-bold uppercase">Pelunasan</span>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-10 border-2 border-dashed border-slate-100 rounded-3xl">
      <p class="text-xs text-slate-400 italic">Belum ada riwayat pembayaran.</p>
    </div>
  </div>
</template>

<script setup>
import { formatIDR } from "@/lib/global";
defineProps({
  history: Array,
  loading: Boolean
});
</script>