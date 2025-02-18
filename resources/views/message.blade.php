@if(!empty(session('success')))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(!empty(session('error')))
<div class="alert alert-error">
    {{session('error')}}
</div>
@endif