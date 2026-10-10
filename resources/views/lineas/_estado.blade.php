@switch($estado)
    @case('ok') <span class="badge text-bg-success">Cumple</span> @break
    @case('faltan') <span class="badge text-bg-warning">Faltan buses</span> @break
    @default <span class="badge text-bg-danger">Exceso de buses</span>
@endswitch
