<label class="form-label">Municipalidad</label>
<select name="municipalidad_id" class="form-select" required>
    <option value="">Seleccione…</option>
    @foreach ($municipalidades as $m)
        <option value="{{ $m->id }}" @selected(old('municipalidad_id', $valor) == $m->id)>{{ $m->nombre }}</option>
    @endforeach
</select>
