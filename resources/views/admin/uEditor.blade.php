
<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$column}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')
        {{--这个style可以限制他的高度，不会随着内容变长 --}}
        <textarea type='text/plain' style="height:400px;" id="{{$column}}" name="{{$name}}" placeholder="{{ $placeholder }}" {!! $attributes !!}  class='{{$column}}'>
            {!! old($column, $value) !!}
        </textarea>
        @include('admin::form.help-block')
    </div>
</div>