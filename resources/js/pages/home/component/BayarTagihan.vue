<template>
  <div v-if="modals.installment" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-md p-7 shadow-2xl scale-in">
      <div class="flex justify-between mb-8">
        <h2 class="text-xl font-black flex items-center gap-2 text-amber-600"><i class="fa-solid fa-arrow-down"></i> Bayar Tagihan</h2>
        <button @click="onClose" class="cursor-pointer"><i class="fa-solid fa-x"></i></button>
      </div>
      <form @submit.prevent="handleInstallment" class="space-y-4">
        <Dropdown 
          v-model="instForm.loanId"
          :options="loanOptions"
          valueKey="no_kontrak"
          labelKey="no_kontrak"
          subKey="nama"
          placeholder="Cari nama anggota atau ID pinjaman..."
          subLabelPrefix=""
          :searchFields="['no_kontrak', 'nama']"
          @change="fetchLoanDetail"
          @search="fetchPinjamanSearch"
        />
        <!-- <select v-model="instForm.loanId" @change="fetchLoanDetail" required class="w-full rounded-2xl border-slate-200 p-4 font-bold bg-slate-50">
          <option value="">-- Pilih Pinjaman --</option>
          <option v-for="l in loans.filter(x => x.sisaTenor > 0)" :key="l.id" :value="l.id">
            {{ l.id }} - {{ members.find(m => m.id === l.memberId)?.nama }} (Sisa {{ l.sisaTenor }}x)
          </option>
        </select> -->
        
        <div v-if="currentLoanCalc" class="space-y-4 animate-in slide-in-from-top-4 duration-300">
          <div :class="['p-5 rounded-3xl border-2 transition-all', instForm.isLunas ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-100']">
            <div class="mb-4 flex flex-col">
              <span class="text-sm font-bold">{{ selectedLoanDetail.nama_anggota }}</span>
              <span class="text-xs text-slate-400">{{ selectedLoanDetail.no_anggota }}</span>
            </div>
            <div class="flex justify-between items-start mb-4">
                <span :class="['text-[10px] font-black px-3 py-1 rounded-full uppercase text-white', instForm.isLunas ? 'bg-amber-500' : 'bg-emerald-500']">
                  {{ instForm.isLunas ? 'Pelunasan Total' : `Angsuran Ke-${currentLoanCalc.angsuranKe}` }}
                </span>
                <div class="text-right text-[10px] font-bold text-slate-400">BUNGA {{ currentLoanCalc.bungaRate*100 }}%</div>
            </div>
            <div class="space-y-2 text-xs">
              <div class="flex justify-between"><span>Pokok</span><span class="font-bold text-slate-900">{{ formatIDR(currentLoanCalc.pokokBayar) }}</span></div>
              <div class="flex justify-between"><span>Bunga</span><span class="font-bold text-slate-900">{{ formatIDR(currentLoanCalc.bungaNominal) }}</span></div>
              <div class="pt-2 border-t border-dashed border-emerald-300 flex justify-between font-black text-lg text-emerald-700"><span>Total</span><span>{{ formatIDR(currentLoanCalc.total) }}</span></div>
            </div>
          </div>

          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Uang yang Diterima</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
              <input 
                type="text" 
                :value="formattedDisplay"
                @input="onMoneyInput"
                placeholder="0"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-xl font-black text-lg text-slate-700 outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
            
            <div v-if="instForm.amountPaid > 0" class="mt-3 flex justify-between items-center px-1">
              <span class="text-[10px] font-bold text-slate-400 uppercase">
                {{ instForm.amountPaid >= currentLoanCalc.total ? 'Kembalian' : 'Kurang' }}
              </span>
              <span :class="['font-bold', instForm.amountPaid >= currentLoanCalc.total ? 'text-emerald-600' : 'text-rose-500']">
                {{ formatIDR(Math.abs(instForm.amountPaid - currentLoanCalc.total)) }}
              </span>
            </div>
          </div>
  
          <div v-if="!currentLoanCalc.isEarly" @click="instForm.isLunas = !instForm.isLunas" class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl cursor-pointer">
            <div :class="['w-6 h-6 rounded flex items-center justify-center border-2', instForm.isLunas ? 'bg-amber-500 border-amber-500' : 'bg-white border-slate-300']">
              <i v-if="instForm.isLunas" class="fa-regular fa-circle-check text-white"></i>
            </div>
            <span class="text-xs font-bold text-slate-700">Klik untuk Pelunasan Sekaligus</span>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button 
              type="button"
              @click="instForm.paymentMethod = 'cash'"
              :class="['py-3 cursor-pointer rounded-xl text-xs font-bold transition', instForm.paymentMethod === 'cash' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-500']"
            >
              <i class="fa-solid fa-money-bill-wave mr-2"></i> CASH
            </button>
            <button 
              type="button"
              @click="instForm.paymentMethod = 'transfer'"
              :class="['py-3 cursor-pointer rounded-xl text-xs font-bold transition', instForm.paymentMethod === 'transfer' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-500']"
            >
              <i class="far fa-credit-card mr-2"></i> TRANSFER
            </button>
          </div>
        </div>
        <button type="submit" :disabled="!instForm.loanId" class="w-full cursor-pointer py-4 bg-emerald-600 text-white rounded-2xl font-black shadow-xl hover:bg-emerald-700 disabled:opacity-30">KONFIRMASI PEMBAYARAN</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import api from '@/lib/api';
