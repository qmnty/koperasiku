<template>
  <div v-if="modals.loan" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-7 shadow-2xl scale-in">
      <div class="flex justify-between mb-8">
        <h2 class="text-xl font-black text-emerald-600 flex items-center gap-2"><i class="fa-solid fa-hand-holding-dollar"></i> Pencairan Dana</h2>
        <button @click="modals.loan = false" class="cursor-pointer"><i class="fa-solid fa-x"></i></button>
      </div>
      <form @submit.prevent="handleCreateLoan" class="space-y-4">
        <Dropdown 
          v-model="loanForm.memberId"
          :options="members"
          labelKey="nama"
          subKey="pj"
          subLabelPrefix="PJ: "
          :searchFields="['nama', 'pj', 'no_anggota']"
        />
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div class="flex flex-col gap-2">
            <label for="realisasi" class="text-sm font-semibold text-slate-600 ml-1">Nominal Realisasi</label>
            <input 
              id="realisasi"
              v-model="loanForm.realisasi" 
              type="number" 
              placeholder="Contoh: 5000000" 
              required 
              class="w-full border-2 border-slate-100 rounded-2xl p-4 py-2 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 bg-slate-50 font-bold"
            >
          </div>

          <div class="flex flex-col gap-2">
            <label for="tenor" class="text-sm font-semibold text-slate-600 ml-1">Tenor (Bulan)</label>
            <input 
              id="tenor"
              v-model="loanForm.tenor" 
              type="number"
              max="12"
              placeholder="Lama Pinjaman" 
              required 
              class="w-full border-2 border-slate-100 rounded-2xl p-4 py-2 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 bg-slate-50 font-bold"
            >
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="tanggalCair" class="text-sm font-semibold text-slate-600 ml-1">Tanggal Pencairan</label>
          <input 
            id="tanggalCair"
            v-model="loanForm.tanggalCair" 
            type="date" 
            required 
            class="w-full cursor-pointer border-2 border-slate-100 rounded-2xl p-4 py-2 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 bg-slate-50 font-bold"
          >
        </div>
        <div v-if="loanForm.realisasi > 0" class="grid grid-cols-2 gap-3">
          <div class="p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-xs">
            <p class="font-bold text-slate-500 mb-2 uppercase tracking-tight">Potongan Pencairan</p>
            <div class="flex justify-between text-rose-500 mb-1"><span>Administrasi (1%)</span><span>-{{ formatIDR(loanForm.realisasi * 0.01) }}</span></div>
            <div class="flex justify-between text-rose-500 mb-1"><span>Simp. Khusus (1%)</span><span>-{{ formatIDR(loanForm.realisasi * 0.01) }}</span></div>
            <div class="flex justify-between text-rose-500 mb-2"><span>Voucher (1%)</span><span>-{{ formatIDR(loanForm.realisasi * 0.01) }}</span></div>
            <div class="flex justify-between font-black text-slate-700 text-sm pt-2 border-t"><span>Dana Diterima</span><span>{{ formatIDR(loanForm.realisasi * 0.97) }}</span></div>
          </div>

          <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
            <p class="text-[10px] font-black text-emerald-600 mb-1 uppercase tracking-wider">Estimasi Angsuran / Bulan</p>
            <div class="flex justify-between items-end">
              <div>
                <h4 class="text-xl font-black text-emerald-700 leading-none">
                  {{ formatIDR(estimasiAngsuran) }}
                </h4>
                <p class="text-[9px] text-emerald-600 mt-1 font-medium">
                  Pokok: {{ formatIDR(loanForm.realisasi / loanForm.tenor) }} + Jasa
                </p>
              </div>
              <div class="text-right">
                <span class="text-[10px] font-bold text-emerald-700 bg-white px-2 py-1 rounded-lg border border-emerald-200">
                  {{ loanForm.tenor }}x Bayar
                </span>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-black hover:bg-emerald-700 transition cursor-pointer">Submit</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import api from '@/lib/api';
import { computed, onMounted, ref } from 'vue';
import { reactive } from 'vue';
import { formatIDR } from '@/lib/global';
import Dropdown from '@/components/Dropdown.vue';


const props = defineProps({
  modals: Object
})

const loanForm = reactive({ 
  memberId: '', 
  realisasi: 0, 
  tenor: 1,
  tanggalCair: new Date().toISOString().split('T')[0] 
});

const emit = defineEmits(['success']);

const members = ref([])

onMounted(() => {
  fetchAnggota();
})

const estimasiAngsuran = computed(() => {
  if (!loanForm.realisasi || !loanForm.tenor) return 0;
  
  const pokok = Number(loanForm.realisasi) / Number(loanForm.tenor);
  const jasa = Number(loanForm.realisasi) * 0.015; // Contoh jasa 1.5% flat
  
  return pokok + jasa;
});

async function fetchAnggota() {
  try {
    const response = await api.get('anggota', {
      params: {
        only_aktif: 1 // atau true
      }
    });
    // Sesuaikan dengan struktur return Laravel (biasanya response.data.data atau response.data)
    members.value = response.data.data || response.data;
  } catch (error) {
    console.error("Gagal mengambil data anggota:", error);
  } finally {
  }
}
const handleCreateLoan = async () => {
  const realisasi = Number(loanForm.realisasi);
  const tenor = Number(loanForm.tenor);
  
  // Hitung angsuran pokok dan tanggal jatuh tempo di frontend atau backend.
  // Disarankan di backend untuk konsistensi, tapi di sini kita hitung dulu
  // untuk ditampilkan di UI.
  const angsuranPokok = realisasi / tenor;
  
  try {
    // 1. Kirim data ke API backend
    const res = await api.post('anggota/pinjaman/store', {
      memberId: loanForm.memberId,
      tanggalCair: loanForm.tanggalCair,
      realisasi: realisasi,
      tenor: tenor,
      angsuranPokok: angsuranPokok
    });

    if (res.status !== 200 && res.status !== 201) {
      throw new Error(res.data.message || 'Gagal membuat pinjaman');
    }

    if(res.data.gagal) {
      throw new Error(res.data.message)
    }

    emit('success', res.data.loan)
    
    props.modals.loan = false;
    
    // Reset form
    Object.assign(loanForm, { 
      memberId: '', 
      realisasi: 0, 
      tenor: 1, 
      tanggalCair: new Date().toISOString().split('T')[0] 
    });
    
  } catch (error) {
    console.error(error);
    alert('Gagal membuat pinjaman: ' + error.message);
  }
};
</script>