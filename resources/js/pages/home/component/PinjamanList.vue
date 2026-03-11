<template>
  <div class="flex justify-between mb-6">
    <!-- <h1 class="text-2xl font-extrabold">Pinjaman Aktif</h1> -->
    <div class="flex flex-col lg:flex-row">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-800">Pinjaman Aktif</h1>
      </div>
      <div v-if="user.role === 'admin'">
        <!-- <Button 
          @click="importPinjamanConfirmation" 
          :disabled="onImport"
          class="mt-4 ml-2 lg:mt-0 cursor-pointer bg-green-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-2"
        >
          <template v-if="onImport">
            <i class="fas fa-spinner animate-spin"></i> Memproses...
          </template>
          <template v-else>
            Import Pinjaman
          </template>
        </Button> -->
        <div class="flex flex-row items-start gap-1">
          <TooltipButton
            @click="importPinjamanConfirmation" text="Import Pinjaman"
            class="bg-green-700 text-lg p-0.5 text-white rounded-lg hover:bg-green-400 cursor-pointer"
            :base-class="'ml-2'"
          >
            <i class="fas fa-upload"></i>
            <span class="text-[10px] font-medium mt-1">Pinjaman</span>
          </TooltipButton>
  
          <TooltipButton
            @click="importAngsuranConfirmation" text="Import Angsuran"
            class="bg-cyan-700 text-lg p-0.5 text-white rounded-lg hover:bg-cyan-400 cursor-pointer"
            :base-class="'ml-2'"
          >
            <i class="fas fa-upload"></i>
            <span class="text-[10px] font-medium mt-1">Angsuran</span>
          </TooltipButton>

          <TooltipButton
            @click="exportPinjaman" text="Export to xlsx"
            class="bg-amber-600 text-lg p-0.5 text-white rounded-lg hover:bg-amber-400 cursor-pointer"
            :base-class="'ml-2'"
          >
            <template v-if="exporting">
              <i class="fas fa-spinner animate-spin"></i>
            </template>
            <template v-else>
              <i class="fas fa-download text-xl"></i>
            </template>
            <span class="text-[10px] font-medium mt-1">Export</span>
          </TooltipButton>
        </div>
        <input 
            type="file" 
            ref="fileAngsuranInput" 
            class="hidden" 
            accept=".xlsx, .xls" 
            @change="handleFileChange" 
        />
        <input 
            type="file" 
            ref="fileAngsuranInput" 
            class="hidden" 
            accept=".xlsx, .xls" 
            @change="handleFileAngsuranChange" 
        />
      </div>
      <!-- <Button 
        @click="exportPinjaman" 
        class="mt-4 lg:mt-0 ml-2 cursor-pointer bg-cyan-600 text-white rounded-xl text-xs font-bold hover:bg-cyan-700 transition flex items-center gap-2"
      >
        <template v-if="exporting">
          <i class="fas fa-spinner animate-spin"></i>
        </template>
        <template v-else>
          <i class="fa-solid fa-file-lines text-xl"></i>
        </template>
      </Button> -->
    </div>
    <div class="flex gap-2">
      <button @click="modals.installment = true" class="cursor-pointer bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-bold flex gap-2 items-center hover:bg-amber-600"><i class="fa-solid fa-money-bill-1"></i> Bayar</button>
      <button v-if="user.role !== 'staff'" @click="modals.loan = true" class="cursor-pointer bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold flex gap-2 items-center hover:bg-emerald-700"><i class="fa-solid fa-plus"></i> Baru</button>
    </div>
  </div>
  <div class="relative mb-4">
    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
      <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
    </span>
    <input 
      v-model="searchQuery" 
      @input="handleSearch"
      type="text" 
      placeholder="Cari nama atau ID..." 
      class="pl-10 pr-4 py-2 border bg-white border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full lg:w-64"
    />
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
            <div class="text-[10px] text-slate-400 font-mono">{{ l.no_kontrak }}</div>
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
    <div class="flex gap-1 items-center py-4">
      <div class="text-center">

      </div>
      <button 
        @click="changePage(pagination.current_page - 1)" 
        :disabled="pagination.current_page === 1"
        class="px-3 py-1 cursor-pointer rounded-lg border border-slate-200 text-xs font-bold disabled:opacity-30 hover:bg-slate-100"
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
          :class="['px-3 py-1 rounded-lg cursor-pointer border text-xs font-bold transition', 
            pagination.current_page === page 
              ? 'bg-emerald-600 text-white border-emerald-600' 
              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100']"
        >
          {{ page }}
        </button>
        
        <span v-else-if="page === pagination.current_page - 2 || page === pagination.current_page + 2" class="px-2 text-slate-400">...</span>
      </template>

      <button 
        @click="changePage(pagination.current_page + 1)" 
        :disabled="pagination.current_page === pagination.last_page"
        class="px-3 py-1 rounded-lg cursor-pointer border border-slate-200 text-xs font-bold disabled:opacity-30 hover:bg-slate-100"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </div>
  <PinjamanAdd
    :modals="modals"
    @success="addLoans"
  />
  <BayarTagihan 
    :members = "members"
    :modals = "modals"
    :loans = "loans"
    :initial-loan-id="targetLoanId"
    @success="bayarSuccess"
  />
  <PinjamanDetailModal 
    :modals="props.modals"
    :loan="selectedLoan"
    :history="loanHistory"
    :loading="isLoadingHistory"
    @select-loan="onHandlePay"
  />
</template>

