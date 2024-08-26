
<button type="button" class="btn btn-info rounded-pill" data-bs-toggle="modal" data-bs-target="#disablebackdrop-{{$event->id}}">
    Получить полные результаты
</button>
<div class="modal fade" id="disablebackdrop-{{$event->id}}" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Получить полные результаты</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                В связи с большой нагрузкой и возможным долгим ожиданием, отправка результатов осуществляется только на почту
                <div class="col-md-6 mt-2 w-100">
                    <div class="form-floating">
                        <input value="{{\Illuminate\Support\Facades\Auth::user()->email ?? ''}}" data-event-id="{{$event->id}}" type="email" class="form-control" id="allResultFloatingEmail" placeholder="joedoe@exapmle.ru" required>
                        <label for="allResultFloatingEmail">Email</label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button id="send-all-result-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button id="send-all-result" type="button" class="btn btn-primary">Отправить</button>
            </div>
        </div>
    </div>
</div><!-- End Disabled Backdrop Modal-->
