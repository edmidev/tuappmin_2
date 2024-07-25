@if(Auth::user()->rol_id != 1 && $residencia->status == 'Desactivado')
<div class="modal fade" id="modal-status" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">	<span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>Tu usuario se encuentra desactivado. Comunicate con el administrador.</p>

                <p>Equipo {{ config('app.name') }}.</p>
            </div>             
        </div>
    </div>
</div>
@endif