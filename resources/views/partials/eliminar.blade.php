<form method="POST" action="{{ $accion }}" class="d-inline" data-confirmar="{{ $confirmar ?? '¿Seguro que desea eliminar este registro?' }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i>{{ isset($texto) ? ' '.$texto : '' }}</button>
</form>
