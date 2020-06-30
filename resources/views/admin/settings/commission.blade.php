@extends('admin.layouts.admin')
@section('content')
    <div class="block-header">
        <h2>комиссия</h2> <br>
    </div>

    <form action="{{route('CommissionSave')}}" method="post">
        <div class="modal-body">
            {{csrf_field()}}
            <label>комиссия (%)</label>
            <div class="form-group">
                <div class="form-line">
                    <input type="number"  min="0" step="0.1" class="form-control" name="value" value="{{$c->value}}">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success waves-effect">Сохранить</button>
        </div>
    </form>
    </div>
@endsection