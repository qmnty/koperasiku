<template>
  <div v-if="props.modals.member" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-md p-7 shadow-2xl scale-in">
      <div class="flex justify-between mb-8">
        <h2 class="text-xl font-black flex items-center gap-2 text-emerald-600"><i class="fa-solid fa-plus"></i> Anggota Baru</h2>
        <button @click="modals.member = false" class="cursor-pointer"><i class="fa-solid fa-x"></i></button>
      </div>
      <form @submit.prevent="handleAddMember" class="space-y-4">
        <div class="flex flex-col gap-1.5">
          <label for="nama" class="text-sm font-bold text-slate-600 ml-1">Nama Lengkap</label>
          <input 
            id="nama"
            v-model="newMember.nama" 
            placeholder="Masukkan Nama Lengkap" 
            required 
            class="w-full rounded-2xl border-2 border-slate-200 p-4 font-bold bg-slate-50 outline-none focus:border-emerald-500 transition"
          >
        </div>

        <div class="flex flex-col gap-1.5">
          <label for="pj" class="text-sm font-bold text-slate-600 ml-1">Penanggung Jawab / Kelompok</label>
          <input 
            id="pj"
            v-model="newMember.pj" 
            placeholder="Nama PJ atau Kelompok" 
            required 
            class="w-full rounded-2xl border-2 border-slate-200 p-4 font-bold bg-slate-50 outline-none focus:border-emerald-500 transition"
          >
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label for="pokok" class="text-sm font-bold text-slate-600 ml-1">Simpanan Pokok</label>
            <input 
              id="pokok"
              v-model="newMember.pokok" 
              type="number" 
              placeholder="Nominal Pokok" 
              class="w-full rounded-2xl border-2 border-slate-200 p-4 font-bold bg-slate-50 outline-none focus:border-emerald-500 transition"
            >
          </div>
          <div class="flex flex-col gap-1.5">
            <label for="wajib" class="text-sm font-bold text-slate-600 ml-1">Simpanan Wajib</label>
            <input 
              id="wajib"
              v-model="newMember.wajibAwal" 
              type="number" 
              placeholder="Nominal Wajib" 
              class="w-full rounded-2xl border-2 border-slate-200 p-4 font-bold bg-slate-50 outline-none focus:border-emerald-500 transition"
            >
          </div>
        </div>

        <button type="submit" class="w-full cursor-pointer py-4 bg-emerald-600 text-white rounded-2xl font-black hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 active:scale-[0.98]">
          SIMPAN ANGGOTA
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import api from '@/lib/api';
import Swal from 'sweetalert2';
import { reactive } from 'vue';

const props = defineProps({
  modals: Object,
  members: Array
})
const emit = defineEmits(['success']);

const newMember = reactive({ nama: '', pj: '', pokok: 0, wajibAwal: 30000 });

const handleAddMember = () => {
  try {
    const id = Math.random().toString(36).substr(2, 9).toUpperCase();
    const member = {
      id,
      nama: newMember.nama,
      pj: newMember.pj,
      pokok: Number(newMember.pokok),
      wajib: Number(newMember.wajibAwal),
      sukarela: 0,
      khusus: 0
    };
    const res = api.post('/anggota/store', member);
    props.modals.member = false;
    emit('success');
    Object.assign(newMember, { nama: '', pj: '', pokok: 0, wajibAwal: 30000 });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      toast: true,
      timer: 2500,
      timerProgressBar: true,
      text: error,
      showConfirmButton: false
    })
  }
};
</script>