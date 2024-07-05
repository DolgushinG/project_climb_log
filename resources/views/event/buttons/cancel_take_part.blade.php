@if($event->is_access_user_cancel_take_part)
<button type="button" class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#btn_modal_cancel_take_part_participant">
    Отменить регистрацию
</button>
<div class="modal fade" id="btn_modal_cancel_take_part_participant" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Регистрация</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Вы уверены, что хотите отменить регистрацию?
            </div>
            <div class="modal-footer">
                <button id="btn_cancel_take_part_participant" data-event-id="{{$event->id}}"
                        data-link="{{$event->link}}"
                        data-user-id="{{Auth()->user()->id}}"
                        class="btn btn-danger rounded-pill">Отменить регистрацию</button>
            </div>
        </div>
    </div>
</div>
@endif
