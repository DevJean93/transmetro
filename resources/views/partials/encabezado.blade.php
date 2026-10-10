<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{!! $titulo !!}</h1>
        @isset($subtitulo)<div class="text-secondary">{{ $subtitulo }}</div>@endisset
    </div>
    <div class="no-print">@isset($nuevo)<a href="{{ $nuevo }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo</a>@endisset</div>
</div>
