<template>
  <div class="space-y-6">
    <button 
      @click="handleExport" 
      :disabled="exporting"
      class="cursor-pointer bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-2xl text-sm font-bold flex items-center gap-2 transition disabled:opacity-50"
    >
      <i v-if="exporting" class="fa-solid fa-spinner animate-spin"></i>
      <i v-else class="fa-solid fa-file-excel"></i>
      {{ exporting ? 'Mengunduh...' : 'Export Excel' }}
    </button>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-3xl border border-slate-200">
      
      <div class="flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-400 ml-2 uppercase">Mulai</label>
        <input 
          v-model="filters.start_date" 
          type="date" 
          class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
        >
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-400 ml-2 uppercase">Sampai</label>
        <input 
          v-model="filters.end_date" 
          type="date" 
          class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
        >
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-400 ml-2 uppercase">Kelompok</label>
        <select v-model="filters.kelompok" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none appearance-none cursor-pointer">
          <option value="">Semua</option>
          <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
        </select>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-[10px] font-bold text-slate-400 ml-2 uppercase">Kategori</label>
        <select v-model="filters.kategori" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none appearance-none cursor-pointer">
          <option value="">Semua</option>
          <option value="setor">Setoran</option>
          <option value="tarik">Penarikan</option>
          <option value="pinjaman">Pinjaman</option>
          <option value="angsuran">Angsuran</option>
        </select>
      </div>
    </div>

    <div class="space-y-3">
      <div v-for="t in transactions" :key="t.id" class="bg-white p-4 rounded-2xl border border-slate-100 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-4">
          <div :class="['p-3 rounded-xl', t.is_keluar ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600']">
            <ArrowUpRight v-if="t.is_keluar" :size="20" />
            <ArrowDownLeft v-else :size="20" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-800">{{ t.nama }}</span>
              <span class="text-[9px] font-bold text-slate-400 italic">({{ t.kelompok }})</span>
              <span class="text-[8px] font-black px-1.5 py-0.5 rounded uppercase bg-slate-100 text-slate-500">{{ t.tipe }}</span>
            </div>
            <div class="text-[10px] text-slate-400">{{ t.tanggal }} • {{ t.notes }}</div>
          </div>
        </div>
        <div :class="['font-black text-right', t.is_keluar ? 'text-rose-600' : 'text-emerald-600']">
          {{ t.is_keluar ? '-' : '+' }}{{ formatIDR(t.nominal) }}
        </div>
      </div>
      </div>
  </div>
</template>

<script setup>
import { onMounted, ref, reactive, watch, computed } from 'vue';
import api from '@/lib/api';
import { formatIDR } from '@/lib/global';
import { ArrowUpRight, ArrowDownLeft } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const transactions = ref([]);
const loading = ref(false);
const filters = reactive({
  search: '',
  kategori: '',
  kelompok: '',
  start_date: '',
  end_date: ''
});

// Mengambil daftar kelompok unik dari data transaksi yang sedang tampil
// Atau Anda bisa fetch daftar kelompok dari API khusus anggota
const groups = ref([]);

const exporting = ref(false);

async function handleExport() {
  exporting.value = true;
  try {
    // Gunakan axios untuk mendownload file sebagai blob
    const response = await api.get('transaksi/export', {
      params: filters, // Kirim filter yang sedang aktif
      responseType: 'blob' 
    });

    // Proses download di browser
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Laporan_Transaksi_${new Date().getTime()}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error("Gagal export:", error);
    alert("Terjadi kesalahan saat mengunduh data.");
  } finally {
    exporting.value = false;
  }
}

async function fetchTransaksi() {
  loading.value = true;
  try {
    const res = await api.get('transaksi', {
      params: filters
    });
    transactions.value = res.data;
    
    // Update daftar kelompok jika belum ada (hanya jika ingin dinamis dari data yang ada)
    if (groups.value.length === 0) {
       const uniqueGroups = [...new Set(res.data.map(item => item.kelompok))];
       groups.value = uniqueGroups.sort();
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

watch(() => filters.search, debounce(() => fetchTransaksi(), 500));
watch(() => filters.kategori, () => fetchTransaksi());
watch(() => filters.kelompok, () => fetchTransaksi());
watch(() => [filters.start_date, filters.end_date], () => {
  fetchTransaksi();
});

onMounted(() => {
  fetchTransaksi();
});
</script>