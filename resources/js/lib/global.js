export const formatIDR = (val) => new Intl.NumberFormat('id-ID', { 
  style: 'currency', 
  currency: 'IDR', 
  maximumFractionDigits: 0 
}).format(val);

export const formatIDRNumberOnly = (value) => {
  if (!value) return '';
  return new Intl.NumberFormat('id-ID').format(value);
};

export const parseMoney = (value) => {
  // Menghapus semua karakter selain angka
  return parseInt(value.replace(/[^0-9]/g, ''), 10) || 0;
};