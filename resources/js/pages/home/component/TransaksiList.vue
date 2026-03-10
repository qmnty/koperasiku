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
        <select 
          v-model="filters.kategori" 
          class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none appearance-none cursor-pointer"
        >
          <option value="">Semua</option>
          <option v-for="opt in kategori" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>
      <div class="relative mb-4">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
          <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
        </span>
        <input 
          v-model="search" 
          @input="handleSearch"
          type="text" 
          placeholder="Cari nama..." 
          class="pl-10 pr-4 py-2 border bg-white border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full lg:w-64"
        />
      </div>
    </div>

    <div class="space-y-3">
      <div v-if="loading" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm">
        <div class="bg-white p-6 rounded-2xl shadow-2xl border border-slate-100 flex flex-col items-center gap-3">
          <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 text-3xl"></i>
          <span class="text-sm font-bold text-slate-600 tracking-wide">Memuat Data...</span>
        </div>
      </div>
      <div v-else>
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
    <div v-if="pagination.last_page > 1" class="flex justify-center mt-6 items-center gap-1">
      <button 
        @click="changePage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="px-3 py-2 cursor-pointer rounded-xl bg-white border border-slate-200 text-xs font-bold hover:bg-slate-50 transition disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      
      <template v-for="page in pagination.last_page" :key="page">
        <button 
          v-if="
            page === 1 || 
            page === pagination.last_page || 
            (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)
          "
          @click="changePage(page)"
          :class="[
            'px-4 cursor-pointer py-2 rounded-xl border text-xs font-bold transition',
            pagination.current_page === page 
              ? 'bg-emerald-600 text-white border-emerald-600' 
              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
          ]"
        >
          {{ page }}
        </button>
        
        <span 
          v-else-if="page === pagination.current_page - 2 || page === pagination.current_page + 2" 
          class="px-2 text-slate-400 text-xs font-bold"
        >
          ...
        </span>
      </template>

      <button 
        @click="changePage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 cursor-pointer py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold hover:bg-slate-50 transition disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, reactive, watch, computed } from 'vue';
import api from '@/lib/api';
import { formatIDR } from '@/lib/global';
import { ArrowUpRight, ArrowDownLeft } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const kategori = ref([])
const transactions = ref([])
const loading = ref(false)
const pagination = ref(false)
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

async function fetchTransaksi(page = 1) {
  loading.value = true;
  try {
    const res = await api.get('transaksi', {
      params: { ...filters, page }
    });
    transactions.value = res.data.data;
    pagination.value = res.data;
    
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

async function fetchKategori() {
  try {
    const res = await api.get('/transaksi/kategori');
    kategori.value = res.data;
  } catch (error) {
    console.error("Gagal memuat kategori:", error);
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchTransaksi(page);
  }
};

watch(() => filters.search, debounce(() => fetchTransaksi(1), 500));
watch(() => filters.kategori, () => fetchTransaksi(1));
watch(() => filters.kelompok, () => fetchTransaksi(1));
watch(() => [filters.start_date, filters.end_date], () => {
  fetchTransaksi(1);
});

onMounted(() => {
  fetchTransaksi();
  fetchKategori()
});
</script>