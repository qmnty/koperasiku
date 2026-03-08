<template>
  <div v-if="isLoadingRiwayat" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl shadow-2xl border border-slate-100 flex flex-col items-center gap-3">
      <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 text-3xl"></i>
      <span class="text-sm font-bold text-slate-600 tracking-wide">Memuat Data...</span>
    </div>
  </div>
  <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
    <div class="flex flex-col lg:flex-row">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-800">Data Anggota</h1>
        <p class="text-xs text-slate-500 font-medium">Total: {{ memberList.length }} Anggota ditemukan</p>
      </div>
      <div v-if="user.role === 'admin'">
        <Button 
          @click="importAnggotaConfirmation" 
          :disabled="onImport"
          class="mt-4 m-2 lg:mt-0 cursor-pointer bg-green-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-2"
        >
          <template v-if="onImport">
            <i class="fas fa-spinner animate-spin"></i> Memproses...
          </template>
          <template v-else>
            Import Anggota
          </template>
        </Button>

        <input 
            type="file" 
            ref="fileInput" 
            class="hidden" 
            accept=".xlsx, .xls" 
            @change="handleFileChange" 
        />
      </div>
      <Button 
        @click="exportAnggota" 
        class="mt-4 lg:mt-0 ml-2 cursor-pointer bg-cyan-600 text-white rounded-xl text-xs font-bold hover:bg-cyan-700 transition flex items-center gap-2"
      >
        <template v-if="exporting">
          <i class="fas fa-spinner animate-spin"></i>
        </template>
        <template v-else>
          <i class="fa-solid fa-file-lines text-xl"></i>
        </template>
      </Button>
    </div>
    
    <div class="flex flex-wrap gap-2 w-full lg:w-auto">
      <select 
        v-model="selectedStatus"
        class="bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition cursor-pointer"
      >
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="tidak_aktif">Tidak Aktif</option>
      </select>
      <div class="w-64">
        <Dropdown 
          baseClass="bg-white!"
          v-model="selectedGroup"
          :options="groups"
          placeholder="Cari PJ Kelompok..."
          @change="fetchAnggota" 
        />
      </div>
      <!-- <select 
        v-model="selectedGroup"
        class="bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition cursor-pointer"
      >
        <option value="">Semua Kelompok</option>
        <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
      </select> -->

      <div class="relative flex-grow sm:flex-grow-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-2.5 text-slate-400">
          <path d="m21 21-4.34-4.34"></path>
          <circle cx="11" cy="11" r="8"></circle>
        </svg>
        <input 
          type="text" 
          v-model="searchTerm" 
          placeholder="Cari nama..." 
          class="w-full sm:w-64 border-slate-200 pl-10 pr-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition bg-white"
        >
      </div>

      <button v-if="user.role !== 'staff'" @click="modals.member = true" class="bg-emerald-600 text-white px-4 py-2 rounded-xl cursor-pointer hover:bg-emerald-700 transition flex items-center gap-2 font-bold text-sm">
        <i class="fa-solid fa-plus text-xs"></i>
        <!-- <span class="hidden sm:inline">Anggota</span> -->
      </button>
    </div>
  </div>

  <div v-if="isLoading" class="text-center py-10 text-slate-500">
    Memuat data...
  </div>
  <div v-else>
    <div v-if="memberList.length" class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div v-for="m in memberList" :key="m.id" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 @click="showMemberRiwayat(m.id)" class="font-bold text-lg cursor-pointer group-hover:text-emerald-600 transition flex items-center gap-1">
              {{ m.nama }}<i class="fa-solid fa-circle-info"></i>
            </h3>
            <span 
              @click="user.role !== 'staff' && toggleStatus(m)"
              :class="[
                'text-[9px] px-2 py-0.5 rounded-full font-black uppercase tracking-wider border transition-all',
                // Tambahkan cursor pointer hanya jika bukan staff
                user.role !== 'staff' ? 'cursor-pointer hover:opacity-80 active:scale-95' : 'cursor-default',
                
                m.status === 'aktif' 
                  ? 'bg-emerald-50 text-emerald-600 border-emerald-100' 
                  : 'bg-slate-100 text-slate-500 border-slate-200'
              ]"
              :title="user.role === 'staff' ? 'Anda tidak memiliki akses' : 'Klik untuk ubah status'"
            >
              {{ m.status || 'aktif' }}
            </span>
            <p class="text-[10px] text-slate-400 font-mono">ID: {{ m.no_anggota }}</p>
          </div>
          <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded">Kelompok: {{ m.pj }}</span>
        </div>
        <div class="text-xs space-y-2 text-slate-500">
          <div class="flex justify-between border-t border-slate-300 pt-2"><span>Wajib</span><span class="text-slate-900 font-bold">{{ formatIDR(m.wajib) }}</span></div>
          <div class="flex justify-between"><span>Sukarela</span><span class="text-slate-900 font-bold">{{ formatIDR(m.sukarela) }}</span></div>
          <div class="flex justify-between"><span>Khusus</span><span class="text-slate-900 font-bold">{{ formatIDR(m.khusus) }}</span></div>
          <div class="flex justify-between"><span>Pokok</span><span class="text-slate-900 font-bold">{{ formatIDR(m.pokok) }}</span></div>
          <div class="border-t border-slate-300 pt-2 flex justify-between text-sm font-black text-emerald-600">
            <span>Total</span><span>{{ formatIDR(m.pokok + m.wajib + m.sukarela + m.khusus) }}</span>
          </div>
        </div>
        <div class="mt-4 flex gap-2">
          <button v-if="m.status === 'aktif' && user.role !== 'staff'" @click="selectedMemberId = m.id; modals.tarik = true" class="cursor-pointer flex-1 bg-red-600 text-white py-2 rounded-xl text-xs font-bold hover:bg-emerald-700 transition">Tarik</button>
          <button v-if="m.status === 'aktif'" @click="selectedMemberId = m.id; modals.saving = true" class="cursor-pointer flex-1 bg-emerald-600 text-white py-2 rounded-xl text-xs font-bold hover:bg-emerald-700 transition">Setor</button>
          <button @click="showMemberRiwayat(m.id)" class="cursor-pointer flex-1 bg-slate-100 text-slate-600 py-2 rounded-xl text-xs font-bold hover:bg-slate-200 transition">Detail</button>
        </div>
      </div>
      <div v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <span class="text-xs text-slate-500 font-bold">
          Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
        </span>
        
        <div class="flex gap-2">
          <button 
            @click="fetchAnggota(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="cursor-pointer px-4 py-2 rounded-xl border border-slate-200 text-xs font-black disabled:opacity-30 hover:bg-slate-50 transition"
          >
            Sebelumnya
          </button>
          
          <button 
            @click="fetchAnggota(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="cursor-pointer px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-black disabled:opacity-30 hover:bg-emerald-700 transition"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
    <div v-else class="text-center py-12 text-slate-500 bg-white rounded-2xl border border-slate-200">
      <h3 class="text-lg font-semibold text-slate-700">Data Tidak Ditemukan</h3>
      <p class="text-sm">Tidak ada data yang tersedia untuk ditampilkan saat ini.</p>
    </div>
  </div>
  <AnggotaSetor
    :modals="props.modals"
    :memberId="selectedMemberId"
    @success="setorOnSuccess"
  />
  <AnggotaTarikTunai
    :modals="props.modals"
    :memberId="selectedMemberId"
    :currentMember="memberList.find(m => m.id === selectedMemberId)"
    @success="tarikOnSuccess"
  />
  <AnggotaDetail
    :modals="props.modals"
    :memberTransactions="memberTransactions"
  />
  <AnggotaAdd
    :modals="props.modals"
    @success="fetchAnggota"
  />
