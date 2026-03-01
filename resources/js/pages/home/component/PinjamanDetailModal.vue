<template>
  <div v-if="props.modals.loanDetail && loan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
      
      <div class="p-8 pb-4 flex justify-between items-start">
        <div>
          <span class="text-[10px] font-black bg-emerald-100 text-emerald-600 px-2 py-1 rounded-lg uppercase tracking-wider">Detail Pinjaman</span>
          <h2 class="text-2xl font-black text-slate-800 mt-2">{{ loan.nama }}</h2>
          <p class="text-xs text-slate-400 font-mono">{{ loan.id }}</p>
        </div>
        <button @click="props.modals.loanDetail = false" class="bg-slate-100 text-slate-400 w-10 h-10 rounded-full hover:bg-rose-50 hover:text-rose-500 transition cursor-pointer">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-8 pt-4 overflow-y-auto custom-scroll">
        <div class="grid grid-cols-2 gap-3 mb-8">
          <div class="p-4 bg-slate-50 rounded-3xl border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Sisa Tenor</p>
            <p class="text-lg font-black text-slate-700">{{ loan.sisaTenorManual }} <span class="text-xs text-slate-400">/ {{ loan.tenor }} Bln</span></p>
          </div>
          <div class="p-4 bg-rose-50 rounded-3xl border border-rose-100">
            <p class="text-[10px] font-bold text-rose-400 uppercase mb-1">Sisa Tagihan</p>
            <p class="text-lg font-black text-rose-600">{{ formatIDR(loan.sisaTagihan) }}</p>
          </div>
        </div>

        <h3 class="font-black text-slate-800 mb-4 flex items-center gap-2">
          <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Riwayat Angsuran
        </h3>
        <PinjamanRiwayat :history="history" :loading="loading" />
      </div>

      <div class="p-8 bg-slate-50/50 border-t border-slate-100">
        <button 
          @click="handlePay"
          class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-emerald-200 hover:bg-emerald-700 active:scale-95 transition cursor-pointer">
          BAYAR ANGSURAN SEKARANG
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatIDR } from "@/lib/global";
import PinjamanRiwayat from "./PinjamanRiwayat.vue";

const props = defineProps({
  modals: Object,
  loan: Object,
  history: Array,
  loading: Boolean
});

const handlePay = () => {
  props.modals.loanDetail = false;
  props.modals.installment = true;
};
</script>