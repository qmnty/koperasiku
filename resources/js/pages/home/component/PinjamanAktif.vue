<template>
  <div class="flex justify-between mb-6">
    <h1 class="text-2xl font-extrabold">Pinjaman Aktif</h1>
    <div class="flex gap-2">
      <button @click="modals.installment = true" class="cursor-pointer bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-bold flex gap-2 items-center hover:bg-amber-600"><i class="fa-solid fa-money-bill-1"></i> Bayar</button>
      <button @click="modals.loan = true" class="cursor-pointer bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold flex gap-2 items-center hover:bg-emerald-700"><i class="fa-solid fa-plus"></i> Baru</button>
    </div>
  </div>
  <div v-if="isLoading" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl shadow-2xl border border-slate-100 flex flex-col items-center gap-3">
      <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 text-3xl"></i>
      <span class="text-sm font-bold text-slate-600 tracking-wide">Memuat Data...</span>
    </div>
  </div>
  <div v-else class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm overflow-x-auto">
    <table class="w-full text-sm min-w-[600px]">
      <thead class="bg-slate-50 text-slate-500 border-slate-200 text-[10px] uppercase font-bold border-b">
        <tr>
          <th class="p-4 text-left">Anggota</th>
          <th class="p-4 text-left">Realisasi</th>
          <th class="p-4 text-left">Jatuh Tempo</th>
          <th class="p-4 text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="l in loans" :key="l.id" @click="openDetail(l)" class="hover:bg-slate-50 cursor-pointer">
          <td class="p-4 font-bold">
            {{ l.nama }}
            <div class="text-[10px] text-slate-400 font-mono">{{ l.id }}</div>
          </td>
          <td class="p-4">
            <div class="font-bold text-emerald-600">{{ formatIDR(l.realisasi) }}</div>
            <div class="text-[10px] text-slate-400">Tenor: {{ l.tenor }} bln</div>
          </td>
          <td class="p-4 text-rose-600 font-semibold">{{ l.jatuhTempo }}</td>
          <td class="p-4 text-center">
            <span :class="['px-3 py-1 rounded-full text-[10px] font-bold', l.sisaTenor === 0 ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100']">
              {{ l.sisaTenor === 0 ? 'SELESAI' : 'BERJALAN' }}
            </span>
          </td>
        </tr>
        <tr v-if="loans.length === 0"><td colspan="4" class="p-12 text-center text-slate-300 italic">Belum ada pinjaman aktif</td></tr>
      </tbody>
    </table>
  </div>
  <PinjamanAdd
    :modals="modals"
    @success="addLoans"
  />
  <BayarTagihan 
    :members = "members"
    :modals = "modals"
    :loans = "loans"
    @success="bayarSuccess"
  />
  <PinjamanDetailModal 
    :modals="props.modals"
    :loan="selectedLoan"
    :history="loanHistory"
    :loading="isLoadingHistory"
  />
</template>

<script setup>
import { formatIDR } from "@/lib/global";
import PinjamanAdd from "./PinjamanAdd.vue";
import { onMounted, ref } from "vue";
import api from "@/lib/api";
import BayarTagihan from "./BayarTagihan.vue";
import PinjamanDetailModal from "./PinjamanDetailModal.vue";
const props = defineProps({
  members: Array,
  modals: Object
})

const loans = ref([])

const isLoading = ref(null)
const selectedLoan = ref(null);
const loanHistory = ref([]);
const isLoadingHistory = ref(false);

function addLoans(newLoan)
{
  fetchPinjaman()
}

function bayarSuccess()
{
  fetchPinjaman()
}

const openDetail = async (loan) => {
  // 1. Simpan data baris yang diklik ke state lokal
  selectedLoan.value = loan;
  console.log(loan)
  
  // 2. Buka modal (Pastikan props.modals.loanDetail sudah didefinisikan di Parent utama)
  props.modals.loanDetail = true;
  
  // 3. Reset riwayat lama agar tidak muncul data milik orang lain saat loading
  loanHistory.value = [];
  isLoadingHistory.value = true;
  
  try {
    // 4. Ambil data riwayat dari API
    const response = await api.get(`pinjaman/riwayat/${loan.id}`);
    
    // 5. Masukkan ke state untuk diteruskan ke komponen PinjamanDetailModal
    loanHistory.value = response.data.history || response.data;
  } catch (error) {
    console.error("Gagal mengambil riwayat pinjaman:", error);
  } finally {
    isLoadingHistory.value = false;
  }
};

async function fetchPinjaman() {
  isLoading.value = true;
  try {
    const response = await api.get('pinjaman');
    // Sesuaikan dengan struktur return Laravel (biasanya response.data.data atau response.data)
    loans.value = response.data.data || response.data;
  } catch (error) {
    console.error("Gagal mengambil data anggota:", error);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchPinjaman();
})
</script>