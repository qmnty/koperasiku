<template>
  <div v-if="modals.saving" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-sm p-7 shadow-2xl scale-in">
      <div class="flex justify-between mb-8">
        <h2 class="text-xl font-black flex items-center gap-2 text-emerald-600"><i class="fa-solid fa-plus"></i>Setoran Dana</h2>
        <button @click="modals.saving = false" class="cursor-pointer"><i class="fa-solid fa-x"></i></button>
      </div>
      <div class="flex gap-2 mb-6 bg-slate-100 p-1 rounded-xl">
          <button v-for="t in ['wajib', 'sukarela']" :key="t" @click="savingForm.tipe = t" :class="['cursor-pointer flex-1 py-2 text-[10px] font-black uppercase rounded-lg transition', savingForm.tipe === t ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400']">{{ t }}</button>
      </div>
      <form @submit.prevent="handleSaving" class="space-y-6">
        <input 
          v-model="savingForm.nominal" 
          type="number" 
          required 
          :disabled="savingForm.tipe === 'wajib'"
          :value="savingForm.tipe === 'wajib' ? 30000 : savingForm.nominal"
          class="w-full border border-slate-200 rounded-2xl py-5 px-6 text-xl font-black bg-slate-50 text-emerald-600 focus:ring-2 focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed"
        >
        <div class="grid grid-cols-2 gap-2">
          <button 
            type="button"
            @click="savingForm.paymentMethod = 'cash'"
            :class="['py-3 cursor-pointer rounded-xl text-xs font-bold transition', savingForm.paymentMethod === 'cash' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-500']"
          >
            <i class="fa-solid fa-money-bill-wave mr-2"></i> CASH
          </button>
          <button 
            type="button"
            @click="savingForm.paymentMethod = 'transfer'"
            :class="['py-3 cursor-pointer rounded-xl text-xs font-bold transition', savingForm.paymentMethod === 'transfer' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-500']"
          >
            <i class="far fa-credit-card mr-2"></i> TRANSFER
          </button>
        </div>
        <button type="submit" class="w-full cursor-pointer py-4 bg-emerald-600 text-white rounded-2xl font-black shadow-lg">SIMPAN SETORAN</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import api from '@/lib/api';
import { ref } from 'vue';
import { reactive } from 'vue';

const savingForm = reactive({ memberId: '', tipe: 'wajib', nominal: 30000, paymentMethod: 'cash' });
const props = defineProps({
  modals: Object,
  memberId: Number
})
const emit = defineEmits(['success']);

const handleSaving = async() => {
  const nominal = Number(savingForm.nominal);
  try {
    let res = await api.post('anggota/simpanan/store', {
      memberId: props.memberId,
      nominal: nominal,
      tipe: savingForm.tipe,
      paymentMethod: savingForm.paymentMethod
    });
    if(res.status !== 200 && res.status !== 201) throw new Error(res.data.message);
    if (props.memberId) {
      emit('success', {
        memberId: props.memberId,
        tipe: savingForm.tipe,
        nominal: nominal
      });
    }
    props.modals.saving = false;
    savingForm.nominal = 30000;
  } catch (error) {
    console.log(error);
  }
};
</script>