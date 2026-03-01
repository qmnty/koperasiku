<template>
  <div v-if="modals.tarik" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl scale-in border border-slate-100">
      
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black flex items-center gap-2 text-rose-600">
          <i class="fa-solid fa-money-bill-transfer"></i> Tarik Tunai
        </h2>
        <button @click="modals.tarik = false" class="bg-slate-100 text-slate-400 w-8 h-8 rounded-full hover:bg-rose-50 hover:text-rose-500 transition cursor-pointer">
          <i class="fa-solid fa-xmark text-xs"></i>
        </button>
      </div>

      <div class="bg-slate-50 rounded-3xl p-5 mb-6 border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Total Dana Tersedia</p>
        <h3 class="text-2xl font-black text-slate-800">{{ formatIDR(totalSaldo) }}</h3>
      </div>

      <form @submit.prevent="handleSaving" class="space-y-6">
        <div class="space-y-3">
          <div class="flex justify-between items-center ml-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-tight">Nominal Penarikan</label>
            
            <label class="flex items-center gap-2 cursor-pointer group">
              <input 
                type="checkbox" 
                v-model="isAllSelected"
                class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
              >
              <span class="text-[10px] font-black text-slate-400 group-hover:text-rose-600 transition">AMBIL SEMUA</span>
            </label>
          </div>

          <div class="relative">
            <input 
              v-model="savingForm.nominal" 
              type="number" 
              required 
              :disabled="isAllSelected"
              placeholder="0"
              @keydown="filterKey"
              class="w-full border-2 border-slate-100 rounded-2xl py-5 px-6 text-2xl font-black bg-white text-rose-600 focus:border-rose-500 outline-none transition disabled:bg-slate-50 disabled:text-rose-400"
            >
            <span v-if="isAllSelected" class="absolute right-6 top-1/2 -translate-y-1/2 bg-rose-100 text-rose-600 text-[10px] font-black px-3 py-1 rounded-lg">MAX</span>
          </div>
        </div>

        <div v-if="isFullWithdrawal" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3 animate-pulse">
          <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-1"></i>
          <div>
            <p class="text-xs font-black text-amber-700 uppercase leading-none mb-1">Status Keanggotaan</p>
            <p class="text-[10px] text-amber-600 leading-relaxed font-medium">
              Anda akan menarik seluruh dana. Akun akan diubah menjadi <strong class="underline">TIDAK AKTIF</strong>.
            </p>
          </div>
        </div>

        <button 
          type="submit" 
          :disabled="savingForm.nominal <= 0"
          class="w-full cursor-pointer py-5 bg-rose-600 text-white rounded-2xl font-black shadow-lg shadow-rose-200 hover:bg-rose-700 transition active:scale-[0.98] disabled:bg-slate-200 disabled:shadow-none"
        >
          KONFIRMASI TARIK TUNAI
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import api from '@/lib/api';
import { reactive, computed, watch, ref } from 'vue';
import { formatIDR } from '@/lib/global';

const props = defineProps({
  modals: Object,
  memberId: Number,
  // Tambahkan prop saldo untuk info & validasi
  currentMember: Object 
})

const emit = defineEmits(['success']);

const savingForm = reactive({ nominal: 0 });
const isAllSelected = ref(false);

watch(isAllSelected, (val) => {
  if (val) {
    savingForm.nominal = totalSaldo.value;
  }
});

watch(() => savingForm.nominal, (newVal) => {
  const cleaned = String(newVal).replace(/[^0-9]/g, '');
  let finalVal = Number(cleaned);
  if (finalVal > totalSaldo.value) {
    finalVal = totalSaldo.value;
  }
  savingForm.nominal = finalVal;

  isAllSelected.value = newVal === totalSaldo.value && totalSaldo.value > 0;
});

// Hitung total saldo yang bisa ditarik
const totalSaldo = computed(() => {
  if (!props.currentMember) return 0;
  return Number(props.currentMember.wajib) + 
         Number(props.currentMember.sukarela) + 
         Number(props.currentMember.pokok) +
         Number(props.currentMember.khusus);
});

// Cek apakah penarikan akan mengosongkan saldo
const isFullWithdrawal = computed(() => {
  return savingForm.nominal >= totalSaldo.value && totalSaldo.value > 0;
});

const handleSaving = async () => {
  if (savingForm.nominal > totalSaldo.value) {
    alert("Saldo tidak mencukupi!");
    return;
  }

  try {
    let res = await api.post('anggota/simpanan/tarik-store', {
      memberId: props.memberId,
      nominal: Number(savingForm.nominal),
      is_closing: isFullWithdrawal.value // Beri tahu backend jika ini penutupan akun
    });

    if (res.status === 200 || res.status === 201) {
      emit('success', res.data.data);
      props.modals.tarik = false;
      savingForm.nominal = 0;
    }
  } catch (error) {
    console.error(error);
    alert(error.response?.data?.message || "Gagal melakukan penarikan");
  }
};
</script>