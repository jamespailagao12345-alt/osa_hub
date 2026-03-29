@props([
  'name' => 'birth_date',
  'label' => 'Birth Date',
  'value' => null,
  'ageName' => 'age',
  'required' => false,
])
<div class="mb-2" data-birth-age-pair>
  <label for="{{ $name }}" class="form-label">{{ $label }}</label>
  <div class="d-flex gap-2 align-items-center">
    <input type="date" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}" class="form-control" max="{{ date('Y-m-d', strtotime('-1 day')) }}" min="1900-01-01" {{ $required ? 'required' : '' }}>
    <input type="number" name="{{ $ageName }}" id="{{ $ageName }}" class="form-control" placeholder="Age" readonly style="max-width: 100px;">
  </div>
</div>
@once
@push('scripts')
<script>
(function(){
  function calcAge(d){
    const bd = new Date(d);
    if(isNaN(bd.getTime())) return '';
    const t = new Date();
    let a = t.getFullYear() - bd.getFullYear();
    const m = t.getMonth() - bd.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < bd.getDate())) a--;
    return a;
  }
  function bindPair(container){
    const date = container.querySelector('input[type="date"]');
    const age = container.querySelector('input[type="number"]');
    if(!date || !age) return;
    const update = ()=> age.value = calcAge(date.value);
    date.addEventListener('change', update);
    update();
  }
  document.querySelectorAll('[data-birth-age-pair]').forEach(bindPair);
})();
</script>
@endpush
@endonce