<script setup>
import { formatIDR } from "@/lib/global";
import PinjamanAdd from "./PinjamanAdd.vue";
import { onMounted, ref } from "vue";
import api from "@/lib/api";
import BayarTagihan from "./BayarTagihan.vue";
import PinjamanDetailModal from "./PinjamanDetailModal.vue";
import Button from "@/components/ui/button/Button.vue";
import Swal from "sweetalert2";
import TooltipButton from "@/components/TooltipButton.vue";
const props = defineProps({
  members: Array,
  modals: Object,
  user: Object
})

const loans = ref([])

const isLoading = ref(null)
const selectedLoan = ref(null);
const loanHistory = ref([]);
const isLoadingHistory = ref(false);
const onImport = ref(false)
const exporting = ref(false)
const fileInput = ref(null);
const fileAngsuranInput = ref(false)
const searchQuery = ref('')
const targetLoanId = ref(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0
})

function addLoans(newLoan)
{
  fetchPinjaman()
  Swal.fire({
    icon: 'success',
    toast: true,
    timer: 2500,
    timerProgressBar: true,
    text: 'Pinjaman baru berhasil disimpan',
    showConfirmButton: false
  })
}

function bayarSuccess()
{
  fetchPinjaman()
  Swal.fire({
    icon: 'success',
    toast: true,
    timer: 2500,
    timerProgressBar: true,
    text: 'Pembayaran berhasil disimpan',
    showConfirmButton: false
  })
}

function onHandlePay(value)
{
  targetLoanId.value = value
}

const openDetail = async (loan) => {
  // 1. Simpan data baris yang diklik ke state lokal
  selectedLoan.value = loan;
  
  // 2. Buka modal (Pastikan props.modals.loanDetail sudah didefinisikan di Parent utama)
  props.modals.loanDetail = true;
  
  // 3. Reset riwayat lama agar tidak muncul data milik orang lain saat loading
  loanHistory.value = [];
  isLoadingHistory.value = true;
  
  try {
    // 4. Ambil data riwayat dari API
    const response = await api.get(`pinjaman/riwayat/${loan.no_kontrak}`);
    
    // 5. Masukkan ke state untuk diteruskan ke komponen PinjamanDetailModal
    loanHistory.value = response.data.history || response.data;
  } catch (error) {
    console.error("Gagal mengambil riwayat pinjaman:", error);
  } finally {
    isLoadingHistory.value = false;
  }
};

async function fetchPinjaman(page = 1) {
  isLoading.value = true;
  try {
    const response = await api.get('pinjaman', {
      params: {
        page: page,
        search: searchQuery.value
      }
    });
    
    // Asumsi Laravel mengembalikan: response.data.data (array hasil) dan fields pagination lainnya
    if (response.data.data && Array.isArray(response.data.data)) {
        loans.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } else {
        // Jika backend tidak pakai pagination standar Laravel
        loans.value = response.data;
    }
  } catch (error) {
    console.error("Gagal mengambil data anggota:", error);
  } finally {
    isLoading.value = false;
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchPinjaman(page);
  }
};

// Handler Search (dengan simple debounce)
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPinjaman(1); // Kembali ke hal 1 saat cari
  }, 500);
};

const importPinjamanConfirmation = async () => {
    if (onImport.value) return;

    const result = await Swal.fire({
        title: 'Konfirmasi Import',
        text: "Pilih file .xlsx untuk mengimpor data pinjaman.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Pilih File',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a', // Green-600
        borderRadius: '1.5rem'
    });

    if (result.isConfirmed) {
        // Memicu jendela file browser muncul
        fileInput.value.click();
    }
};

const importAngsuranConfirmation = async () => {
    if (onImport.value) return;

    const result = await Swal.fire({
        title: 'Konfirmasi Import',
        text: "Pilih file .xlsx untuk mengimpor data angsuran.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Pilih File',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a', // Green-600
        borderRadius: '1.5rem'
    });

    if (result.isConfirmed) {
        // Memicu jendela file browser muncul
        fileAngsuranInput.value.click();
    }
};

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Mulai proses upload
    onImport.value = true;
    
    const formData = new FormData();
    formData.append('file', file);

    try {
        Swal.fire({
            title: 'Sedang Mengunggah',
            text: 'Harap tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const response = await api.post('/pinjaman/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        Swal.fire({
            title: 'Sukses!',
            text: response.data.message || 'Data berhasil diimpor.',
            icon: 'success',
            borderRadius: '1.5rem'
        });
    } catch (error) {
        Swal.fire({
            title: 'Gagal',
            text: error.response?.data?.message || 'Terjadi kesalahan saat mengunggah.',
            icon: 'error'
        });
    } finally {
        onImport.value = false;
        // Penting: Reset input agar file yang sama bisa dipilih kembali jika perlu
        event.target.value = '';
    }
};

const handleFileAngsuranChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Mulai proses upload
    onImport.value = true;
    
    const formData = new FormData();
    formData.append('file', file);

    try {
        Swal.fire({
            title: 'Sedang Mengunggah',
            text: 'Harap tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const response = await api.post('/pinjaman/angsuran/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        Swal.fire({
            title: 'Sukses!',
            text: response.data.message || 'Data berhasil diimpor.',
            icon: 'success',
            borderRadius: '1.5rem'
        });
    } catch (error) {
        Swal.fire({
            title: 'Gagal',
            text: error.response?.data?.message || 'Terjadi kesalahan saat mengunggah.',
            icon: 'error'
        });
    } finally {
        onImport.value = false;
        // Penting: Reset input agar file yang sama bisa dipilih kembali jika perlu
        event.target.value = '';
    }
};

async function exportPinjaman() {
  exporting.value = true;
  try {
    // Gunakan axios untuk mendownload file sebagai blob
    const response = await api.get('pinjaman/export', {
      responseType: 'blob' 
    });

    // Proses download di browser
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Pinjaman Koperasi.xlsx`);
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

onMounted(() => {
  fetchPinjaman();
})
</script>