import { computed, reactive, ref, watch } from 'vue';
import { formatIDR, parseMoney, formatIDRNumberOnly } from '@/lib/global';
import Dropdown from '@/components/Dropdown.vue';

const instForm = reactive({ loanId: '', isLunas: false, paymentMethod: 'cash' });
const props = defineProps({
  loans: Array,
  members: Array,
  modals: Object,
  initialLoanId: String
})

const selectedLoanDetail = ref(null)
const isLoading = ref(false)
const loanOptions = ref(props.loans)

const emit = defineEmits('success');

const fetchPinjamanSearch = async (query) => {
  const response = await api.get(`/pinjaman/search?search=${query}`);
  loanOptions.value = response.data;
  selectedLoanDetail.value = null
};

const formattedDisplay = computed(() => {
  return instForm.amountPaid > 0 ? formatIDRNumberOnly(instForm.amountPaid) : '';
})

const onMoneyInput = (event) => {
  // 1. Ambil nilai mentah dari input
  let val = event.target.value;

  // 2. Bersihkan karakter non-angka
  const cleanValue = val.replace(/[^0-9]/g, '');

  // 3. Update model (instForm)
  instForm.amountPaid = cleanValue === '' ? 0 : parseInt(cleanValue, 10);

  // 4. PAKSA input DOM agar hanya menampilkan angka bersih 
  // Ini kunci agar huruf "menghilang" seketika saat diketik
  event.target.value = cleanValue === '' ? '' : new Intl.NumberFormat('id-ID').format(cleanValue);
};

function onClose() {
  props.modals.installment = false
  selectedLoanDetail.value = null
  instForm.loanId = null
}

const handleInstallment = async () => {
  const calc = currentLoanCalc.value;
  if (!calc) return;

  // Konfirmasi jika pelunasan
  if (instForm.isLunas && !confirm('Apakah Anda yakin ingin melakukan pelunasan sekaligus?')) {
    return;
  }

  isLoading.value = true;
  try {
    const response = await api.post('/pinjaman/bayar', {
      loan_id: instForm.loanId,
      is_lunas: instForm.isLunas,
      nominal_pokok: calc.pokokBayar,
      nominal_bunga: calc.bungaNominal,
      total_bayar: calc.total,
      angsuran_ke: calc.angsuranKe,
      amount_paid: instForm.amountPaid,
      payment_method: instForm.paymentMethod
    });

    if (response.data.success) {      
      // Tutup modal
      props.modals.installment = false;
      
      // Reset form
      Object.assign(instForm, { loanId: '', isLunas: false });
      selectedLoanDetail.value = null;

      emit('success');

      // Emit event ke parent jika perlu refresh data list pinjaman
      // emit('refresh-data'); 
    }
  } catch (error) {
    console.error("Gagal bayar:", error);
    alert(error.response?.data?.message || "Terjadi kesalahan saat menyimpan pembayaran");
  } finally {
    isLoading.value = false;
  }
};