</template>

<script setup>
import api from '@/lib/api';
import { onMounted, ref, computed, watch } from 'vue';
import Swal from 'sweetalert2';
import { formatIDR } from '@/lib/global';
import AnggotaDetail from './AnggotaDetail.vue';
import AnggotaSetor from './AnggotaSetor.vue';
import AnggotaTarikTunai from './AnggotaTarikTunai.vue';
import AnggotaAdd from './AnggotaAdd.vue';
import Button from '@/components/ui/button/Button.vue';
import Dropdown from '@/components/Dropdown.vue';
import { debounce } from 'lodash';

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const props = defineProps({
  modals: {
    type: Object,
    required: true
  },
  user: Object
});

const isLoading = ref(true)
const isLoadingRiwayat = ref(false)
const searchTerm = ref('')
const memberList = ref([])
const memberTransactions = ref([]);
const selectedMemberId = ref(null)
const selectedGroup = ref('')
const onImport = ref(false)
const fileInput = ref(null);
const groups = ref(null)

const exporting = ref(false)

const selectedStatus = ref('')

// const groups = computed(() => {
//   if (!memberList.value.length) return [];
  
//   // Ambil unique PJ
//   const uniquePj = [...new Set(memberList.value.map(m => m.pj))].filter(Boolean);
  
//   // Map ke bentuk Object agar bisa dibaca Dropdown
//   return uniquePj.sort().map(name => ({
//     id: name,    // Kita gunakan nama PJ sebagai ID untuk v-model
//     nama: name   // Ini yang akan tampil di label
//   }));
// });

