<template>
  <div v-if="isLoadingRiwayat" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl shadow-2xl border border-slate-100 flex flex-col items-center gap-3">
      <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 text-3xl"></i>
      <span class="text-sm font-bold text-slate-600 tracking-wide">Memuat Data...</span>
    </div>
  </div>
  <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-slate-800">Data Anggota</h1>
      <p class="text-xs text-slate-500 font-medium">Total: {{ filteredMembers.length }} Anggota ditemukan</p>
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

      <select 
        v-model="selectedGroup"
        class="bg-white border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition cursor-pointer"
      >
        <option value="">Semua Kelompok</option>
        <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
      </select>

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
        <span class="hidden sm:inline">Anggota</span>
      </button>
    </div>
  </div>

  <div v-if="isLoading" class="text-center py-10 text-slate-500">
    Memuat data...
  </div>
  <div v-else>
    <div v-if="memberList.length" class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div v-for="m in filteredMembers" :key="m.id" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 @click="showMemberRiwayat(m.id)" class="font-bold text-lg cursor-pointer group-hover:text-emerald-600 transition flex items-center gap-1">
              {{ m.nama }}<i class="fa-solid fa-circle-info"></i>
            </h3>
            <span :class="[
              'text-[9px] px-2 py-0.5 rounded-full font-black uppercase tracking-wider border',
              m.status === 'aktif' 
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100' 
                : 'bg-slate-100 text-slate-500 border-slate-200'
            ]">
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
import { onMounted, ref, computed } from 'vue';
import { formatIDR } from '@/lib/global';
import AnggotaDetail from './AnggotaDetail.vue';
import AnggotaSetor from './AnggotaSetor.vue';
import AnggotaTarikTunai from './AnggotaTarikTunai.vue';
import AnggotaAdd from './AnggotaAdd.vue';

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

const selectedStatus = ref('')

const groups = computed(() => {
  if (!memberList.value) return [];
  const allGroups = memberList.value.map(m => m.pj);
  return [...new Set(allGroups)].sort();
});

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

async function fetchAnggota() {
  isLoading.value = true;
  try {
    const response = await api.get('anggota');
    memberList.value = response.data.data || response.data;
  } catch (error) {
    console.error("Gagal mengambil data anggota:", error);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchAnggota();
})
</script>