watch(() => instForm.loanId, (newId) => {
  if(!newId) selectedLoanDetail.value = null
})
watch(() => props.initialLoanId, (newId) => {
  if (newId) {
    instForm.loanId = newId;
    fetchLoanDetail(); // Otomatis ambil detail saat ID terisi
  }
}, { immediate: true });

const currentLoanCalc = computed(() => {
  // 1. Prioritaskan data detail dari API jika sudah terpilih
  if (!selectedLoanDetail.value) return null;

  const data = selectedLoanDetail.value;
  
  // 2. Tentukan progress untuk aturan pinalti pelunasan
  // Jika sisa tenor masih banyak (misal total tenor 12, sisa 12), berarti progress masih 0
  const totalTenor = data.tenor || 12;
  const sisaTenor = data.sisa_tenor;
  // const angsuranKe = totalTenor - sisaTenor + 1;
  const angsuranKe = data.angsuranke
  // const progress = (angsuranKe / totalTenor);
  const progress = data.total_tagihan > 0 
  ? (data.total_bayar / data.total_tagihan)
  : 0;

  // 3. Logika bungaRate: 
  // Jika Lunas & Progress < 50% maka 2% (0.02), jika tidak maka 1% (0.01)
  let bungaRate = (instForm.isLunas && progress < 0.5) ? 0.02 : 0.01;

  const isEarly = angsuranKe < 2; 
  const canPelunasan = !isEarly;

  if (isEarly && instForm.isLunas) {
    instForm.isLunas = false;
  }
  
  // 4. Hitung nominal bunga berdasarkan rate terbaru
  // Ambil nominal_realisasi dari data API (misal: 1.000.000)
  const nominalRealisasi = data.nominal_realisasi || 1000000; 
  const bungaNominal = nominalRealisasi * bungaRate;

  if (instForm.isLunas) {
    bungaRate = (progress < 0.5) ? 0.02 : 0.01;
  }

  // 5. Hitung pokok
  // const pokokBayar = instForm.isLunas 
  //   ? (data.pokok * sisaTenor) 
  //   : data.pokok;

  const pokokBayar = instForm.isLunas
    ? (data.total_tagihan - data.total_bayar)
    : data.pokok;

  return { 
    ...data, 
    angsuranKe, 
    isEarly,      // Masih angsuran ke-1
    canPelunasan, // Sudah boleh lunas (>= angsuran 2)
    bungaRate, 
    bungaNominal, 
    pokokBayar, 
    total: pokokBayar + bungaNominal 
  };
});

const fetchLoanDetail = async () => {
  // Jika input dikosongkan kembali
  if (!instForm.loanId) {
    selectedLoanDetail.value = null;
    return;
  }

  isLoading.value = true;
  try {
    // Sesuaikan URL dengan route Laravel Anda
    const response = await api.get(`/pinjaman/detail/${instForm.loanId}`);
    
    // Simpan data dari backend ke state
    selectedLoanDetail.value = response.data;
    instForm.amountPaid = response.data.pokok + response.data.bunga_estimasi
    
    // Opsi: Update data form secara otomatis jika ada nilai yang datang dari backend
    // instForm.nominal_bayar = response.data.angsuran_per_bulan;
    
  } catch (error) {
    console.error("Gagal mengambil data pinjaman:", error);
    alert("Data pinjaman tidak ditemukan");
  } finally {
    isLoading.value = false;
  }
};

watch(() => props.loans, (newLoans) => {
  loanOptions.value = newLoans;
}, { immediate: true, deep: true });
</script>