const filteredMembers = computed(() => {
  const q = searchTerm.value.toLowerCase();
  const group = selectedGroup.value;
  const status = selectedStatus.value;
  
  if (!memberList.value) return [];
  
  return memberList.value.filter(m => {
    const matchSearch = m.nama.toLowerCase().includes(q) || 
                       m.pj.toLowerCase().includes(q) || 
                       m.no_anggota?.toString().includes(q);
    
    const matchGroup = group === '' || m.pj === group;
    const currentStatus = m.status || 'aktif';
    const matchStatus = status === '' || currentStatus === status;

    return matchSearch && matchGroup && matchStatus;
  });
});

const debouncedFetch = debounce((page) => {
  fetchAnggota(page);
}, 1000);

watch([searchTerm, selectedGroup, selectedStatus], () => {
  debouncedFetch(1);
});

const importAnggotaConfirmation = async () => {
    if (onImport.value) return;

    const result = await Swal.fire({
        title: 'Konfirmasi Import',
        text: "Pilih file .xlsx untuk mengimpor data anggota.",
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

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Mulai proses upload
    onImport.value = true;
    
    const formData = new FormData();
    formData.append('file', file);

    try {
        // Tampilkan loading dialog agar user tidak interaksi sembarangan
        Swal.fire({
            title: 'Sedang Mengunggah',
            text: 'Harap tunggu...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const response = await api.post('/anggota/import', formData, {
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

async function exportAnggota() {
  exporting.value = true;
  try {
    // Gunakan axios untuk mendownload file sebagai blob
    const response = await api.get('anggota/export', {
      responseType: 'blob' 
    });

    // Proses download di browser
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Anggota Koperasi.xlsx`);
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

async function toggleStatus(member) {
  // Opsi: Tambahkan konfirmasi sederhana
  const confirmMsg = `Ubah status ${member.nama} menjadi ${member.status === 'aktif' ? 'Non-Aktif' : 'Aktif'}?`;
  if (!confirm(confirmMsg)) return;

  try {
    const newStatus = member.status === 'aktif' ? 'non-aktif' : 'aktif';
    
    // Panggil API (sesuaikan endpoint-mu)
    await api.patch(`anggota/${member.id}/status`, {
      status: newStatus
    });

    // Update data di lokal agar UI langsung berubah tanpa refresh
    member.status = newStatus;
    
    // Opsional: Tampilkan notifikasi sukses
    // alert('Status berhasil diperbarui');
  } catch (error) {
    console.error("Gagal mengubah status:", error);
    alert('Gagal memperbarui status');
  }
}

const tarikOnSuccess = (payload) => {
  fetchAnggota()
  const index = memberList.value.findIndex(m => m.id === payload.memberId);
  
  if (index !== -1) {
    memberList.value[index] = {
      ...memberList.value[index],
      saldo_pokok: payload.pokok,
      saldo_wajib: payload.wajib,
      saldo_sukarela: payload.sukarela,
      saldo_khusus: payload.khusus,
      status: payload.status
    };

  }
};

const setorOnSuccess = (payload) => {
  const index = memberList.value.findIndex(m => m.id === payload.memberId);
  if (index !== -1) {
    memberList.value[index][payload.tipe] += payload.nominal;
  }
};

async function showMemberRiwayat(id) {
  isLoadingRiwayat.value = true;
  try {
    const response = await api.get(`anggota/riwayat-transaksi/${id}`);
    memberTransactions.value = response.data.transaksi;
    props.modals.memberDetail = response.data.anggota;
  } catch (error) {
    console.error("Gagal mengambil riwayat:", error);
  } finally {
    isLoadingRiwayat.value = false
  }
}

async function fetchAnggota(page = 1) {
  isLoading.value = true;
  try {
    const response = await api.get('anggota', {
      params: { 
        page: page,
        search: searchTerm.value, // Kirim search ke server
        group: selectedGroup.value,
        status: selectedStatus.value
      }
    });
    if (response.data.data) {
      memberList.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total
      };
    } else {
      // Fallback jika backend belum di-paginate
      memberList.value = response.data;
    }
    // memberList.value = response.data.data || response.data;
  } catch (error) {
    console.error("Gagal mengambil data anggota:", error);
  } finally {
    isLoading.value = false;
  }
}

async function fetchPJ()
{
  try {
    const res = await api.get('anggota/pj')
    if(res.data) {
      groups.value = res.data
        .map(item => ({
          id: item.pj,   // Ambil nilai dari key 'pj'
          nama: item.pj  // Gunakan nilai yang sama untuk label di dropdown
        }))
        // Opsional: Urutkan secara numerik jika PJ berupa angka
        .sort((a, b) => parseInt(a.nama) - parseInt(b.nama));
    }
  } catch(e) {
    console.log(e)
  }
}

onMounted(async() => {
  fetchAnggota();
  await fetchPJ();
})
</script>