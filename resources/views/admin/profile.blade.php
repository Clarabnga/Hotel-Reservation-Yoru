@extends('admin.dashboard')
@section('admin')
@include('message')

<<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <h6 class="card-title">Profie Update</h6>

                <form class="forms-sample" action="{{url('profile/update')}}" method="post">
                    <div class="mb-3">
                        {{csrf_field()}}
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control"
                            placeholder="Name" name="name" value="{{$getRecord->name}}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" 
                            placeholder="Username" name="username" value="{{$getRecord->username}}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" value="{{$getRecord->email}}">
                        <span style="color: red">{{$errors->first('email')}}</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control"
                            placeholder="Password" name="password">
                    </div>
                    leave blank if u're not changing

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control"
                            placeholder="Phone" name="phone"  value="{{$getRecord->phone}}">
                    </div>
                    <div class="form-check mb-3">
                      
                    </div>
                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                </form>

            </div>
        </div>
    </div>
    </div>
    @endsection