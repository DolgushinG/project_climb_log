<button id="btn-list-pending" data-bs-toggle="modal" data-bs-target="#list_pending"
        class="btn btn-warning rounded-pill">
    Лист ожидания
</button>
<div class="modal fade" id="list_pending" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбрать сеты в лист ожидания</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="listPending">
                    <div class="row mb-3">
                        <div class="col-sm-10">
                            <p>Вы можете добавиться в лист ожидания и как только место освободиться, вы сразу автоматически его займете, дополнительно вам на почту придет уведомление.</p> <br>
                            @foreach($sets as $set)
                                @if($set->free <= 0)
                                    <div class="form-check form-switch">
                                        @if(in_array(strval($set->number_set), $list_pending->number_sets ?? []))
                                            <input class="form-check-input" type="checkbox" name="{{$set->number_set}}" id="gridCheck{{$set->number_set}}" value="{{$set->number_set}}" checked>
                                            <label class="form-check-label" for="gridCheck{{$set->number_set}}">
                                                Сет {{$set->number_set}}
                                                @lang('somewords.'.$set->day_of_week)
                                                @isset($set->date[$set->day_of_week])
                                                    {{$set->date[$set->day_of_week]}}
                                                @endisset
                                                {{$set->time}}
                                            </label>
                                        @else
                                            @if($set->id == \App\Models\Set::get_number_set_id_for_user($event, Auth()->user()->id))
                                                <input class="form-check-input" type="checkbox" name="{{$set->number_set}}" id="gridCheck{{$set->number_set}}" value="{{$set->number_set}}">
                                                <label class="form-check-label" for="gridCheck{{$set->number_set}}">
                                                    Сет {{$set->number_set}}
                                                    @lang('somewords.'.$set->day_of_week)
                                                    @isset($set->date[$set->day_of_week])
                                                        {{$set->date[$set->day_of_week]}}
                                                    @endisset
                                                    {{$set->time}}
                                                </label>
                                            @else
                                                <input class="form-check-input" type="checkbox" name="{{$set->number_set}}" id="gridCheck{{$set->number_set}}" value="{{$set->number_set}}">
                                                <label class="form-check-label" for="gridCheck{{$set->number_set}}">
                                                    Сет {{$set->number_set}}
                                                    @lang('somewords.'.$set->day_of_week)
                                                    @isset($set->date[$set->day_of_week])
                                                        {{$set->date[$set->day_of_week]}}
                                                    @endisset
                                                    {{$set->time}}
                                                </label>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                            <br>
                            @include('event.selects.categories_modal')
                            @include('event.selects.birthdayModal')<br>
                            @include('event.selects.gendersModal')<br>
                            @include('event.selects.sport_categoriesModal')<br>
                            <div style="display:none;" id="error-message-modal" class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">

                @if($is_add_to_list_pending)
                    <button id="add-to-list-pending-remove" data-user-id="{{Auth()->user()->id}}" data-event-id="{{$event->id}}" type="button" class="btn btn-danger">Удалить</button>
                    <button id="list-pending-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button id="add-to-list-pending" data-user-id="{{Auth()->user()->id}}" data-event-id="{{$event->id}}" type="button" class="btn btn-primary">Изменить</button>
                @else
                    <button id="list-pending-close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button id="add-to-list-pending" data-user-id="{{Auth()->user()->id}}" data-event-id="{{$event->id}}" type="button" class="btn btn-primary">Добавить</button>
                @endif
            </div>
        </div>
    </div>
</div>
